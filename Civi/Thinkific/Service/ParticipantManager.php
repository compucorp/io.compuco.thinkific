<?php

namespace Civi\Thinkific\Service;

use Civi\Thinkific\Utils\ErrorHandler;
use Civi\Thinkific\Utils\Webform;

class ParticipantManager {

  public function __construct(private UserHandler $userHandler, private EnrollmentHandler $enrollmentHandler) {
  }

  public function addParticipant(\CRM_Event_DAO_Participant $participant, int $courseId): void {
    try {
      $participantId = (int) $participant->id;
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
      $participantId = (int) $participant->id;
      $thinkificUserId = $this->userHandler->getThinkificUserIdForParticipant($participant);
      $this->enrollmentHandler->unEnroll($thinkificUserId, $courseId, $participantId);
    }
    catch (\Throwable $e) {
      \Civi::log()->error('Participant un-enrollment error for participant ' . $participant->id . ' :: ' . $e->getMessage());
      $this->displayError();
    }
  }

  private function displayError(): void {
    if (Webform::isWebformSubmission()) {
      ErrorHandler::displayWebformError();
    }
    else {
      \Civi::$statics[\CRM_Thinkific_ExtensionUtil::LONG_NAME]['participantSynError'] = TRUE;
    }
  }

}
