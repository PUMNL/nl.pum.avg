<?php
/**
 * Class with several functions for anonymizing users
 *
 */

class CRM_Avg_Utils {
  protected $cid;

  public function __construct($cid) {
    if(!is_numeric($cid)) {
      try{
        $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer');
      } catch(Exception $ex){

      }
      if(!is_numeric($this->cid)) {
        return FALSE;
      }
    } else {
      $this->cid = $cid;
    }

    set_time_limit(1800); //Run max half an hour
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removePersonalData()
   *
   * Function to remove the personal data from a contact
   *
   * @return void
   */
  public function removePersonalData() {
    $results = array();

    if(is_numeric($this->cid)) {
      //Clear some fields of additional data
        try {
          //Clear Demographics: Birth Date / Age / Contact Image
          $params = array('version' => 3,
                          'sequential' => 1,
                          'id' => $this->cid,
                          'birth_date' => '',
                          'image_URL' => '',
                          'source' => 'Anonymized',
                          'gender_id' => '');
          $results[] = civicrm_api('Contact','update',$params);
        } catch (CiviCRM_API3_Exception $e) {

        }
    }

    return $results;
  }

  public function removeAdditionalData() {
    $results = array();

    if(is_numeric($this->cid)) {
      //Get fields
      $grp_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_custom_group WHERE title = 'Additional Data'");
      if (!empty($grp_id)){
        $field_group_fields_params = array(
          'version' => 3,
          'sequential' => 1,
          'custom_group_id' => $grp_id,
        );

        try {
          $field_group_fields = civicrm_api('CustomField', 'get', $field_group_fields_params);
        } catch (CiviCRM_API3_Exception $e) {

        }
      }

      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->cid
      );

      if(!empty($field_group_fields['values']) && is_array($field_group_fields['values'])){
        //Clear some of the custom fields
        foreach($field_group_fields['values'] as $key => $value) {
          if(in_array($value['label'], array('Registration Date','Initials','City of Birth','Country of Birth','Marital Status','Skype Name','Facebook Address','Twitter account name'))) {
            $params['custom_'.$value['id']] = '';
          }
        }
      }

      try {
        $results = civicrm_api('Contact','update',$params);
      } catch (CiviCRM_API3_Exception $e) {

      }
    }

    return $results;
  }

  /**
   * CRM_Avg_Utils::blockUserAccount()
   *
   * Blocks user account & set notification email_frequency to never
   *
   * @return void
   */
  public function blockUserAccount() {
    if(is_numeric($this->cid)) {
      //Get Drupal ID using CiviCRM contact ID
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $this->cid,
      );

      try {
        $result = civicrm_api('UFMatch', 'get', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }

      if(!empty($result['values']) && is_array($result['values'])){
        foreach($result['values'] as $key => $value){
          //Load drupal user
          if(!empty($value['uf_id'])){
            $users = entity_load('user', array($value['uf_id']));

            foreach ($users as $uid => $user) {
              //Block account
              $user->status = 0;
              $user->name = 'anonymous_'.date('Ymd').rand();
              //Set e-mail frequency to never
              if(isset($user->field_email_frequency['und'][0]['value'])) {
                $user->field_email_frequency['und'][0]['value'] = '+10 years';
              }

              user_save($user);
            }
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeDrupalRolesOfUser()
   *
   * Function to remove drupal roles of a user.
   *
   * @return void
   */
  public function removeDrupalRolesOfUser() {
    if(is_numeric($this->cid)) {
      //Get Drupal ID using CiviCRM contact ID
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $this->cid,
      );

      try {
        $result = civicrm_api('UFMatch', 'get', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }

      if(!empty($result['values']) && is_array($result['values'])){
        foreach($result['values'] as $key => $value){
          //Load drupal user
          $uid = $value['uf_id'];
          $user = user_load($uid);
          //Remove drupal roles
          $user->roles = array();
          user_save($user);
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeJobTitle()
   *
   * Function to remove the job title from a contact
   *
   * @return void
   */
  public function removeJobTitle() {
    if(is_numeric($this->cid)) {
      //Remove job title
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->cid,
        'job_title' => '',
      );

      try {
        $result = civicrm_api('Contact', 'update', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeName()
   *
   * Function to remove the name from a contact
   *
   * @return void
   */
  public function removeName() {
    if(is_numeric($this->cid)) {
      //Remove Name
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->cid,
        'first_name' => '',
        'middle_name' => '',
        'last_name' => 'anonymous_'.date('Ymd'),
        'prefix_id' => '',
      );

      try {
        $result = civicrm_api('Contact', 'update', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }
    }
  }

  /**
   * CRM_Avg_Utils::removeNameFromCaseTitles()
   *
   * Functino to remove the name of the user from case titles
   *
   * @return
   */
  public function removeNameFromCaseTitles() {
    if(is_numeric($this->cid)) {
      try {
        $params_contact = array(
          'version' => 3,
          'sequential' => 1,
          'id' => $this->cid,
        );
        $contact = civicrm_api('Contact', 'getsingle', $params_contact);
      } catch (CiviCRM_API3_Exception $e) {

      }

      try {
        $params_cases = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
        );
        $cases = civicrm_api('Case', 'get', $params_cases);
      } catch (CiviCRM_API3_Exception $e) {

      }

      if(!empty($contact['display_name']) && is_array($cases['values']) && $cases['count'] > 0){
        try {
          $params_casetype_travelcase = array(
            'version' => 3,
            'sequential' => 1,
            'option_group_name' => 'case_type',
            'name' => 'TravelCase',
            'return' => 'label,id',
          );
          $casetype_travelcase_label = civicrm_api('OptionValue', 'get', $params_casetype_travelcase);

          if (!empty($cases['values']) && is_array($cases['values'])){
            foreach($cases['values'] as $key => $value) {
              $params_current_case = array(
                'version' => 3,
                'sequential' => 1,
                'contact_id' => $this->cid,
                'id' => $value['id'],
              );
              $current_case = civicrm_api('Case', 'get', $params_current_case);

              $params_update_current_case = array(
                'version' => 3,
                'sequential' => 1,
                'case_type_id' => $current_case['values'][0]['case_type_id'],
                'id' => $current_case['values'][0]['id'],
                'subject' => $contact['display_name'].'-'.$casetype_travelcase_label['values'][0]['label'].'-'.$current_case['values'][0]['id']
              );
              $update_current_case = civicrm_api('Case', 'update', $params_update_current_case);
            }
          }
        } catch (CiviCRM_API3_Exception $e) {

        }
      }
    }

    if(!empty($update_current_case['is_error']) && $update_current_case['is_error'] == 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * CRM_Avg_Utils::removeCustomGroupDataOfContact()
   *
   * Function to remove all data of the custom groups of a contact
   *
   * @param mixed $custom_group_names
   *  Array of custom_groups, use following format:
   *    array(
   *      'Passport Information'          => 'yes',
   *      'In Case of Emergency Contact'  => 'no',
   *      'Bank Information'              => 'yes',
   *      'Nationality'                   => 'no',
   *      'Medical Information'           => 'yes',
   *      'Flight information'            => 'yes'
   *    )
   * @return void
   */
  public function removeCustomGroupDataOfContact($custom_group_names) {
    if(is_numeric($this->cid)) {
      $groups = array();

      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->cid
      );

      foreach($custom_group_names as $group_name => $remove) {
        if($remove == 'yes') {
          $grp_id = CRM_Core_DAO::singleValueQuery("SELECT `id` FROM `civicrm_custom_group` WHERE `title` = %1 LIMIT 1", array(1 => array($group_name, 'String')));
          $dao_field_ids = CRM_Core_DAO::executeQuery("SELECT `id` FROM `civicrm_custom_field` WHERE `custom_group_id` = %1", array(1 => array($grp_id, 'String')));

          while($dao_field_ids->fetch()){
            $params['custom_'.$dao_field_ids->id] = '';
          }
        }
      }

      $result = civicrm_api('Contact','update',$params);
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeExpertData()
   *
   * Function to remove the expert data from an expert
   *
   * @return void
   */
  public function removeExpertData() {
    if(is_numeric($this->cid)) {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'name' => 'expert_data',
      );

      $result = civicrm_api('CustomGroup', 'get', $params);

      if($result['count'] > 0) {
        foreach($result['values'] as $key=>$value) {
          if(!empty($value['table_name'])){
            try {
              $sql = CRM_Core_DAO::executeQuery("DELETE FROM {$value['table_name']} WHERE entity_id={$this->cid}");
            } catch (Exception $e) {
              CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
            }
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeAddresses()
   *
   * Function to remove all addresses from a contact
   *
   * @return void
   */
  public function removeAddresses() {
    //Get all addresses of contact
    if(is_numeric($this->cid)) {
      try {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
        );
        $addresses = civicrm_api('Address', 'get', $params);

        if(!empty($addresses['values']) && is_array($addresses['values'])) {
          //Remove all addresses of contact
          foreach($addresses['values'] as $key => $value) {
            if(!empty($value['id'])){
              $params = array(
                'version' => 3,
                'sequential' => 1,
                'id' => $value['id'],
              );
              $result = civicrm_api('Address', 'delete', $params);
            }
          }
        }
      } catch (CiviCRM_API3_Exception $e) {

      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removePhoneNumbers()
   *
   * Function to remove all phone numbers from a contact
   *
   * @return void
   */
  public function removePhoneNumbers() {
    //Get all phone numbers of contact
    try {
      if(is_numeric($this->cid)) {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
        );
        $phone_numbers = civicrm_api('Phone', 'get', $params);
      }

      if(!empty($phone_numbers['values']) && is_array($phone_numbers['values'])) {
        //Remove all phone numbers of contact
        foreach($phone_numbers['values'] as $key => $value) {
          if(!empty($value['id'])){
            $params = array(
              'version' => 3,
              'sequential' => 1,
              'id' => $value['id'],
            );
            $result = civicrm_api('Phone', 'delete', $params);
          }
        }
      }
    } catch (CiviCRM_API3_Exception $e) {

    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeMailAddresses()
   *
   * Function to remove all mail addresses from a contact
   *
   * @return void
   */
  public function removeMailAddresses() {
    //Get all mail addresses of contact
    if(is_numeric($this->cid)) {
      try {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
        );
        $mail_addresses = civicrm_api('Email', 'get', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }

      if(!empty($mail_addresses['values']) && is_array($mail_addresses['values'])) {
        //Remove all mail addresses of contact from contact card
        foreach($mail_addresses['values'] as $key => $value) {
          if(!empty($value['id'])){
            $params = array(
              'version' => 3,
              'sequential' => 1,
              'id' => $value['id'],
            );
            try {
              $result = civicrm_api('Email', 'delete', $params);
            } catch (CiviCRM_API3_Exception $e) {

            }
          }
        }
      }

      //Remove mail address from drupal account, get Drupal ID using CiviCRM contact ID
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $this->cid,
      );
      try {
        $result = civicrm_api('UFMatch', 'get', $params);
      } catch (CiviCRM_API3_Exception $e) {

      }

      if(!empty($result['values']) && is_array($result['values'])){
        foreach($result['values'] as $key => $value){
          //Load drupal user
          $users = entity_load('user', array($value['uf_id']));

          foreach ($users as $uid => $user) {
            $user->mail = $user->name.'@domain.example';
            user_save($user);
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeWorkhistory()
   *
   * Function to remove all work history from a contact
   *
   * @return void
   */
  public function removeWorkhistory() {
    if(is_numeric($this->cid)) {
      try{
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'name' => 'Workhistory',
        );

        $result = civicrm_api('CustomGroup', 'get', $params);
      } catch(Exception $e){

      }

      if($result['count'] > 0) {
        foreach($result['values'] as $key=>$value) {
          if(!empty($value['table_name'])){
            try {
              $sql = CRM_Core_DAO::executeQuery("DELETE FROM {$value['table_name']} WHERE entity_id={$this->cid}");
            } catch (Exception $e) {
              CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
            }
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeEducation()
   *
   * Function to removal all education data from a contact
   *
   * @return void
   */
  public function removeEducation() {
    if(is_numeric($this->cid)) {
      try{
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'name' => 'Education',
        );

        $result = civicrm_api('CustomGroup', 'get', $params);
      } catch(Exception $e){

      }

      if($result['count'] > 0) {
        foreach($result['values'] as $key=>$value) {
          if(!empty($value['table_name'])){
            try {
              $sql = CRM_Core_DAO::executeQuery("DELETE FROM {$value['table_name']} WHERE entity_id={$this->cid}");
            } catch (Exception $e) {
              CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
            }
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeLanguages()
   *
   * Function to removal all languages from a contact
   *
   * @return void
   */
  public function removeLanguages() {
    if(is_numeric($this->cid)) {
      try{
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'name' => 'Languages',
        );

        $result = civicrm_api('CustomGroup', 'get', $params);
      } catch(Exception $e){

      }

      if($result['count'] > 0) {
        foreach($result['values'] as $key=>$value) {
          if(!empty($value['table_name'])){
            try {
              $sql = CRM_Core_DAO::executeQuery("DELETE FROM {$value['table_name']} WHERE entity_id={$this->cid}");
            } catch (Exception $e) {
              CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
            }
          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeFromGroups()
   *
   * Function to remove all groups from a contact
   *
   * @return void
   */
  public function removeFromGroups(){
    if(is_numeric($this->cid)) {
      try{
        //Get all groups from contact
        $groups_params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid
        );
        $groups = civicrm_api('GroupContact', 'get', $groups_params);
      } catch(Exception $e){

      }

      foreach($groups['values'] as $key => $value) {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
          'group_id' => $value['group_id'],
        );
        $result = civicrm_api('GroupContact', 'delete', $params);
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeContactSegments()
   *
   * Function to mark all contactsegments from a contact as past sector
   *
   * @return void
   */
  public function removeContactSegments(){
    if(is_numeric($this->cid)) {
      try{
        //Get all contact segments of contact
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $this->cid,
          'is_active' => 1
        );
        $contact_segments = civicrm_api('ContactSegment', 'get', $params);
      } catch(Exception $e) {

      }

      //Remove all contact segments of contact
      if(!empty($contact_segments['values']) && is_array($contact_segments['values'])){
        foreach($contact_segments['values'] as $key => $value) {
          if(!empty($value['id'])) {
            //$params = array(
            //  'version' => 3,
            //  'sequential' => 1,
            //  //'contact_id' => $this->cid,
            //  'id' => $value['id']
            //);
            //ContactSegment delete API doesn't work: contains a bug, it call to CRM_Contactsegment_BAO_ContactSegment::deleteWithId but that function does not exist
            //It was added in org.civicoop.contactsegment commit: ca182163c0c53f1fa7df2a9cb6398c26724c8496 (13-12-2015)
            //And it was removed in org.civicoop.contactsegment commit: 7613cc7bb0737bec329320c81f2bd31e13c78772 (15-12-2015)
            //However, the Delete API of org.civicoop.contactsegment is still refering to this function.
            //$result = civicrm_api('ContactSegment', 'delete', $params);

            //For now using a manual delete-query.
            $end_date = date('YmdHis');
            try {
              $sql = "UPDATE civicrm_contact_segment SET is_active = %1, end_date = %2 WHERE contact_id = %3";

              CRM_Core_DAO::executeQuery($sql, array(
                1 => array(1, 'Integer'),
                2 => array($end_date, 'String'),
                3 => array($this->cid, 'Integer')
              ));
            } catch (Exception $e) {
              CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
            }

          }
        }
      }
    }
  }

  /**
   * CRM_Avg_Form_AnonymizeUser::removeDocuments()
   *
   * Function to remove all documents from a contact
   *
   * @return void
   */
  public function removeDocuments(){
    try{
      if(is_numeric($this->cid)) {
        $localExtensions = civicrm_api('Extension', 'get', array('version' => 3, 'sequential' => 1));

        $extensionInstalled = FALSE;
        if(!empty($localExtensions['values']) && is_array($localExtensions['values'])) {
          foreach($localExtensions['values'] as $key => $value) {
            if ($value['key'] == 'org.civicoop.documents' && $value['status'] == 'installed') {
              $extensionInstalled = TRUE;
              break;
            }
          }
        }

        if($extensionInstalled == TRUE) {
          $documentRepo = CRM_Documents_Entity_DocumentRepository::singleton();
          $documents = $documentRepo->getDocumentsByContactId($this->cid, FALSE);

          foreach($documents as $doc) {
            $docId = $doc->getId();

            if(!empty($docId)) {
              $documentsRepo = CRM_Documents_Entity_DocumentRepository::singleton();
              $document = $documentsRepo->getDocumentById($docId);
              $documentsRepo->remove($document);
            }
          }
        }
      }
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
    }
  }

  /**
   * CRM_Avg_Utils::addUserToAnonymizedUsers()
   *
   * Function to add an anonymized user to group anonymized users
   *
   * @return void
   */
  public function addUserToAnonymizedUsers(){
    try{
      if(is_numeric($this->cid)) {
        $params_group = array(
          'version' => 3,
          'sequential' => 1,
          'title' => 'Anonymized Users',
        );
        $result_group = civicrm_api('Group', 'get', $params_group);

        if(!empty($result_group['id'])) {
          $params_groupcontact = array(
            'version' => 3,
            'sequential' => 1,
            'contact_id' => $this->cid,
            'group_id' => $result_group['id'],
          );
          $result_groupcontact = civicrm_api('GroupContact', 'create', $params_groupcontact);
        }

        if(!empty($result_groupcontact['is_error']) && $result_groupcontact['is_error'] == 0){
          return TRUE;
        } else {
          return FALSE;
        }
      }
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
    }
  }

  /**
   * CRM_Avg_Utils::addUserToAnonymizedUsers()
   *
   * Function to add an anonymized user to group anonymized users
   *
   * @return void
   */
  public function addUserToCleanedInactiveUsers(){
    try{
      if(is_numeric($this->cid)) {
        $params_group = array(
          'version' => 3,
          'sequential' => 1,
          'title' => 'Cleaned Inactive Users',
        );
        $result_group = civicrm_api('Group', 'get', $params_group);

        if(!empty($result_group['id'])) {
          $params_groupcontact = array(
            'version' => 3,
            'sequential' => 1,
            'contact_id' => $this->cid,
            'group_id' => $result_group['id'],
          );
          $result_groupcontact = civicrm_api('GroupContact', 'create', $params_groupcontact);
        }

        if(!empty($result_groupcontact['is_error']) && $result_groupcontact['is_error'] == 0){
          return TRUE;
        } else {
          return FALSE;
        }
      }
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode()." - ".$e->getMessage(), FALSE);
    }
  }

  public static function batchFinishedExecutionMessage(){
    CRM_Core_Session::setStatus('All tasks in queue have been executed successfully', 'AVG', 'success');
  }
}