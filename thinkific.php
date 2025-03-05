<?php

require_once 'thinkific.civix.php';

use CRM_Thinkific_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function thinkific_civicrm_config(&$config): void {
  _thinkific_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function thinkific_civicrm_install(): void {
  _thinkific_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function thinkific_civicrm_enable(): void {
  _thinkific_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function thinkific_civicrm_navigationMenu(&$menu) {
  _thinkific_civix_insert_navigation_menu($menu, 'Administer/CiviEvent', array(
    'label' => E::ts('Thinkific LMS Settings'),
    'name' => 'thinkific_lms_settings',
    'url' => 'civicrm/admin/setting/preferences/thinkific',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _thinkific_civix_navigationMenu($menu);
}
