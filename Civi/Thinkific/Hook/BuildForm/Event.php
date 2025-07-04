<?php

namespace Civi\Thinkific\Hook\BuildForm;

class Event {

  public function __construct() {
  }

  public function run(): void {
    \CRM_Core_Resources::singleton()->addScriptFile('io.compuco.thinkific', 'js/modifyEventForm.js');
  }

  public static function shouldRun(string $formName, \CRM_Core_Form $form): bool {
    return $formName === 'CRM_Event_Form_ManageEvent_EventInfo' &&
      ($form->_action == \CRM_Core_Action::ADD || $form->_action == \CRM_Core_Action::UPDATE);
  }

}
