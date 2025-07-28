<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Others.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Model_Helper_Others extends Sesapi_Model_Helper_Abstract
{

  /**
   * Generates text representing an similar items
   * 
   * @param array $items The items
   * @return string
   */
  public function direct($items = array())
  {
    if( empty($items) ) {
      return false;
    }
    $count = engine_count($items);
    if( $count === 1 ) {
      $attribs = array('class' => 'feed_item_username');
      return array_pop($items)->toString($attribs);
    }

    if( Zend_Registry::isRegistered('Zend_View') ) {
      $view = Zend_Registry::get('Zend_View');
      $count = $view->locale()->toNumber($count);
    }

    $translate = Zend_Registry::get('Zend_Translate');
    $othersKey = '%s others';
    if( $translate instanceof Zend_Translate ) {
      $othersKey = $translate->translate($othersKey);
    }
    $text = vsprintf($othersKey, $count);
    $link = '<a '
      . 'class="feed_item_username" '
      . 'href="javascript:void()"'
      . '>'
      . $text
      . '</a>';

    return '<span class="tip_container">'
      . $link
      . $this->getListHtml($items)
      . '</span>';
  }

  protected function getListHtml($items)
  {
    $itemList = '<span class="tip_wapper">'
      . '<ul class="tip_body">';
    foreach( $items as $item ) {
      $itemList .= '<li>'
        . $item->toString()
        . '</li>';
    }
    return $itemList . '</ul></span>';
  }
}
