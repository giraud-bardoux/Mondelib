<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Url.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_Helper_Url extends Activity_Model_Helper_Abstract
{
  /**
   * Generates a url for action
   * 
   * @param mixed $params
   * @param string $innerHTML
   * @return string
   */
  public function direct($params, $innerHTML,$separator = ' &rarr; ')
  {
    // Passed an absolute url
    if( is_string($params) )
    {
      $uri = $params;
    }
    
    else if( is_array($params) && isset($params['uri']) )
    {
      $uri = $params['uri'];
    }

    // Passed a route array
    else if( is_array($params) )
    {
      $route = ( isset($params['route']) ? $params['route'] : 'default' );
      unset($params['route']);
      $uri = Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
    }

    // Whoops, just return the innerHTML
    else
    {
      return $innerHTML;
    }

    return '<a href="'.$uri.'">'.$innerHTML.'</a>';
  }
}
