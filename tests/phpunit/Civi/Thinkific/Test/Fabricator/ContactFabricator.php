<?php

namespace Civi\Thinkific\Test\Fabricator;

class ContactFabricator extends AbstractBaseFabricator {

  /**
   * Entity's name.
   *
   * @var string
   */
  protected static $entityName = 'Contact';

  /**
   * Array if default parameters to be used to create a contact.
   *
   * @var array
   */
  protected static $defaultParams = [
    'contact_type' => 'Individual',
    'first_name'   => 'Test',
    'last_name'    => 'Test',
    'sequential'   => 1,
  ];

  /**
   * Fabricates a contact with the given parameters.
   *
   * @param array $params
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function fabricate(array $params = []) {
    $params = array_merge(static::$defaultParams, $params);
    $params['display_name'] = "{$params['first_name']} {$params['last_name']}";

    return parent::fabricate($params);
  }

  /**
   * Fabricates a contact with an e-mail address.
   *
   * @param array $params
   * @param string $email
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function fabricateWithEmail($params = [], $email = 'iamthe@batman.com') {
    $contact = self::fabricate($params);

    civicrm_api3('Email', 'create', [
      'email' => $email,
      'contact_id' => $contact['id'],
      'is_primary' => 1,
    ]);

    return $contact;
  }

}
