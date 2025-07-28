<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Bootstrap.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Bootstrap extends Engine_Application_Bootstrap_Abstract
{

  protected function _initFrontController() {
    include APPLICATION_PATH . '/application/modules/Sesiosapp/controllers/Checklicense.php';
  }
  
  public function __construct($application) {
    parent::__construct($application);
    $sesiosapp_load = Zend_Registry::isRegistered('sesiosapp_load') ? Zend_Registry::get('sesiosapp_load') : null;
    if (empty($sesiosapp_load)) {
      return null;
    } 
  }
}