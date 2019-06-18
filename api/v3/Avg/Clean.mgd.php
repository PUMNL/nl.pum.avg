<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
  array (
    'name' => 'Cron:Avg.Clean',
    'entity' => 'Job',
    'params' =>
    array (
      'version' => 3,
      'name' => 'AVG: Clean Inactive Users',
      'description' => 'Job to clean all users that have exit status',
      'run_frequency' => 'Daily',
      'api_entity' => 'Avg',
      'api_action' => 'Clean',
      'parameters' => '',
    ),
  ),
);
