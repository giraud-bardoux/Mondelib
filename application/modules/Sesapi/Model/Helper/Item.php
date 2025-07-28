<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Item.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Model_Helper_Item extends Sesapi_Model_Helper_Abstract
{
  /**
   * Generates text representing an item
   * 
   * @param mixed $item The item or item guid
   * @param string $text (OPTIONAL)
   * @param string $href (OPTIONAL)
   * @return string
   */
  public function direct($item, $text = null, $href = null, $hideVerifiedIcon = true)
  {
    $item = $this->_getItem($item, false);

    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }

    if( !isset($text) )
    {
      $text = $item->getTitle(false);
      
      if($item->getType() == 'user' && $item->is_verified) {
        $text .= ' <img src="'.$item->verifiedIcon().'" />';
      }
    }

    // translate text
    $translate = Zend_Registry::get('Zend_Translate');
    if( !($item instanceof User_Model_User) && $translate instanceof Zend_Translate ) {
      $text = $translate->translate($text);
      // if the value is pluralized, only use the singular
      if (is_array($text))
        $text = $text[0];
    }

    if( !isset($href) )
    {
      $href = $item->getHref();
    }
    return (array('title'=>$text,'id'=>$item->getIdentity(),'type'=>$item->getType(),'module'=>strtolower($item->getModuleName()),'href'=>Engine_Api::_()->sesapi()->getBaseUrl(false).$href));    
  }
}
