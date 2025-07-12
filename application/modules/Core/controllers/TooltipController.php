<?php

class Core_TooltipController extends Core_Controller_Action_Standard
{

  public function indexAction()
  {
    $guid = $this->_getParam('guid', false);
    if (!$guid)
      return;
    $this->view->subject = $subject = Engine_Api::_()->getItemByGuid($guid);

    if (!$subject)
      return;
  }
}
