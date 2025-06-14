<?php

namespace Civi\Thinkific\Utils;

class ErrorHandler {

  const LMS_SYNC_ERROR_MESSAGE = 'Your purchase or registration was successful but an error occurred with your course enrolment. Please contact the administrator to resolve this.';
  const EMAIL_EXIST_ERROR_MESSAGE = 'This email address is currently associated with a different account in the Learning Management System. Please select another or contact your administrator.';

  public static function displayError(string $message): void {
    if (Webform::isWebformSubmission()) {
      self::displayWebformError($message);
    }
    else {
      self::displayCiviError($message);
    }
  }

  public static function displayWebformError(string $message): void {
    /** @phpstan-ignore function.notFound */
    drupal_get_messages();
    /** @phpstan-ignore function.notFound */
    drupal_set_message($message, 'error');
  }

  public static function displayCiviError(string $message): void {
    \CRM_Core_Session::singleton()->getStatus(TRUE);
    \CRM_Core_Session::singleton()->setStatus($message, 'Learning Management System Integration:', 'error');
  }

}
