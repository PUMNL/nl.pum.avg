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
					'status' => array(
										'name' => 'status',
										'title' => ts('Status'),
										'default' => TRUE,
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
  										'no_repeat' => TRUE),
          'expert_status_123' 	=> array(
										'name' => 'expert_status_123',
										'title' => ts('Expert Status'),
										'default' => TRUE,
  										'no_repeat' => TRUE));


 	  $this->_columns = array('civicrm_contact' => array(	'dao'       => 'CRM_Contact_DAO_Contact',
														'fields'    => $this->_fields,
														'grouping' 	=> 'contact-fields'));

    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', E::ts('Clean Inactive Users'));

    $this->assign('clean','<input type="submit" id="clean_all_selected" class="form-submit" value="Clean all selected users" />');

    parent::preProcess();
  }

  function postProcess() {
    set_time_limit(0);
    $sql = "SELECT DISTINCT gc.contact_id, gc.status, con.first_name, con.middle_name, con.last_name, eml.email, ed.expert_status_123
            FROM civicrm_group_contact gc
            LEFT JOIN civicrm_contact con ON con.id = gc.contact_id
            LEFT JOIN civicrm_email eml ON eml.contact_id = con.id
            LEFT JOIN civicrm_value_expert_data_20 ed ON ed.entity_id = con.id
            WHERE (
              (gc.group_id IN (SELECT id FROM civicrm_group grp WHERE grp.title = 'Former Expert'))
              AND
              (gc.group_id NOT IN (SELECT id FROM civicrm_group grp WHERE grp.title IN ('Anonymized Users','Active Expert')))
            )
            AND (gc.status = 'Added')
            AND eml.is_primary = '1'
            ORDER BY gc.contact_id";

  	//Set column headers for the report
  	$this->_columnHeaders = $this->_fields;

  	//Fetch the rows for the report
    $rows = array();
    $contacts = array();

    //Check if button 'Clean all selected users' is pressed
    if($this->_submitValues['_qf_default'] == 'CleanInactiveUsers:submit') {
      if(!empty($this->_submitValues['_qf_AnonymizeExitUsers_submit_save']) && $this->_submitValues['_qf_AnonymizeExitUsers_submit_save'] == 'Create Report') {

      } else {
        $hasPermission = CRM_Core_Permission::check('avg_batch_anonymize_users');

        if($hasPermission == TRUE) {
          foreach($_POST as $key => $value) {
            if(substr($key,0,8) == 'chkUser_' && $value == 'on') {
              $contactId = substr($key, strpos($key,'chkUser_')+strlen('chkUser_'));
              $contacts[] = $contactId;
            }
          }

          //If contacts array is not empty, anonymize button is pressed
          if(!empty($contacts) && is_array($contacts)) {
            foreach($contacts as $key => $cid) {
              $AvgUtils = new CRM_Avg_Utils($cid);
              //$AvgUtils->removePersonalData();
              $AvgUtils->blockUserAccount();
              $AvgUtils->removeDrupalRolesOfUser();
              //$AvgUtils->removeJobTitle();
              //$AvgUtils->removeName();
              //$AvgUtils->removeNameFromCaseTitles();
              $AvgUtils->removeExpertData();
              //$AvgUtils->removeGender();
              //$AvgUtils->removeAddresses();
              //$AvgUtils->removePhoneNumbers();
              //$AvgUtils->removeMailAddresses();
              $AvgUtils->removeWorkhistory();
              $AvgUtils->removeEducation();
              $AvgUtils->removeLanguages();
              $AvgUtils->removeCustomGroupDataOfContact('Passport Information');
              $AvgUtils->removeCustomGroupDataOfContact('In Case of Emergency Contact');
              $AvgUtils->removeCustomGroupDataOfContact('Bank Information');
              $AvgUtils->removeCustomGroupDataOfContact('Medical Information');
              $AvgUtils->removeCustomGroupDataOfContact('Flight Information');
              $AvgUtils->removeAdditionalData();
              $AvgUtils->removeFromGroups();
              $AvgUtils->removeContactSegments();
              $AvgUtils->removeDocuments();
              $AvgUtils->addUserToAnonymizedUsers();
            }
            drupal_set_message(t('All sensative data for the selected users has been cleared.'));
          }
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

  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('contact_id', $row)) {
        if ($viewLinks) {
          $url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' . $value, $this->_absoluteUrl);
          $rows[$rowNum]['civicrm_contact_contact_source_link'] = $url;
          $rows[$rowNum]['civicrm_contact_contact_source_hover'] = $onHover;
        }
      }
    }
  }

}
