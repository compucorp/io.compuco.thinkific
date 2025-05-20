<?php

namespace Civi\Thinkific\Service;

use Civi\Thinkific\Utils\Webform;

class ParticipantManager {

  public function __construct(private UserHandler $userHandler, private EnrollmentHandler $enrollmentHandler) {
  }

  public function addParticipant(\CRM_Event_DAO_Participant $participant, int $courseId): void {
    try {
      $participantStatusId = (int) $participant->status_id;
      $participantId = (int) $participant->id;
      $participantStatus = \CRM_Event_PseudoConstant::participantStatus($participantStatusId);
      $thinkificUserId   = $this->userHandler->getThinkificUserIdForParticipant($participant);
      $this->enrollmentHandler->enroll($thinkificUserId, $courseId, $participantId);
    }
    catch (\Throwable $e) {
      \Civi::log()->error('Participant enrollment error for participant ' . $participant->id . ' :: ' . $e->getMessage());
      $this->displayError();
    }
  }

  public function removeParticipant(\CRM_Event_DAO_Participant $participant, int $courseId): void {
    try {
      $thinkificUserId = $this->userHandler->getThinkificUserIdForParticipant($participant);
      $participantId = (int) $participant->id;
      $this->enrollmentHandler->unEnroll($thinkificUserId, $courseId, $participantId);
    }
    catch (\Throwable $e) {
      \Civi::log()->error('Participant un-enrollment error for participant ' . $participant->id . ' :: ' . $e->getMessage());
      $this->displayError();
    }
  }

  private function displayError(): void {
    $msg = 'Your purchase or registration was successful but an error occurred with your course enrolment. Please contact the administrator to resolve this.';
    if (Webform::isWebformSubmission()) {
      /** @phpstan-ignore function.notFound */
      drupal_set_message($msg, 'error');
    }
    else {
      \CRM_Core_Session::setStatus($msg, 'LMS Integration:', 'error');
    }
  }

}
