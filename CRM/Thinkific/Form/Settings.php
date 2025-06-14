<?php

use CRM_Thinkific_ExtensionUtil as E;
use Civi\Thinkific\SettingsManager;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Thinkific_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm(): void {
    CRM_Utils_System::setTitle(E::ts('Thinkfic LMS Settings'));
    $this->add('password', SettingsManager::API_KEY, E::ts('Thinkific API key'), NULL, TRUE);
    $this->add('text', SettingsManager::SUBDOMAIN, E::ts('Thinkific Sub domain'), NULL, TRUE);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess(): void {
    $values = $this->exportValues();

    try {
      /** @var Civi\Thinkific\Service\ApiClient $client */
      $client = Civi::service('service.thinkific.api_client');
      $headers = [
        'X-Auth-API-Key' => $values[SettingsManager::API_KEY],
        'X-Auth-Subdomain' => $values[SettingsManager::SUBDOMAIN],
        'Content-Type' => 'application/json',
      ];
      $client->request('GET', 'courses', $headers);
      /** @var array<string, mixed> $result */
      $result = civicrm_api3('Setting', 'create', [
        SettingsManager::API_KEY => $values[SettingsManager::API_KEY],
        SettingsManager::SUBDOMAIN => $values[SettingsManager::SUBDOMAIN],
      ]);
      if ($result['is_error'] == 0) {
        CRM_Core_Session::singleton()->setStatus(E::ts('Connection success.'), E::ts('Thinkfic Learning Management System Settings'), 'success');
      }
    }
    catch (Throwable $e) {
      Civi::log()->error('LMS Setting error ' . $e->getMessage());
      $msg = 'An issue has occurred connecting to the Thinkific platform. Please contact your administrator.';
      if ($e instanceof BadResponseException) {
        $msg .= ' Error code: ' . $e->getResponse()->getStatusCode();
      }
      CRM_Core_Session::singleton()->setStatus(
        $msg,
        E::ts('Thinkfic Learning Management System Settings'),
        'error'
      );
    }

    parent::postProcess();
  }

  /**
   * Set defaults for form.
   *
   * @return array<string, mixed>
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues(): array {
    $defaults = [];
    $domainId = CRM_Core_Config::domainID();

    /** @var array<string, array<int, array<string, mixed>>> $currentValues */
    $currentValues = civicrm_api3('Setting', 'get', ['return' => [SettingsManager::API_KEY, SettingsManager::SUBDOMAIN]]);

    if (isset($currentValues['values'][$domainId])) {
      foreach ($currentValues['values'][$domainId] as $name => $value) {
        $defaults[$name] = $value;
      }
    }
    return $defaults;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return list<string>
   */
  public function getRenderableElementNames(): array {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
