<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: FluentListUsers.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_View_Helper_FluentListUsers extends Zend_View_Helper_Abstract
{
  public function FluentListUsers($items, $translate = false,$isLike = false,$viewer)
  {
    if( 0 === ($num = engine_count($items)) )
    {
      return '';
    }
    $isLike = false;
    $comma = $this->view->translate(',');
    $and = $this->view->translate('and');
    $countItems = engine_count($items);
    $index = 1;
    if($num > 3)
      $num = 4;
    if($isLike){
      if(($num - 1) != 0)
        $content = 'You';
      else
        $content = $viewer->getTitle();
      $index = 1;
      if($num > 3)
      $num = 3;
    }else{
      $content = '';
    }
    $break = false;
    foreach( $items as $item )
    {
      if($isLike && $viewer->getIdentity() == $item->getIdentity()){
        continue;
      }
       //if( $num > 1 && $index != $countItems) $content .= $comma . ' '; else $content .= ' ';
      //if( $countItems > 1 && $index == $countItems - 1  ) $content .= $and . ' ';
      
      if($index >= 3 && (($num >= 3 && $isLike) || $num >= 4)){
        $break = true;
        break;
      }
      
      $content .= $comma.' ';
      
      $href = null;
      $title = null;

      if( is_object($item) ) {
        if( method_exists($item, 'getTitle') && method_exists($item, 'getHref') ) {
          $href = $item->getHref();
          $title = $item->getTitle();
        } else if( method_exists($item, '__toString') ) {
          $title = $item->__toString();
        } else {
          $title = (string) $item;
        }
      } else {
        $title = (string) $item;
      }
      
      if( $translate ) {
        $title = $this->view->translate($title);
      }

      //if( null === $href ) {
        $content .= $title;
     // } else {
     //   $content .= $this->view->htmlLink($href, $title);
     // }
      
      $index++;
    }
    $content = trim($content,',');
    if($break){
      $text = ($countItems - 2) > 1 ? 'others' : 'other';
      $content .= ' and '.($countItems - 2).' '.$text;
    }else {
     $content =  strrev(implode(strrev(' and'), explode(strrev(','), strrev($content), 2)));  
    }
    return trim($content,',');
  }
}
