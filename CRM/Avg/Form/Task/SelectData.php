<?php

class CRM_Avg_Form_Task_SelectData extends CRM_Contact_Form_Task {

  function buildQuickForm() {
    CRM_Core_Resources::singleton()->addScriptFile('nl.pum.avg', 'anonymize_user.js');
    // add form elements
    $this->addElement(
      'select', // field type
      'block_useraccount', // field name
      'Block User Account', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_drupalroles', // field name
      'Remove drupal roles from user account', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_name', // field name
      'Name', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_namefromcasetitles', // field name
      'Name from case titles', // field label
      $this->getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_personaldata', // field name
      'Personal Data', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_additionaldata', // field name
      'Additional Data', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_jobtitle', // field name
      'Job title', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_passportinformation', // field name
      'Passport Information', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_incaseofemergency', // field name
      'In case of emergency information', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_bankinformation', // field name
      'Bank information', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_nationality', // field name
      'Nationality', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_medical', // field name
      'Medical Information', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_flight', // field name
      'Flight Information', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_expertdata', // field name
      'Expert Data', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_addresses', // field name
      'Addresses', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_mailaddresses', // field name
      'Mail addresses', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_phonenumbers', // field name
      'Phone Numbers', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_workhistory', // field name
      'Work History', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_education', // field name
      'Education', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_languages', // field name
      'Languages', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_groups', // field name
      'Remove from groups', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_contactsegments', // field name
      'Remove contact segments', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );
    $this->addElement(
      'select', // field type
      'remove_documents', // field name
      'Remove documents', // field label
      self::getYesNoOptions(), // list of options
      FALSE // is required
    );

    $this->assign('back_to_civicrm','<a href="'.CRM_Utils_System::url('civicrm/contact/search','reset=1').'" class="button">&lt;&lt; Back to contact search</a>');
    $this->assign('set_all_buttons_to_yes','<a href="#" class="button" onclick="anonymize_groups_yes();">Set all groups to "Yes"</a>');
    $this->assign('set_all_buttons_to_no','<a href="#" class="button" onclick="anonymize_groups_no();">Set all groups to "No"</a>');

    $this->assign('elementNames', $this->getRenderableElementNames());

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Anonymize users now'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    //$this->assign('elementNames', $this->getRenderableElementNames());
  }

  public function postProcess() {

    $selected_checkboxes = $this->controller->exportValues();

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => 'nl.pum.avg',
      'reset' => false, //do not flush queue upon creation
    ));

    foreach($this->_contactIds as $cid) {
      //create a task
      $task = new CRM_Queue_Task(
        array('CRM_Avg_Page_BatchAnonymizer', 'Anonymize'), //call back method
        array($cid,$selected_checkboxes)
      );
      //now add this task to the queue
      $queue->createItem($task);
    }

    $url = CRM_Utils_System::url('civicrm/avg/batch_anonymizer');
    CRM_Utils_System::redirect($url);

  }

  public static function getYesNoOptions() {
    $options = array(
      'no' => ts('No'),
      'yes' => ts('Yes'),
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