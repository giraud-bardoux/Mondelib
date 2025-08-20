<?php
class Moduleflou_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->headScript()->appendFile($this->view->layout()->staticBaseUrl . 'application/modules/Moduleflou/externals/scripts/module-flou.js');
    $this->view->headLink()->appendStylesheet($this->view->layout()->staticBaseUrl . 'application/modules/Moduleflou/externals/styles/main.css');
    $this->view->title = 'Paramètres du Module Flou';
  }
}
