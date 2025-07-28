<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Actors.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Model_Helper_Actors extends Sesapi_Model_Helper_Abstract
{
  public function direct($subject, $object = false)
  {
    $pageSubject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;
    
    $subject = $this->_getItem($subject, false);
    $object = $this->_getItem($object, false);
    
    // Check to make sure we have an item
    if( !($subject instanceof Core_Model_Item_Abstract) || !($object instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }
    if($subject->getGuid() == $object->getGuid()){
      return "";
    }else if( null === $pageSubject ) {
      return (array('title'=>"".$object->getTitle(),'id'=>$object->getIdentity(),'type'=>$object->getType(),'module'=>strtolower($object->getModuleName()),'href'=>Engine_Api::_()->sesapi()->getBaseUrl(false).$object->getHref(),'seprator'=>"  \\u2192   "));
    } else if( $pageSubject->isSelf($subject) ) {
      return (array('title'=>"".$object->getTitle(),'id'=>$object->getIdentity(),'type'=>$object->getType(),'module'=>strtolower($object->getModuleName()),'href'=>Engine_Api::_()->sesapi()->getBaseUrl(false).$object->getHref(),'seprator'=>"  \\u2192   "));
    } else if( $pageSubject->isSelf($object) ) {
      return "";
    } else {
      return (array('title'=>"".$object->getTitle(),'id'=>$object->getIdentity(),'type'=>$object->getType(),'module'=>strtolower($object->getModuleName()),'href'=>Engine_Api::_()->sesapi()->getBaseUrl(false).$object->getHref(),'seprator'=>"  \\u2192   "));
    }
  }
}
