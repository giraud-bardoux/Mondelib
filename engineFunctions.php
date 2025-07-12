<?php

/**
 * @version     $Id: engineFunctions.php 2018-06-20 00:04:31Z $
 * @copyright   Copyright (c) 2006-2020 Webligo Developments
 * @license     http://www.socialengine.com/license/
 */


function engine_in_array($string = '', $array = array()) {
  if(is_array($array) && in_array($string, $array))
    return true;
  else 
    return false;
}

function engine_count($array = array()) {
  if(is_countable($array) && count($array) > 0)
    return count($array);
  else 
    return 0;
}
