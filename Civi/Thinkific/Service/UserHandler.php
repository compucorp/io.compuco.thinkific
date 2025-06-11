<?php

namespace Civi\Thinkific\Service;

use Civi\Thinkific\ContactCustomFieldsManager;
use Psr\Http\Message\ResponseInterface;

class UserHandler {

  const EXTERNAL_SOURCE_PREFIX = 'civi_';

  public function __construct(private ApiClient $apiClient) {
  }

  public function getThinkificUserIdForParticipant(\CRM_Event_DAO_Participant $participant): int {
    $existingThinkificUserId = $this->getContactsThinkificId((int) $participant->contact_id);
    $contactId = (int) $participant->contact_id;

    if ($existingThinkificUserId === 0) {
      $existingThinkificUser = $this->getExistingThinkificUserByEmail((string) \CRM_Contact_BAO_Contact::getPrimaryEmail($contactId));
      if (!empty($existingThinkificUser->id)) {
        $this->validateExternalSource($existingThinkificUser->external_source ?? '', $contactId);
        $existingThinkificUserId = $existingThinkificUser->id;
      }
    }
    if ($existingThinkificUserId > 0) {
      $this->updateThinkificUser($contactId, $existingThinkificUserId);

      return $existingThinkificUserId;
    }

    return $this->createNewThinkificUser($contactId);
  }

  public function getContactsThinkificId(int $contactId): int {
    /** @var array<string, string|int> $contactThinkificData */
    $contactThinkificData = civicrm_api4('Contact', 'get', [
      'checkPermissions' => FALSE,
      'select' => [ContactCustomFieldsManager::CUSTOM_GROUP . '.*'],
      'where' => [['id', '=', $contactId]],
    ])->getArrayCopy()[0] ?? [];

    if (empty($contactThinkificData)
      || empty($contactThinkificData[ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::USER_FIELD])) {
      return 0;
    }
    try {
      $url = 'users/' . $contactThinkificData[ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::USER_FIELD];
      $existingUserResponse = $this->apiClient->request('GET', $url);
      /** @var \stdClass $existingUserObject */
      $existingUserObject = json_decode($existingUserResponse->getBody()->getContents());
      if (!empty($existingUserObject->id)) {
        return (int) $contactThinkificData[ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::USER_FIELD];
      }
    }
    catch (\Throwable $e) {
    }

    return 0;
  }

  public function getExistingThinkificUserByEmail(string $email): \stdClass {
    $existingUser = new \stdClass();
    if ($email === '') {
      return $existingUser;
    }

    $url = 'users/?query[email]=' . $email;
    try {
      $existingUserResponse = $this->apiClient->request('GET', $url);
      /** @var \stdClass $existingUserObject */
      $existingUserObject = json_decode($existingUserResponse->getBody()->getContents());
      if (!empty($existingUserObject->items) && !empty($existingUserObject->items[0]) && $existingUserObject->items[0] instanceof \stdClass) {
        $existingUser = $existingUserObject->items[0];
      }
    }
    catch (\Throwable $e) {
    }

    return $existingUser;
  }

  public function updateThinkificUser(int $contactId, int $thinkificUserId): void {
    $contactData = $this->getContactData($contactId);
    if (empty($contactData)) {
      throw new \InvalidArgumentException('Contact does not have all required information!');
    }

    $url = 'users/' . $thinkificUserId;
    $userData = [
      'first_name' => $contactData['first_name'],
      'last_name' => $contactData['last_name'],
      'company' => (string) $contactData['employer_id.display_name'],
      'external_source' => self::EXTERNAL_SOURCE_PREFIX . $contactId,
    ];
    try {
      $updateUserResponse = $this->apiClient->request('PUT', $url, [], $userData);
      $this->updateContact($updateUserResponse, $contactId, $thinkificUserId);
    }
    catch (\GuzzleHttp\Exception\ClientException $e) {
      $this->updateContact($e->getResponse(), $contactId, $thinkificUserId);
      throw $e;
    }
  }

  public function createNewThinkificUser(int $contactId): int {
    $email = (string) \CRM_Contact_BAO_Contact::getPrimaryEmail($contactId);
    $contactData = $this->getContactData($contactId);
    if ($email === '' || empty($contactData)) {
      throw new \InvalidArgumentException('Contact does not have all required information!');
    }

    $url = 'users';
    $userData = [
      'first_name' => $contactData['first_name'],
      'last_name' => $contactData['last_name'],
      'email' => $email,
      'company' => (string) $contactData['employer_id.display_name'],
      'external_source' => self::EXTERNAL_SOURCE_PREFIX . $contactId,
    ];
    try {
      $createUserResponse = $this->apiClient->request('POST', $url, [], $userData);
      /** @var \stdClass $newUser */
      $newUser = json_decode($createUserResponse->getBody()->getContents());
      $thinkificUserId = $newUser->id ?? 0;
      $this->updateContact($createUserResponse, $contactId, $thinkificUserId, $newUser);

      return $thinkificUserId;
    }
    catch (\GuzzleHttp\Exception\ClientException $e) {
      $this->updateContact($e->getResponse(), $contactId, NULL);
      throw $e;
    }
  }

  public function updateContact(ResponseInterface $response, int $contactId, ?int $thinkificUserId, ?\stdClass $user = NULL): void {
    $statusCode = $response->getStatusCode();
    $response = !empty($user) ? $user : $response->getBody()->getContents();
    civicrm_api4('Contact', 'update', [
      'values'           => [
        ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::USER_FIELD        => $thinkificUserId,
        ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::RESPONSE_FIELD    => !empty($response) ? serialize($response) : $statusCode,
        ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::SYNC_STATUS_FIELD => $statusCode >= 200 && $statusCode <= 299 ? '1' : '0',
        ContactCustomFieldsManager::CUSTOM_GROUP . '.' . ContactCustomFieldsManager::SYNC_DATE_FIELD   => date('Y-m-d H:i:s'),
      ],
      'where'            => [
        ['id', '=', $contactId],
      ],
      'checkPermissions' => FALSE,
    ]);
  }

  /**
   * @param int $contactId
   *
   * @return array<string, string|int>
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function getContactData(int $contactId): array {
    /** @var array<string, string|int> $contactData */
    $contactData = civicrm_api4('Contact', 'get', [
      'checkPermissions' => FALSE,
      'select' => ['*', 'employer_id.display_name'],
      'where' => [['id', '=', $contactId]],
    ])->getArrayCopy()[0] ?? [];

    return $contactData;
  }

  public function validateExternalSource(string $source, int $contactId): void {
    preg_match('/civi_(\d+)/', $source, $matches);

    if (!empty($matches[1]) && (int) $matches[1] !== $contactId) {
      throw new \InvalidArgumentException('Email already exist in thinkific and its linked to another contact');
    }
  }

}
