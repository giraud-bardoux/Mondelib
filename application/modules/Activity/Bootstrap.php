<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Bootstrap.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Bootstrap extends Engine_Application_Bootstrap_Abstract
{

  public function __construct($application)
  {

    parent::__construct($application);

    $this->initViewHelperPath();

    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Activity_Plugin_Core);

    //Emotions Load
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $script = "var chatEmotions = " . Engine_Api::_()->activity()->getEmoticons('', '', true) . ";";
    $view->headScript()->appendScript($script);
  }
}
