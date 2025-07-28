<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: RequireAdmin.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Controller_Action_Helper_RequireAdmin extends
  Core_Controller_Action_Helper_RequireAbstract
{
  protected $_errorAction = array('requireadmin', 'error', 'core');

  public function checkRequire()
  {
    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $ret = $viewer->isAllowed('admin');
    }
    catch( Exception $e )
    {
      $ret = false;
    }


    if( !$ret && APPLICATION_ENV == 'development' && Zend_Registry::isRegistered('Zend_Log') && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log )
    {
      $target = $this->getRequest()->getModuleName() . '.' .
              $this->getRequest()->getControllerName() . '.' .
              $this->getRequest()->getActionName();
      $log->log('Require class '.get_class($this).' failed check for: '.$target, Zend_Log::DEBUG);
    }

    return $ret;
  }
}
