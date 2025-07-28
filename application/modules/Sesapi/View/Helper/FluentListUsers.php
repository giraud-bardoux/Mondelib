<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: FluentListUsers.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_View_Helper_FluentListUsers extends Zend_View_Helper_Abstract
{
  public function FluentListUsers($items, $translate = false,$isLike = false,$viewer,$isPageSubject = '')
  {
    $itemsS = array();
    foreach($items as $itemS){
      $itemsS[] = $itemS->getType() != "user" ?  Engine_Api::_()->getItem($itemS->poster_type,$itemS->poster_id) : $itemS;  
    }
    $items = $itemsS;
    if( 0 === ($num = engine_count($items)) )
    {
      return '';
    }
    $isLike = false;
    $comma = $this->view->translate(',');
    $and = $this->view->translate('AND');
    $countItems = engine_count($items);
    $content = "";
    if($isLike){
        $content .= $this->view->translate('YOU');
    }else{
      $content .= $items[0]->getTitle();  
    }
    if($countItems - 1 > 0){
       $content .= $countItems - 1 == 1 ? ' '.$and.' '.($countItems - 1).' '.$this->view->translate('other') : ' '.$and.' '.($countItems - 1).' '.$this->view->translate('others') ;
    }
    return $content;
  }
}
