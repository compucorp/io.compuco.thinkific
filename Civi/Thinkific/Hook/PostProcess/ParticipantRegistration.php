<?php

namespace Civi\Thinkific\Hook\PostProcess;

use Civi\Thinkific\Utils\ErrorHandler;

class ParticipantRegistration {

  public function run(): void {
    ErrorHandler::displayCiviError();
  }

  public static function shouldRun(string $formName, \CRM_Core_Form $form): bool {
    return in_array($formName, ['CRM_Event_Form_Registration_Confirm', 'CRM_Event_Form_Participant'], TRUE) &&
      ($form->_action == \CRM_Core_Action::ADD || $form->_action == \CRM_Core_Action::UPDATE) &&
      !empty(\Civi::$statics[\CRM_Thinkific_ExtensionUtil::LONG_NAME]['participantSynError']);
  }

}
