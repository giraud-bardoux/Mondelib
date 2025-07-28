<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Menus.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Plugin_Menus
{
	public function enableIosModule(){
		// Must be logged in
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesiosapp')){
      return true; 
    }
    return false;
		
	}
	public function enableAndroidModule(){
		// Must be logged in
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesandroidapp')){
      return true; 
    }
    return false;
		
	}
  
}