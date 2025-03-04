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
