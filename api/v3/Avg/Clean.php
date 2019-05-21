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
    $returnValues = array();

    //Set time limit to 1800, otherwise execution is stopped after 30 seconds
    set_time_limit(1800); //Run max half an hour

    $grp_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_custom_group WHERE title = 'Expert Data'");
    $params_expertStatusField = array(
      'version' => 3,
      'sequential' => 1,
      'custom_group_id' => $grp_id,
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
    $sql = CRM_Avg_Utils::cleanUpQuery(100);

    $query_result = CRM_Core_DAO::executeQuery($sql);

    $users = array();

    while($query_result->fetch()){
      if(!empty($query_result->contact_id)){
        $users[] = $query_result->contact_id;
      }
    }

    $returnValues['is_error'] = 0;
    $returnValues['users_count'] = count($users);
    $returnValues['values'] = $users;

    if(!empty($users)) {
      $queue = CRM_Queue_Service::singleton()->create(array(
        'type' => 'Sql',
        'name' => 'nl.pum.avg',
        'reset' => true, //Flush queue upon creation
      ));

      $task = new CRM_Queue_Task(
        array('CRM_Avg_Page_BatchAnonymizer', 'Anonymize'), //call back method
        array($users,array(
          'block_useraccount' => 'yes',
          'remove_drupalroles' => 'yes',
          'remove_name' => 'no',
          'remove_namefromcasetitles' => 'no',
          'remove_personaldata' => 'no',
          'remove_additionaldata' => 'yes',
          'remove_jobtitle' => 'no',
          'remove_passportinformation' => 'yes',
          'remove_incaseofemergency' => 'yes',
          'remove_bankinformation' => 'no',
          'remove_nationality' => 'no',
          'remove_medical' => 'yes',
          'remove_flight' => 'yes',
          'remove_expertdata' => 'yes',
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

      $runner = new CRM_Queue_Runner(array(
        'title' => ts('nl.pum.avg: Clean inactive users queue runner'), //title fo the queue
        'queue' => $queue, //the queue object
        'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
        'onEnd' => array('CRM_Avg_Page_BatchAnonymizer', 'onEnd'),
        'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'),
      ));

      $runner->runAll();
    }

    $params['version'] = 3;
    $params['sequential'] = 1;
    $params['users'] = $users;

    return civicrm_api3_create_success($returnValues, $params, 'Avg', 'Clean');
}