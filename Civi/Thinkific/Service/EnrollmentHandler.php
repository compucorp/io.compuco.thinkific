<?php

namespace Civi\Thinkific\Service;

use Civi\Thinkific\ParticipantCustomFieldsManager;
use Psr\Http\Message\ResponseInterface;

class EnrollmentHandler {

  public function __construct(private ApiClient $apiClient) {
  }

  public function enroll(int $userId, int $courseId, int $participantId): void {
    $currentEnrollment = $this->getCurrentEnrollment($userId, $courseId);
    if (empty($currentEnrollment->id)) {
      $url = 'enrollments';
      $enrollmentData = ['course_id' => $courseId, 'user_id' => $userId, 'activated_at' => date('Y-m-d\TH:i:s\Z')];

      try {
        $enrollmentResponse = $this->apiClient->request('POST', $url, [], $enrollmentData);
        $enrollmentResponseContent = $enrollmentResponse->getBody()->getContents();
        if (!empty($enrollmentResponseContent)) {
          /** @var \stdClass $newEnrollment */
          $newEnrollment = json_decode($enrollmentResponseContent);
          $this->updateParticipant($enrollmentResponse, $participantId, $newEnrollment);
        }
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->updateParticipant($e->getResponse(), $participantId, new \stdClass());
        throw $e;
      }
    }
  }

  public function unEnroll(int $userId, int $courseId, int $participantId): void {
    $currentEnrollment = $this->getCurrentEnrollment($userId, $courseId);
    if (empty($currentEnrollment->id)) {
      return;
    }

    $url = 'enrollments/' . $currentEnrollment->id;
    $enrollmentData = ['expiry_date' => date('Y-m-d\TH:i:s\Z')];
    try {
      $unEnrollResponse = $this->apiClient->request('PUT', $url, [], $enrollmentData);
      $this->updateParticipant($unEnrollResponse, $participantId, $currentEnrollment);
    }
    catch (\GuzzleHttp\Exception\ClientException $e) {
      $this->updateParticipant($e->getResponse(), $participantId, $currentEnrollment);
      throw $e;
    }
  }

  public function getCurrentEnrollment(int $userId, int $courseId): \stdClass {
    $url = '/enrollments?query[user_id]=' . $userId . '&query[course_id]=' . $courseId . '&query[expired]=0';
    $currentEnrollment = new \stdClass();

    try {
      $enrollmentResponse = $this->apiClient->request('GET', $url);
      $enrollment         = json_decode($enrollmentResponse->getBody()->getContents());
      if ($enrollment instanceof \stdClass && !empty($enrollment->items)) {
        $currentEnrollment = $enrollment->items[0];
      }
    }
    catch (\Throwable $e) {
    }

    return $currentEnrollment;
  }

  public function updateParticipant(ResponseInterface $response, int $participantId, \stdClass $enrollment): void {
    $statusCode = $response->getStatusCode();
    civicrm_api4('Participant', 'update', [
      'values' => [
        ParticipantCustomFieldsManager::CUSTOM_GROUP . '.' . ParticipantCustomFieldsManager::ENROLLMENT_FIELD  => $enrollment->id ?? NULL,
        ParticipantCustomFieldsManager::CUSTOM_GROUP . '.' . ParticipantCustomFieldsManager::RESPONSE_FIELD    => !empty($enrollment->id) ? serialize($enrollment) : $statusCode,
        ParticipantCustomFieldsManager::CUSTOM_GROUP . '.' . ParticipantCustomFieldsManager::SYNC_STATUS_FIELD => $statusCode >= 200 && $statusCode <= 299 ? '1' : '0',
        ParticipantCustomFieldsManager::CUSTOM_GROUP . '.' . ParticipantCustomFieldsManager::SYNC_DATE_FIELD   => date('Y-m-d H:i:s'),
      ],
      'where' => [
        ['id', '=', $participantId],
      ],
      'checkPermissions' => FALSE,
    ]);
  }

}
