<?php

use CRM_Thinkific_ExtensionUtil as E;
use CRM_Thinkific_SettingsManager as SettingsManager;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Thinkific_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm(): void {
    CRM_Utils_System::setTitle(E::ts('Thinkfic LMS Settings'));
    $this->add('password', SettingsManager::API_KEY, E::ts('Thinkfic Api Key'), NULL, TRUE);
    $this->add('text', SettingsManager::SUBDOMAIN, E::ts('Thinkfic Subdomain'), NULL, TRUE);

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

    /** @var array<string, mixed> $result */
    $result = civicrm_api3('setting', 'create', [
      SettingsManager::API_KEY => $values[SettingsManager::API_KEY],
      SettingsManager::SUBDOMAIN => $values[SettingsManager::SUBDOMAIN],
    ]);

    if ($result['is_error'] == 0) {
      CRM_Core_Session::singleton()->setStatus(E::ts('Connection success.'), E::ts('Thinkfic LMS Settings'), 'success');
    }
    else {
      CRM_Core_Session::singleton()->setStatus(
        E::ts('An issue has occurred connecting to the Thinkific platform. Please contact your administrator. Error code: 00'),
        E::ts('Thinkfic LMS Settings'),
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
    $currentValues = civicrm_api3('setting', 'get', ['return' => [SettingsManager::API_KEY, SettingsManager::SUBDOMAIN]]);

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
    /** @phpstan-ignore property.notFound */
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
