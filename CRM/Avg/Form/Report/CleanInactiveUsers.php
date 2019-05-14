<?php
use CRM_Avg_ExtensionUtil as E;

class CRM_Avg_Form_Report_CleanInactiveUsers extends CRM_Report_Form {

  protected $_fields = array();

  function __construct() {
    $this->_fields = array('contact_id' 	=> array(
  										'name' => 'contact_id',
										'title' => ts('Contact ID'),
										'default' => TRUE,
										'required' => TRUE,
  										'no_repeat' => TRUE),
		 			'first_name' 	=> array(
		 								'name' => 'first_name',
					 					'title' => ts('First Name'),
										'default' => TRUE,
  										'no_repeat' => TRUE),
					'middle_name' 	=> array(
										'name' => 'middle_name',
										'title' => ts('Middle Name'),
										'default' => TRUE,
  										'no_repeat' => TRUE),
					'last_name' 	=> array(
										'name' => 'last_name',
										'title' => ts('Last Name'),
										'default' => TRUE,
  										'no_repeat' => TRUE),
          'email' 	=> array(
										'name' => 'email',
										'title' => ts('E-mail'),
										'default' => TRUE,
  										'no_repeat' => TRUE));


 	  $this->_columns = array('civicrm_contact' => array(	'dao'       => 'CRM_Contact_DAO_Contact',
														'fields'    => $this->_fields,
														'grouping' 	=> 'contact-fields'));

    //Make sure _sendmail is defined, because otherwise warning is triggerd
    $this->_sendmail = FALSE;

    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', E::ts('Clean Inactive Users'));

    $this->assign('clean','<input type="submit" id="clean_all_selected" class="form-submit" value="Clean all selected users" />');

    parent::preProcess();
  }

  function postProcess() {
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

    $sql = "SELECT DISTINCT gc.contact_id, gc.status, con.first_name, con.middle_name, con.last_name, eml.email, ed.{$expertStatusField}
            FROM civicrm_group_contact gc
            LEFT JOIN civicrm_subscription_history sh on gc.group_id = sh.group_id and gc.contact_id = sh.contact_id and gc.status = sh.status
            LEFT JOIN civicrm_contact con ON con.id = gc.contact_id
            LEFT JOIN civicrm_email eml ON eml.contact_id = con.id
            LEFT JOIN {$expertDataTable} ed ON ed.entity_id = con.id
            WHERE
            ( -- In group former expert or rejected expert with status Exit
            ( ed.{$expertStatusField} = 'Exit' and gc.status = 'Added' and gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Former Expert','Rejected Expert')))
            OR -- In group Ex-PUM Staff
            (gc.status = 'Added' and gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title = 'Ex-PUM Staff'))
            OR -- Was in group Representatives
            (gc.status = 'Removed' and gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title = 'Representatives'))
            )
            AND sh.date < date_add(now(), interval -9 month) -- Should be added to group at least 9 months ago
            AND NOT EXISTS -- But not in groups that indicate active users
            (SELECT DISTINCT gc2.contact_id FROM civicrm_group_contact gc2 WHERE gc.contact_id = gc2.contact_id and gc2.status = 'Added' and gc2.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Cleaned Inactive Users','Anonymized Users','Active Expert','Staffvolunteers','PUM Staff','Candidate Expert')))
            AND eml.is_primary = '1'
            ORDER BY gc.contact_id";

  	//Set column headers for the report
  	$this->_columnHeaders = $this->_fields;

  	//Fetch the rows for the report
    $rows = array();
    $contacts = array();

    //Check if button 'Clean all selected users' is pressed
    if(!empty($this->_submitValues['_qf_default']) && $this->_submitValues['_qf_default'] == 'CleanInactiveUsers:submit') {
      if(!empty($this->_submitValues['_qf_CleanInactiveUsers_submit_save']) && $this->_submitValues['_qf_CleanInactiveUsers_submit_save'] == 'Create Report') {

      } else {
        $hasPermission = CRM_Core_Permission::check('avg_batch_clean_inactive_users');

        if($hasPermission == TRUE) {
          foreach($_POST as $key => $value) {
            if(substr($key,0,8) == 'chkUser_' && $value == 'on') {
              $contactId = substr($key, strpos($key,'chkUser_')+strlen('chkUser_'));
              $contacts[] = $contactId;
            }
          }

          $queue = CRM_Queue_Service::singleton()->create(array(
            'type' => 'Sql',
            'name' => 'nl.pum.avg',
            'reset' => false, //do not flush queue upon creation
          ));

          if(!empty($contacts) && is_array($contacts)) {
            foreach($contacts as $cid) {
              //create a task
              $task = new CRM_Queue_Task(
                array('CRM_Avg_Page_BatchAnonymizer', 'Anonymize'), //call back method
                array($cid,array(
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
            }
          }

          $url = CRM_Utils_System::url('civicrm/avg/batch_anonymizer');
          CRM_Utils_System::redirect($url);
        } else {
          drupal_set_message(t('You are not allowed to clean inactive users'), 'error');
        }
      }
    }

    $this->buildRows($sql, $rows);
    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
}
