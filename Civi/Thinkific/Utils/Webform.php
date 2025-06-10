<?php

namespace Civi\Thinkific\Utils;

class Webform {

  public static function isWebformSubmission(): bool {
    /** @var string $formID */
    $formID = \CRM_Utils_Request::retrieve('form_id', 'String', NULL, FALSE, '');

    return str_contains($formID, 'webform_client_form_');
  }

}
