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

use CRM_Thinkific_SettingsManager as SettingsManager;

/*
 * Settings metadata file
 */
return [
  'thinkific_api_key' => [
    'name' => SettingsManager::API_KEY,
    'title' => 'Thinkific Api Key',
    'type' => 'String',
    'html_type' => 'password',
    'default' => '',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Api key from Thinkific',
    'html_attributes' => [],
  ],
  'thinkific_subdomain' => [
    'name' => SettingsManager::SUBDOMAIN,
    'title' => 'Thinkific Subdomain',
    'type' => 'String',
    'html_type' => 'text',
    'default' => '',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Subdomain for Thinkific',
    'html_attributes' => [],
  ],
];
