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

    $sql = CRM_Avg_Utils::cleanUpQuery();

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
