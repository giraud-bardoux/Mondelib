<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: IndexController.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    $this->view->someVar = 'someVal';
  }
  function fontAction(){
      if(!engine_count($_POST)){
          echo false;die;
      }
      $font = $this->_getParam('size','');
      $_SESSION['font_theme'] = $font;
      echo true;die;
  }
    function modeAction(){
        if(!engine_count($_POST)){
            echo false;die;
        }
        $font = $this->_getParam('mode','');
        $_SESSION['mode_theme'] = $font;
        echo true;die;
    }
}
