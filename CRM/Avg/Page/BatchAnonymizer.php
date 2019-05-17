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
      'title' => ts('Batch cleaner anonymizer'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_ABORT, //abort upon error and keep task in queue
      'onEnd' => array('CRM_Avg_Page_BatchAnonymizer', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'), //go to page after all tasks are finished
    ));

    $runner->runAllViaWeb();
  }

  /**
   * Handle the final step of the queue
   */
  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    $msg = 'All selected users are anonymized or cleaned. All sensitive data for the selected users has been removed.';
    CRM_Core_Session::setStatus($msg, 'Queue', 'success');
    CRM_Core_Error::debug_log_message($msg);

    //$session = CRM_Core_Session::singleton();
    //$session->pushUserContext(CRM_Utils_System::url('civicrm', 'reset=1', true));

    $result = array();
    $result['is_error'] = 0;
    $result['numberOfItems'] = 0;
    $result['is_continue'] = 0;
    if (!empty($ctx->onEndUrl)) {
      $result['redirect_url'] = $ctx->onEndUrl;
    }

    return $result;
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
   * @return true
   */
  public static function Anonymize(CRM_Queue_TaskContext $ctx, $contact_id, $values) {
    CRM_Core_Error::debug_log_message('AVG: Start processing cleanup');

    if(is_array($contact_id)){
      CRM_Core_Error::debug_log_message('AVG: Contact IDs to clean '.print_r($contact_id,TRUE));

      foreach($contact_id as $cid){
        CRM_Core_Error::debug_log_message('Processing cid: '.$cid);
        self::cleanUser($cid, $values);
      }
    } else {
        CRM_Core_Error::debug_log_message('AVG: Contact IDs to clean '.$contact_id);
        CRM_Core_Error::debug_log_message('Processing cid: '.$contact_id);
        self::cleanUser($contact_id, $values);
    }

    CRM_Core_Error::debug_log_message('AVG: Finished processing all users');

    return true;
  }

  public static function cleanUser($cid, $values) {
    try{
      $AvgUtils = new CRM_Avg_Utils($cid);

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

      $AvgUtils->removeCustomGroupDataOfContact(
        array(
          'Passport Information' => $values['remove_passportinformation'],
          'In Case of Emergency Contact' => $values['remove_incaseofemergency'],
          'Bank Information' => $values['remove_bankinformation'],
          'Nationality' => $values['remove_nationality'],
          'Medical Information' => $values['remove_medical'],
          'Flight information' => $values['remove_flight']
        )
      );

      if(!empty($values['remove_expertdata']) && $values['remove_expertdata'] == 'yes') {
        $AvgUtils->removeExpertData();
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

      if(!empty($values['remove_groups']) && $values['remove_groups'] == 'yes') {
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
    }
    catch (Exception $e) {
      CRM_Core_Error::debug_log_message($e->getMessage());
    }
  }
}
