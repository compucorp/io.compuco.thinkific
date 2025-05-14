<?php

class CRM_Thinkific_Hook_BuildForm_Event {

  public function __construct(private CRM_Core_Form $form) {
  }

  public function run(): void {
    CRM_Core_Resources::singleton()->addScriptFile('io.compuco.thinkific', 'js/modifyEventForm.js');
    \Civi::resources()->addVars('thinkific', ['action' => $this->form->_action]);
  }

  public static function shouldRun(string $formName, CRM_Core_Form $form): bool {
    return $formName === 'CRM_Event_Form_ManageEvent_EventInfo' &&
      ($form->_action == CRM_Core_Action::ADD || $form->_action == CRM_Core_Action::UPDATE);
  }

}
