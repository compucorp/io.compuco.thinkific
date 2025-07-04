<?php

require_once 'thinkific.civix.php';

use CRM_Thinkific_ExtensionUtil as E;
use Civi\Thinkific\Hook\BuildForm\Event;
use Civi\Thinkific\Hook\FieldOptions\EventCreation;
use Civi\Thinkific\Hook\Post\Participant as PostParticipant;
use Civi\Thinkific\Hook\Pre\Participant as PreParticipant;
use Civi\Thinkific\Hook\PostProcess\ParticipantRegistration;

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

function thinkific_civicrm_fieldOptions(string $entity, string $field, ?array &$options) {
  $hooks = [];
  if (EventCreation::shouldRun($entity, $field)) {
    $hooks[] = new EventCreation($field, $options);
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

function thinkific_civicrm_buildForm(string $formName, CRM_Core_Form $form) {
  $hooks = [];
  if (Event::shouldRun($formName, $form)) {
    $hooks[] = new Event();
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

function thinkific_civicrm_post(string $op, string $objectName, $objectId, &$objectRef) {
  $hooks = [];
  if (PostParticipant::shouldRun($op, $objectName, (int) $objectId, $objectRef)) {
    $hooks[] = new PostParticipant((int) $objectId, $objectRef);
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

function thinkific_civicrm_container($container) {
  $containers = [
    new \Civi\Thinkific\Hook\Container\ServiceContainer($container),
  ];

  foreach ($containers as $container) {
    $container->register();
  }
}

function thinkific_civicrm_pageRun($page): void {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Contact_Page_View_Summary') {
    CRM_Core_Resources::singleton()->addStyle('details.customFieldGroup {word-wrap: break-word;} ');
  }
}

function thinkific_civicrm_postProcess($formName, $form): void {
  $hooks = [];
  if (ParticipantRegistration::shouldRun($formName, $form)) {
    $hooks[] = new ParticipantRegistration();
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

function thinkific_civicrm_pre(string $op, string $objectName, ?int $objectId, &$params) {
  $hooks = [];
  if (PreParticipant::shouldRun($op, $objectName, $params)) {
    $hooks[] = new PreParticipant($params);
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}
