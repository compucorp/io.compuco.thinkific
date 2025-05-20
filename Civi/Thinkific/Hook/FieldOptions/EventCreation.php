<?php

namespace Civi\Thinkific\Hook\FieldOptions;

use Civi\Thinkific\EventCustomFieldsManager;

class EventCreation {

  /**
   * @var array
   * @phpstan-ignore missingType.iterableValue
   */
  private static array $thinkificFields = [];

  /**
   * CRM_Thinkific_Hook_FieldOptions_EventCreation constructor.
   *
   * @param string $field
   * @param array<string,string>|null $options
   * @phpstan-ignore property.onlyWritten
   */
  public function __construct(private string $field, private ?array &$options) {
  }

  public function run(): void {
    $thinkificFields = self::getThinkificFields();
    if (array_search($this->field, $thinkificFields) === EventCustomFieldsManager::ROLES_FIELD) {
      $this->fillRolesFieldOptions();
      return;
    }

    $this->fillStatusFieldOptions();
  }

  public static function shouldRun(string $entity, string $field): bool {
    if ($entity !== 'Event') {
      return FALSE;
    }

    $thinkificFields = self::getThinkificFields();

    return in_array($field, $thinkificFields, TRUE);
  }

  /**
   * @return string[]
   */
  private static function getThinkificFields(): array {
    if (!empty(self::$thinkificFields)) {
      return self::$thinkificFields;
    }

    $customFields = civicrm_api4('CustomField', 'get', [
      'checkPermissions' => FALSE,
      'select' => ['CONCAT("custom_", id) AS identifier', 'name'],
      'where' => [
        ['name', 'IN', [EventCustomFieldsManager::ROLES_FIELD, EventCustomFieldsManager::STATUS_FIELD]],
      ],
    ])->getArrayCopy();

    if (empty($customFields)) {
      return self::$thinkificFields;
    }
    /** @var array<string, string> $customField */
    foreach ($customFields as $customField) {
      self::$thinkificFields[$customField['name']] = $customField['identifier'];
    }

    return self::$thinkificFields;
  }

  private function fillRolesFieldOptions(): void {
    /** @var array<int, array<string, string>> $optionValues */
    $optionValues = civicrm_api4('OptionValue', 'get', [
      'select' => ['value', 'label'],
      'where' => [['option_group_id:name', '=', 'participant_role'], ['is_active', '=', 1]],
      'checkPermissions' => FALSE,
    ])->getArrayCopy();

    foreach ($optionValues as $optionValue) {
      $this->options[$optionValue['value']] = $optionValue['label'];
    }
  }

  private function fillStatusFieldOptions(): void {
    /** @var array<int, array<string, string>> $statuses */
    $statuses = civicrm_api4('ParticipantStatusType', 'get', [
      'select' => ['id', 'label'],
      'where' => [['is_active', '=', 1]],
      'checkPermissions' => FALSE,
    ])->getArrayCopy();

    foreach ($statuses as $status) {
      $this->options[$status['id']] = $status['label'];
    }
  }

}
