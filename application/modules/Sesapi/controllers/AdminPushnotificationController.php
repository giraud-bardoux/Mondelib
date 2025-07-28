<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminPushnotificationController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_AdminPushnotificationController extends Core_Controller_Action_Admin {
 public function indexAction() {
   $data['title'] = "This is my title";
   $data['description'] = "Description";
   $userInfo['id'] = 1;
   $userInfo['data'] = "sesalbum";
   $userInfo['type'] = "test";
   $result = Engine_Api::_()->getApi('pushnoti','sesapi')->iOS($data,'95D6B9366DA73B65A4806F30DE15651EB9206B1ABF07C8B9EC76B92B1A5F7505',$userInfo);
   var_dump($result);die;
 }
}