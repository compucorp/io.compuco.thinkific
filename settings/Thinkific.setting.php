<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

use Civi\Thinkific\SettingsManager;

/*
 * Settings metadata file
 */
return [
  SettingsManager::API_ACCESS_TOKEN => [
    'name' => SettingsManager::API_ACCESS_TOKEN,
    'title' => 'Thinkific Access Token',
    'type' => 'String',
    'html_type' => 'password',
    'default' => '',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Access token from Thinkific',
    'html_attributes' => [],
  ],
];
