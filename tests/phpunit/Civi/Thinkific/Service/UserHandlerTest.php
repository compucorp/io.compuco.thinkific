<?php

use Civi\Thinkific\Service\UserHandler;
use Civi\Thinkific\Service\ApiClient;
use Civi\Thinkific\Test\Fabricator\ContactFabricator;
use GuzzleHttp\Psr7\Response;

/**
 * @group headless
 */
class UserHandlerTest extends BaseHeadlessTest {

  public function testNewUserIsCreatedOnlyIfItDoesNotExist(): void {
    $participant = new CRM_Event_DAO_Participant();
    $firstName = 'test';
    $lastName = 'test';
    $email = 'test@test.com';
    $contact = ContactFabricator::fabricateWithEmail(['first_name' => $firstName, 'last_name' => $lastName], $email);
    $participant->contact_id = $contact['id'];

    $mockApiClient = $this->getMockBuilder(ApiClient::class)->disableOriginalConstructor()->getMock();
    $mockApiClient->expects($this->once())->method('request')->with(
        'POST',
        'users',
        [],
        ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'company' => '', 'external_id' => $contact['id']],
      )
      ->willReturn(new Response());

    $mockUserHandler = $this
      ->getMockBuilder(UserHandler::class)
      ->onlyMethods(['getContactsThinkificId', 'getExistingThinkificUserIdByEmail', 'getContactData', 'updateContact', 'updateThinkificUser'])
      ->setConstructorArgs([$mockApiClient])
      ->getMock();
    $mockUserHandler->expects($this->once())
      ->method('getContactsThinkificId')
      ->willReturn(0);
    $mockUserHandler->expects($this->once())
      ->method('getExistingThinkificUserIdByEmail')
      ->willReturn(0);
    $mockUserHandler->expects($this->once())
      ->method('getContactData')
      ->willReturn(['first_name' => $firstName, 'last_name' => $lastName, 'employer_id.display_name' => NULL]);
    $mockUserHandler->expects($this->once())
      ->method('updateContact');
    $mockUserHandler->expects($this->never())
      ->method('updateThinkificUser');

    $mockUserHandler->getThinkificUserIdForParticipant($participant);
  }

  public function testNewUserIsOnlyUpdatedIfItExists(): void {
    $participant = new CRM_Event_DAO_Participant();
    $firstName = 'test';
    $lastName = 'test';
    $email = 'test@test.com';
    $thinkificUserId = 10;
    $contact = ContactFabricator::fabricateWithEmail(
      [
        'first_name' => $firstName,
        'last_name' => $lastName,
      ],
      $email
    );
    $participant->contact_id = $contact['id'];

    $mockApiClient = $this->getMockBuilder(ApiClient::class)->disableOriginalConstructor()->getMock();
    $mockApiClient->expects($this->once())
      ->method('request')
      ->with(
        'PUT',
        'users/' . $thinkificUserId,
        [],
        ['first_name' => $firstName, 'last_name' => $lastName, 'company' => '', 'external_id' => $contact['id']],
      )
      ->willReturn(new Response());

    $mockUserHandler = $this
      ->getMockBuilder(UserHandler::class)
      ->onlyMethods(['getContactsThinkificId', 'createNewThinkificUser'])
      ->setConstructorArgs([$mockApiClient])
      ->getMock();
    $mockUserHandler->expects($this->once())
      ->method('getContactsThinkificId')
      ->willReturn($thinkificUserId);
    $mockUserHandler->expects($this->never())
      ->method('createNewThinkificUser');

    $mockUserHandler->getThinkificUserIdForParticipant($participant);
  }

}
