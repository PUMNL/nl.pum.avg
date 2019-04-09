<?php

use CRM_Avg_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Avg_Form_AnonymizeUser extends CRM_Core_Form {
  public function buildQuickForm() {
    $cid = NULL;

    try{
      $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
      if(empty($cid) && !empty($_GET['cid'])){
        $cid = $_GET['cid'];
      }
    } catch (Exception $e) {

    }

    CRM_Core_Resources::singleton()->addScriptFile('nl.pum.avg', 'anonymize_user.js');

    if(!empty($cid)) {
      //Get display name of user
      $contact_params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $cid,
      );
      $contact = civicrm_api('Contact', 'get', $contact_params);

      if(!empty($contact['values'][0]['display_name'])) {
        CRM_Utils_System::setTitle(ts('Anonimize User: '.$contact['values'][0]['display_name']));
      } else {
        CRM_Utils_System::setTitle(ts('Anonimize User ID: '.$cid));
      }

      // add form elements
      $this->add(
        'select',                   // field type
        'block_useraccount',        // field name
        'Block User Account',       // field label
        $this->getYesNoOptions(),   // list of options
        FALSE                       // is required
      );
      $this->add(
        'select',
        'remove_drupalroles',
        'Remove drupal roles from user account',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_name',
        'Name',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_namefromcasetitles',
        'Name from case titles',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_personaldata',
        'Personal Data',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_additionaldata',
        'Additional Data',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_jobtitle',
        'Job Title',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_passportinformation',
        'Passport Information',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_incaseofemergency',
        'In case of emergency information',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_bankinformation',
        'Bank information',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_nationality',
        'Nationality',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_medical',
        'Medical Information',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_flight',
        'Flight Information',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_expertdata',
        'Expert Data',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_gender',
        'Gender',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_addresses',
        'Addresses',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_mailaddresses',
        'Mail addresses',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_phonenumbers',
        'Phone Numbers',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_workhistory',
        'Work History',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_education',
        'Education',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_languages',
        'Languages',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_groups',
        'Remove from groups',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_contactsegments',
        'Remove contact segments',
        $this->getYesNoOptions(),
        FALSE
      );
      $this->add(
        'select',
        'remove_documents',
        'Remove documents',
        $this->getYesNoOptions(),
        FALSE
      );

      $this->assign('display_name',$contact['values'][0]['display_name']);
      $this->assign('back_to_contact','<a href="'.CRM_Utils_System::url('civicrm/contact/view','action=view&reset=1&cid='.CRM_Utils_Request::retrieve('cid', 'Integer')).'" class="button">&lt;&lt; Back to contact</a>');
      $this->assign('set_all_buttons_to_yes','<a href="#" class="button" onclick="anonymize_groups_yes();">Set all groups to "Yes"</a>');
      $this->assign('set_all_buttons_to_no','<a href="#" class="button" onclick="anonymize_groups_no();">Set all groups to "No"</a>');
      $this->add('hidden','cid',CRM_Utils_Request::retrieve('cid', 'Integer'));
      $this->addButtons(array(
        array(
          'type' => 'submit',
          'name' => E::ts('Anonymize: '.$contact['values'][0]['display_name'].' now'),
          'isDefault' => TRUE,
        ),
      ));
    } else {
      CRM_Core_Session::setStatus('Unable to retrieve contact id ','error');
    }

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    try{
      $cid = CRM_Utils_Request::retrieve('cid', 'Integer');
    } catch(Exception $ex){

    }
    if(empty($cid)) {
      parent::postProcess();
      return FALSE;
    }

    $AvgUtils = new CRM_Avg_Utils($cid);
    if($AvgUtils == FALSE){
      parent::postProcess();
      return FALSE;
    }
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
      $AvgUtils->removeCustomGroupDataOfContact('Nationality');
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

    $AvgUtils->addUserToAnonymizedUsers();

    $cid = CRM_Utils_Request::retrieve('cid', 'Integer');
    if(!empty($cid)){
      CRM_Utils_System::redirect( 'anonymize/complete?cid='.$cid
                                  .'&ua='.$values['block_useraccount']
                                  .'&dr='.$values['remove_drupalroles']
                                  .'&nm='.$values['remove_name']
                                  .'&ct='.$values['remove_namefromcasetitles']
                                  .'&pd='.$values['remove_personaldata']
                                  .'&al='.$values['remove_additionaldata']
                                  .'&jt='.$values['remove_jobtitle']
                                  .'&pi='.$values['remove_passportinformation']
                                  .'&em='.$values['remove_incaseofemergency']
                                  .'&bk='.$values['remove_bankinformation']
                                  .'&nn='.$values['remove_nationality']
                                  .'&md='.$values['remove_medical']
                                  .'&fl='.$values['remove_flight']
                                  .'&ed='.$values['remove_expertdata']
                                  .'&rg='.$values['remove_gender']
                                  .'&ad='.$values['remove_addresses']
                                  .'&ma='.$values['remove_mailaddresses']
                                  .'&pn='.$values['remove_phonenumbers']
                                  .'&wh='.$values['remove_workhistory']
                                  .'&ec='.$values['remove_education']
                                  .'&ln='.$values['remove_languages']
                                  .'&gr='.$values['remove_groups']
                                  .'&cs='.$values['remove_contactsegments']
                                  .'&do='.$values['remove_documents']);
    } else {
      CRM_Utils_System::redirect('anonymize/complete');
    }

    parent::postProcess();
  }

  public function getYesNoOptions() {
    $options = array(
      'no' => E::ts('No'),
      'yes' => E::ts('Yes'),
    );

    return $options;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}