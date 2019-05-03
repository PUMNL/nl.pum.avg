<?php
use CRM_Avg_ExtensionUtil as E;

/**
 * Avg.Clean API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_avg_clean_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * Avg.Clean API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_avg_clean($params) {
    //Set time limit to 0, otherwise execution is stopped after 30 seconds
    set_time_limit(0);

    $params_expertStatusField = array(
      'version' => 3,
      'sequential' => 1,
      'custom_group_name' => 'Expert data',
      'name' => 'expert_status',
      'return' => 'column_name',
    );
    $expertStatusField = civicrm_api('CustomField', 'getvalue', $params_expertStatusField);

    $params_expertDataTable = array(
      'version' => 3,
      'sequential' => 1,
      'name' => 'expert_data',
      'return' => 'table_name',
    );
    $expertDataTable = civicrm_api('CustomGroup', 'getvalue', $params_expertDataTable);

    //Query is limited to 100 users on each run, to prevent that the job is taking too much time
    $sql = "SELECT DISTINCT gc.contact_id, gc.status, con.first_name, con.middle_name, con.last_name, eml.email, ed.{$expertStatusField}
            FROM civicrm_group_contact gc
            LEFT JOIN civicrm_contact con ON con.id = gc.contact_id
            LEFT JOIN civicrm_email eml ON eml.contact_id = con.id
            LEFT JOIN {$expertDataTable} ed ON ed.entity_id = con.id
            WHERE
            ( -- In group former expert or rejected expert with status Exit
            ( ed.{$expertStatusField} = 'Exit' and gc.status = 'Added' and gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Former Expert','Rejected Expert')))
            OR
            -- Or was in group Representatives
            (gc.status = 'Removed' and gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Representatives')))
            )
            AND NOT EXISTS -- But not an active expert or anonymized user or cleaned inactive user
            (SELECT DISTINCT gc2.contact_id FROM civicrm_group_contact gc2 WHERE gc.contact_id = gc2.contact_id and gc2.status = 'Added' and gc2.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Anonymized Users','Active Expert','Cleaned Inactive Users')))
            AND eml.is_primary = '1'
            ORDER BY gc.contact_id
            LIMIT 100";

    $query_result = CRM_Core_DAO::executeQuery($sql);

    $users = array();

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => 'nl.pum.avg',
      'reset' => false, //do not flush queue upon creation
    ));

    while($query_result->fetch()){
      if(!empty($query_result->contact_id)) {
        $task = new CRM_Queue_Task(
          array('CRM_Avg_Page_BatchAnonymizer', 'Anonymize'), //call back method
          array($query_result->contact_id,array(
            'block_useraccount' => 'yes',
            'remove_drupalroles' => 'yes',
            'remove_name' => 'no',
            'remove_namefromcasetitles' => 'no',
            'remove_personaldata' => 'no',
            'remove_additionaldata' => 'yes',
            'remove_jobtitle' => 'no',
            'remove_passportinformation' => 'yes',
            'remove_incaseofemergency' => 'yes',
            'remove_bankinformation' => 'yes',
            'remove_nationality' => 'yes',
            'remove_medical' => 'yes',
            'remove_flight' => 'yes',
            'remove_expertdata' => 'yes',
            'remove_gender' => 'no',
            'remove_addresses' => 'no',
            'remove_mailaddresses' => 'no',
            'remove_phonenumbers' => 'no',
            'remove_workhistory' => 'yes',
            'remove_education' => 'yes',
            'remove_languages' => 'yes',
            'remove_groups' => 'yes',
            'remove_contactsegments' => 'yes',
            'remove_documents' => 'yes',
            'clean' => 'yes', //extra parameter for anonymize or clean
          ))
        );

        //now add this task to the queue
        $queue->createItem($task);
        $users[] = $query_result->contact_id;
      }
    }

    CRM_Core_Error::debug_log_message('Contact IDs to process for AVG Cleanup: '.print_r($users,TRUE));

    $runner = new CRM_Queue_Runner(array(
      'title' => ts('nl.pum.avg: Clean inactive users queue runner'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE, //continue on error otherwise the queue will hang
      'onEnd' => array('CRM_Avg_Utils', 'batchFinishedExecutionMessage'),
      'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'),
    ));

    $runner->runAllViaWeb();

    return civicrm_api3_create_success($returnValues, $params, 'Avg', 'Clean');
}
