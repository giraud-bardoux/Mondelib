<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Fields.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Form_Signup_Fields extends Sesapi_Form_Standard
{
  protected $_fieldType = 'user';

  public function init()
  {
    // Init form
    $this->setTitle('Profile Information');

    $this
      ->setIsCreation(true)
      ->setItem(Engine_Api::_()->user()->getUser(null));
    parent::init();
  }
}