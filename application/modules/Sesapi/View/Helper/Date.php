<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Date.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_View_Helper_Date extends Zend_View_Helper_Abstract
{
  public function date($date_string)
  {
    $date_string = trim($date_string);
    if (empty($date_string) || $date_string == '0-0-0')
      return FALSE;

    // $date_string is formatted as y-m-d
    $return_text = "";
    $date_array  = explode('-', $date_string);

    // @todo replace this hard-coded date string with locale-specific version
    $date_format  = "M j".($date_array[0] != 0?', Y':'');
    $time_padding = (strlen($date_string) > 10 ? '' : '00:00:00');
    return date($date_format, strtotime("$date_string $time_padding"));
  }
}