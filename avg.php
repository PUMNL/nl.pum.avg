<?php

require_once 'avg.civix.php';
use CRM_Avg_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function avg_civicrm_config(&$config) {
  _avg_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function avg_civicrm_xmlMenu(&$files) {
  _avg_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function avg_civicrm_install() {
  _avg_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function avg_civicrm_postInstall() {
  _avg_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function avg_civicrm_uninstall() {
  _avg_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function avg_civicrm_enable() {
  _avg_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function avg_civicrm_disable() {
  _avg_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function avg_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _avg_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function avg_civicrm_managed(&$entities) {
  _avg_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function avg_civicrm_caseTypes(&$caseTypes) {
  _avg_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function avg_civicrm_angularModules(&$angularModules) {
  _avg_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function avg_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _avg_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function avg_civicrm_entityTypes(&$entityTypes) {
  _avg_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function avg_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function avg_civicrm_navigationMenu(&$menu) {
  _avg_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _avg_civix_navigationMenu($menu);
} // */

function avg_civicrm_summaryActions(&$actions, $contactID) {
  $actions['anonymize'] = array(
    'title' => 'Anonymize User',
    'weight' => 0,
    'ref' => 'anonymize-user',
    'key' => 'anonymize-user',
    'href' => '/civicrm/avg/anonymize/'.$contactID
  );
}

/**
 * Implementation of hook_civicrm_searchTasks
 *
 * Add a task for anonymizing users to the search task list
 *
 * @param $objectType
 * @param $tasks
 */
function avg_civicrm_searchTasks( $objectType, &$tasks ) {
  if ($objectType == 'contact') {
    $permission = CRM_Core_Permission::check('administer CiviCRM');
    if (!$permission) {
      return;
    } else {
      $tasks[] = array(
        'title' => ts('Anonymize users'),
        'class' => 'CRM_Avg_Form_Task_SelectData',
        'result' => FALSE
      );
    }
  }
}

/**
 * Implements hook_civicrm_permission
 *
 * @param $permissions
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function avg_civicrm_permission(&$permissions) {
  $prefix = ts('CiviCRM AVG') . ': ';
  $permissions['avg_anonymize_user'] = $prefix . ts('anonymize a single user');
  $permissions['avg_batch_anonymize_users'] = $prefix . ts('anonymize users using batch');
  $permissions['avg_batch_clean_inactive_users'] = $prefix . ts('clean inactive users using batch');
}

function avg_civicrm_pre($op, $objectName, $objectId, &$objectRef){
  if($op == 'create' && $objectName == 'ReportInstance' && !empty($objectId) && !empty($objectRef->report_id) && $objectRef->report_id == 'anonymizeexitusers'){
    $params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $objectId,
      'title' => 'Anonymize Exit Users',
      'description' => 'Anonymize Exit Users',
      'permission' => 'avg_batch_anonymize_users',
    );
    $result = civicrm_api('ReportInstance', 'update', $params);
  }

  if($op == 'create' && $objectName == 'ReportInstance' && !empty($objectId) && !empty($objectRef->report_id) && $objectRef->report_id == 'cleaninactiveusers'){
    $params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $objectId,
      'title' => 'Clean Inactive Users',
      'description' => 'Clean Inactive Users',
      'permission' => 'avg_batch_clean_inactive_users',
    );
    $result = civicrm_api('ReportInstance', 'update', $params);
  }
}