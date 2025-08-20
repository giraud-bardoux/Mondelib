<?php
class Moduleflou_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    $this->view->message = 'Module Flou (v1.22) — démo helper de vue.';
  }
}
