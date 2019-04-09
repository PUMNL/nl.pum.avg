<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Avg_Form_Report_AnonymizeExitUsers',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'AnonymizeExitUsers',
      'description' => 'AnonymizeExitUsers (nl.pum.avg)',
      'class_name' => 'CRM_Avg_Form_Report_AnonymizeExitUsers',
      'report_url' => 'anonymizeexitusers',
      'component' => '',
    ),
  ),
);
