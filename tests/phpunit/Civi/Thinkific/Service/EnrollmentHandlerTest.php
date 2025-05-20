<?php

use Civi\Thinkific\Service\EnrollmentHandler;
use Civi\Thinkific\Service\ApiClient;
use GuzzleHttp\Psr7\Response;

/**
 * @group headless
 */
class EnrollmentHandlerTest extends BaseHeadlessTest {

  public function testEnrollmentIsCreatedOnlyIfItDoesNotExist(): void {
    $courseId = 123;
    $userId = 456;
    $participantId = 789;

    $mockApiClient = $this->getMockBuilder(ApiClient::class)->disableOriginalConstructor()->getMock();
    $mockApiClient->expects($this->once())
      ->method('request')
      ->with(
        'POST',
        'enrollments',
        [],
        ['course_id' => $courseId, 'user_id' => $userId, 'activated_at' => date('Y-m-d\TH:i:s\Z')],
      )
      ->willReturn(new Response());

    $mockEnrollmentHandler = $this->getMockBuilder(EnrollmentHandler::class)
      ->onlyMethods(['getCurrentEnrollment', 'updateParticipant'])
      ->setConstructorArgs([$mockApiClient])
      ->getMock();
    $mockEnrollmentHandler->expects($this->once())->method('getCurrentEnrollment')->willReturn(new stdClass());

    $mockEnrollmentHandler->enroll($userId, $courseId, $participantId, FALSE);
  }

  public function testEnrollmentIsNotCreatedIfItExists(): void {
    $courseId = 123;
    $userId = 456;
    $participantId = 789;
    $currentEnrollment = new stdClass();
    $currentEnrollment->id = 10;

    $mockApiClient = $this->getMockBuilder(ApiClient::class)->disableOriginalConstructor()->getMock();
    $mockApiClient->expects($this->never())->method('request');

    $mockEnrollmentHandler = $this
      ->getMockBuilder(EnrollmentHandler::class)
      ->onlyMethods(['getCurrentEnrollment', 'updateParticipant'])
      ->setConstructorArgs([$mockApiClient])
      ->getMock();
    $mockEnrollmentHandler->expects($this->once())->method('getCurrentEnrollment')->willReturn($currentEnrollment);

    $mockEnrollmentHandler->enroll($userId, $courseId, $participantId, FALSE);
  }

  public function testUnEnrollmentHappensOnlyIfItExists(): void {
    $courseId = 123;
    $userId = 456;
    $participantId = 789;

    $mockApiClient = $this
      ->getMockBuilder(ApiClient::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockApiClient->expects($this->never())->method('request');

    $mockEnrollmentHandler = $this
      ->getMockBuilder(EnrollmentHandler::class)
      ->onlyMethods(['getCurrentEnrollment'])
      ->setConstructorArgs([$mockApiClient])
      ->getMock();
    $mockEnrollmentHandler->expects($this->once())->method('getCurrentEnrollment')->willReturn(new stdClass());

    $mockEnrollmentHandler->unEnroll($userId, $courseId, $participantId);
  }

}
