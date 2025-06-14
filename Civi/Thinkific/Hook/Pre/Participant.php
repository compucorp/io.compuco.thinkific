<?php

namespace Civi\Thinkific\Hook\Pre;

use Civi\Thinkific\EventCustomFieldsManager;
use Civi\Thinkific\Utils\ErrorHandler;

class Participant {

  /**
   * Participant constructor.
   *
   * @param array<string,string|int> $participant
   */
  public function __construct(private array $participant) {
  }

  public function run(): void {
    /** @var array<string, string|int|int[]> $eventData */
    $eventData = civicrm_api4('Event', 'get', [
      'checkPermissions' => FALSE,
      'select' => [EventCustomFieldsManager::CUSTOM_GROUP . '.*'],
      'where' => [['id', '=', $this->participant['event_id'] ?? 0]],
    ])->getArrayCopy()[0] ?? [];

    if (empty($eventData) ||
      empty($eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::SYNC_FIELD][0]) ||
      empty($eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::ID_FIELD])
    ) {
      return;
    }

    $this->validateParticipantEmail();
  }

  /**
   * @param string $op
   * @param string $objectName
   * @param object|array<string,mixed> $params
   *
   * @return bool
   */
  public static function shouldRun(string $op, string $objectName, $params): bool {
    if (!in_array($op, ['create', 'edit']) || $objectName !== 'Participant' || !is_array($params)) {
      return FALSE;
    }

    return TRUE;
  }

  private function validateParticipantEmail(): void {
    /** @var \Civi\Thinkific\Service\UserHandler $userHandler */
    $userHandler = \Civi::service('service.thinkific.user_handler');
    $contactId = (int) ($this->participant['contact_id'] ?? 0);
    $thinkificUser = $userHandler->getExistingThinkificUserByEmail(
      (string) \CRM_Contact_BAO_Contact::getPrimaryEmail($contactId)
    );
    try {
      $userHandler->validateExternalSource($thinkificUser->external_source ?? '', $contactId);
    }
    catch (\InvalidArgumentException $e) {
      ErrorHandler::displayError(ErrorHandler::EMAIL_EXIST_ERROR_MESSAGE);
      \CRM_Utils_System::redirect(\CRM_Utils_System::refererPath());
    }
  }

}
