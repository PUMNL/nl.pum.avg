<?php

require_once 'CRM/Core/Page.php';

class CRM_Avg_Page_BatchAnonymizer extends CRM_Core_Page {
  function run() {
    //retrieve the queue
    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => 'nl.pum.avg',
      'reset' => false, //do not flush queue upon creation
    ));

    $runner = new CRM_Queue_Runner(array(
      'title' => ts('Batch anonymizer'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_ABORT, //abort upon error and keep task in queue
      'onEnd' => array('CRM_Avg_Page_BatchAnonymizer', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'), //go to page after all tasks are finished
    ));

    $runner->runAllViaWeb(); // does not return
  }

  /**
   * Handle the final step of the queue
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    CRM_Core_Session::setStatus('All selected users are anonymized or cleaned. All sensitive data for the selected users has been removed.', 'Queue', 'success');
  }

  /**
   * CRM_Avg_Page_BatchAnonymizer::Anonymize()
   *
   * @param mixed $ctx
   * @param mixed $contact_id
   *  $contact_id - CiviCRM Contact ID to anoymize
   * @param mixed $values
   *  $values - Selected checkboxes in from form SelectData
   *
   * @return TRUE
   */
  public static function Anonymize(CRM_Queue_TaskContext $ctx, $contact_id, $values) {
    $AvgUtils = new CRM_Avg_Utils($contact_id);

    if(!empty($values['block_useraccount']) && $values['block_useraccount'] == 'yes') {
      $AvgUtils->blockUserAccount();
    }
    if(!empty($values['remove_drupalroles']) && $values['remove_drupalroles'] == 'yes') {
      $AvgUtils->removeDrupalRolesOfUser();
    }
    if(!empty($values['remove_name']) && $values['remove_name'] == 'yes') {
      $AvgUtils->removeName();
    }
    if(!empty($values['remove_namefromcasetitles']) && $values['remove_namefromcasetitles'] == 'yes') {
      $AvgUtils->removeNameFromCaseTitles();
    }
    if(!empty($values['remove_personaldata']) && $values['remove_personaldata'] == 'yes') {
      $AvgUtils->removePersonalData();
    }
    if(!empty($values['remove_additionaldata']) && $values['remove_additionaldata'] == 'yes') {
      $AvgUtils->removeAdditionalData();
    }
    if(!empty($values['remove_jobtitle']) && $values['remove_jobtitle'] == 'yes') {
      $AvgUtils->removeJobTitle();
    }
    if(!empty($values['remove_passportinformation']) && $values['remove_passportinformation'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('Passport Information');
    }
    if(!empty($values['remove_incaseofemergency']) && $values['remove_incaseofemergency'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('In Case of Emergency Contact');
    }
    if(!empty($values['remove_bankinformation']) && $values['remove_bankinformation'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('Bank Information');
    }
    if(!empty($values['remove_nationality']) && $values['remove_nationality'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('Nationality');
    }
    if(!empty($values['remove_medical']) && $values['remove_medical'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('Medical Information');
    }
    if(!empty($values['remove_flight']) && $values['remove_flight'] == 'yes') {
      $AvgUtils->removeCustomGroupDataOfContact('Flight information');
    }
    if(!empty($values['remove_expertdata']) && $values['remove_expertdata'] == 'yes') {
      $AvgUtils->removeExpertData();
    }
    if(!empty($values['remove_gender']) && $values['remove_gender'] == 'yes') {
      $AvgUtils->removeGender();
    }
    if(!empty($values['remove_addresses']) && $values['remove_addresses'] == 'yes') {
      $AvgUtils->removeAddresses();
    }
    if(!empty($values['remove_mailaddresses']) && $values['remove_mailaddresses'] == 'yes') {
      $AvgUtils->removeMailAddresses();
    }
    if(!empty($values['remove_phonenumbers']) && $values['remove_phonenumbers'] == 'yes') {
      $AvgUtils->removePhoneNumbers();
    }
    if(!empty($values['remove_workhistory']) && $values['remove_workhistory'] == 'yes') {
      $AvgUtils->removeWorkhistory();
    }
    if(!empty($values['remove_education']) && $values['remove_education'] == 'yes') {
      $AvgUtils->removeEducation();
    }
    if(!empty($values['remove_languages']) && $values['remove_languages'] == 'yes') {
      $AvgUtils->removeLanguages();
    }
    if(!empty($values['remove_groups']) && $values['remove_languages'] == 'yes') {
      $AvgUtils->removeFromGroups();
    }
    if(!empty($values['remove_contactsegments']) && $values['remove_contactsegments'] == 'yes') {
      $AvgUtils->removeContactSegments();
    }
    if(!empty($values['remove_documents']) && $values['remove_documents'] == 'yes') {
      $AvgUtils->removeDocuments();
    }

    //Extra parameter for in batch
    if(!empty($values['anonymize']) && $values['anonymize'] == 'yes') {
      $AvgUtils->addUserToAnonymizedUsers();
    }
    if(!empty($values['clean']) && $values['clean'] == 'yes') {
      $AvgUtils->addUserToCleanedInactiveUsers();
    }

    return true;
  }
}
