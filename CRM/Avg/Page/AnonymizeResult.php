<?php
use CRM_Avg_ExtensionUtil as E;

class CRM_Avg_Page_AnonymizeResult extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle('');

    if(!empty($_GET['cid'])) {
      //Get display name of user
      $contact_params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $_GET['cid'],
      );
      $contact = civicrm_api('Contact', 'get', $contact_params);

      $this->assign('cid', $_GET['cid']);
    }

    if(!empty($contact['values'][0]['display_name'])) {
      $this->assign('display_name', $contact['values'][0]['display_name']);
    } else {
      $this->assign('display_name', 'Display Name: ');
    }

    if(!empty($_GET['ua']) && ($_GET['ua'] == 'yes' | $_GET['ua'] == 'no')) {
      $this->assign('ua', 'User account blocked: '.$_GET['ua']);
    } else {
      $this->assign('ua', 'User account blocked: no');
    }

    if(!empty($_GET['dr']) && ($_GET['dr'] == 'yes' | $_GET['dr'] == 'no')) {
      $this->assign('dr', 'Drupal roles removed: '.$_GET['dr']);
    } else {
      $this->assign('dr', 'Drupal roles removed: no');
    }

    if(!empty($_GET['nm']) && ($_GET['nm'] == 'yes' | $_GET['nm'] == 'no')) {
      $this->assign('nm', 'Name removed: '.$_GET['nm']);
    } else {
      $this->assign('nm', 'Name removed: no');
    }

    if(!empty($_GET['ct']) && ($_GET['ct'] == 'yes' | $_GET['ct'] == 'no')) {
      $this->assign('ct', 'Name from case titles removed: '.$_GET['ct']);
    } else {
      $this->assign('ct', 'Name from case titles removed: no');
    }

    if(!empty($_GET['pd']) && ($_GET['pd'] == 'yes' | $_GET['pd'] == 'no')) {
      $this->assign('pd', 'Personal data removed: '.$_GET['pd']);
    } else {
      $this->assign('pd', 'Personal data removed: no');
    }

    if(!empty($_GET['jt']) && ($_GET['jt'] == 'yes' | $_GET['jt'] == 'no')) {
      $this->assign('jt', 'Job title removed: '.$_GET['jt']);
    } else {
      $this->assign('jt', 'Job title removed: no');
    }

    if(!empty($_GET['pi']) && ($_GET['pi'] == 'yes' | $_GET['pi'] == 'no')) {
      $this->assign('pi', 'Passport information removed: '.$_GET['pi']);
    } else {
      $this->assign('pi', 'Passport information removed: no');
    }

    if(!empty($_GET['em']) && ($_GET['em'] == 'yes' | $_GET['em'] == 'no')) {
      $this->assign('em', 'In case of emergency information removed: '.$_GET['em']);
    } else {
      $this->assign('em', 'In case of emergency information removed: no');
    }

    if(!empty($_GET['bk']) && ($_GET['bk'] == 'yes' | $_GET['bk'] == 'no')) {
      $this->assign('bk', 'Bank information removed: '.$_GET['bk']);
    } else {
      $this->assign('bk', 'Bank information removed: no');
    }

    if(!empty($_GET['nn']) && ($_GET['nn'] == 'yes' | $_GET['nn'] == 'no')) {
      $this->assign('nn', 'Nationality removed: '.$_GET['nn']);
    } else {
      $this->assign('nn', 'Nationality removed: no');
    }

    if(!empty($_GET['md']) && ($_GET['md'] == 'yes' | $_GET['md'] == 'no')) {
      $this->assign('md', 'Medical information removed: '.$_GET['md']);
    } else {
      $this->assign('md', 'Medical information removed: no');
    }

    if(!empty($_GET['fl']) && ($_GET['fl'] == 'yes' | $_GET['fl'] == 'no')) {
      $this->assign('fl', 'Flight information removed: '.$_GET['fl']);
    } else {
      $this->assign('fl', 'Flight information removed: no');
    }

    if(!empty($_GET['ed']) && ($_GET['ed'] == 'yes' | $_GET['ed'] == 'no')) {
      $this->assign('ed', 'Expert data removed: '.$_GET['ed']);
    } else {
      $this->assign('ed', 'Expert data removed: no');
    }

    if(!empty($_GET['ad']) && ($_GET['ad'] == 'yes' | $_GET['ad'] == 'no')) {
      $this->assign('ad', 'Addresses removed: '.$_GET['ad']);
    } else {
      $this->assign('ad', 'Addresses removed: no');
    }

    if(!empty($_GET['ma']) && ($_GET['ma'] == 'yes' | $_GET['ma'] == 'no')) {
      $this->assign('ma', 'Mail addresses removed: '.$_GET['ma']);
    } else {
      $this->assign('ma', 'Mail addresses removed: no');
    }

    if(!empty($_GET['pn']) && ($_GET['pn'] == 'yes' | $_GET['pn'] == 'no')) {
      $this->assign('pn', 'Phone numbers removed: '.$_GET['pn']);
    } else {
      $this->assign('pn', 'Phone numbers removed: no');
    }

    if(!empty($_GET['wh']) && ($_GET['wh'] == 'yes' | $_GET['wh'] == 'no')) {
      $this->assign('wh', 'Work history removed: '.$_GET['wh']);
    } else {
      $this->assign('wh', 'Work history removed: no');
    }

    if(!empty($_GET['ec']) && ($_GET['ec'] == 'yes' | $_GET['ec'] == 'no')) {
      $this->assign('ec', 'Education data removed: '.$_GET['ec']);
    } else {
      $this->assign('ec', 'Education data removed: no');
    }

    if(!empty($_GET['ln']) && ($_GET['ln'] == 'yes' | $_GET['ln'] == 'no')) {
      $this->assign('ln', 'Language data removed: '.$_GET['ln']);
    } else {
      $this->assign('ln', 'Language data removed: no');
    }

    if(!empty($_GET['gr']) && ($_GET['gr'] == 'yes' | $_GET['gr'] == 'no')) {
      $this->assign('gr', 'Groups removed: '.$_GET['gr']);
    } else {
      $this->assign('gr', 'Groups removed: no');
    }

    if(!empty($_GET['cs']) && ($_GET['cs'] == 'yes' | $_GET['cs'] == 'no')) {
      $this->assign('cs', 'Contact segment data removed: '.$_GET['cs']);
    } else {
      $this->assign('cs', 'Contact segment data removed: no');
    }

    if(!empty($_GET['do']) && ($_GET['do'] == 'yes' | $_GET['do'] == 'no')) {
      $this->assign('do', 'Documents removed: '.$_GET['do']);
    } else {
      $this->assign('do', 'Documents removed: no');
    }

    $this->assign('back_to_contact','<a href="'.CRM_Utils_System::url('civicrm/contact/view','action=view&reset=1&cid='.$_GET['cid']).'" class="button">&lt;&lt; Back to contact</a>');

    parent::run();
  }

}
