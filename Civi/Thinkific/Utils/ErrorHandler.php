<?php

namespace Civi\Thinkific\Utils;

class ErrorHandler {

  const ERROR_MESSAGE = 'Your purchase or registration was successful but an error occurred with your course enrolment. Please contact the administrator to resolve this.';

  public static function displayWebformError(): void {
    /** @phpstan-ignore function.notFound */
    drupal_get_messages();
    /** @phpstan-ignore function.notFound */
    drupal_set_message(self::ERROR_MESSAGE, 'error');
  }

  public static function displayCiviError(): void {
    \CRM_Core_Session::singleton()->getStatus(TRUE);
    \CRM_Core_Session::singleton()->setStatus(self::ERROR_MESSAGE, 'LMS Integration:', 'error');
  }

}
