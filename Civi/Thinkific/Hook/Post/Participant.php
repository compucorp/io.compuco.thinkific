<?php

namespace Civi\Thinkific\Hook\Post;

use Civi\Thinkific\EventCustomFieldsManager;

class Participant {

  /**
   * @var array
   * @phpstan-ignore missingType.iterableValue
   */
  private static array $processedParticipants = [];

  public function __construct(private int $participantId, private \CRM_Event_DAO_Participant $participant, private string $op) {
  }

  public function run(): void {
    self::$processedParticipants[$this->op][$this->participantId] = 1;
    /** @var array<string, string|int|int[]> $eventData */
    $eventData = civicrm_api4('Event', 'get', [
      'checkPermissions' => FALSE,
      'select' => [EventCustomFieldsManager::CUSTOM_GROUP . '.*'],
      'where' => [['id', '=', $this->participant->event_id]],
    ])->getArrayCopy()[0] ?? [];

    if (empty($eventData)) {
      return;
    }
    if (empty($eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::SYNC_FIELD][0])) {
      return;
    }
    if (empty($eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::ID_FIELD])) {
      return;
    }

    /** @var \Civi\Thinkific\Service\ParticipantManager $participantManager */
    $participantManager = \Civi::service('service.thinkific.participant_manager');
    /** @var string[] $eventStatuses */
    $eventStatuses = $eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::STATUS_FIELD];
    $courseId = (int) $eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::ID_FIELD];
    if (!empty($eventStatuses) && !in_array($this->participant->status_id, $eventStatuses)) {
      $participantManager->removeParticipant($this->participant, $courseId);
      return;
    }
    /** @var string $participantRoles */
    $participantRoles = $this->participant->role_id;
    /** @var string[] $eventRoles */
    $eventRoles = $eventData[EventCustomFieldsManager::CUSTOM_GROUP . '.' . EventCustomFieldsManager::ROLES_FIELD];
    if (!empty($eventRoles) && empty(array_intersect(explode(\CRM_Core_DAO::VALUE_SEPARATOR, $participantRoles), $eventRoles))) {
      $participantManager->removeParticipant($this->participant, $courseId);
      return;
    }

    $participantManager->addParticipant($this->participant, $courseId);
  }

  /**
   * @param string $op
   * @param string $objectName
   * @param int $objectId
   * @param object|array<string,mixed> $objectRef
   *
   * @return bool
   */
  public static function shouldRun(string $op, string $objectName, int $objectId, $objectRef): bool {
    if (!in_array($op, ['create', 'edit']) || $objectName !== 'Participant' || !$objectRef instanceof \CRM_Event_DAO_Participant || isset(self::$processedParticipants[$op][$objectId])) {
      return FALSE;
    }

    return TRUE;
  }

}
