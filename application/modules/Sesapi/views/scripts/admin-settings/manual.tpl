<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: manual.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
Search below line:<br>
define('_ENGINE_R_TARG', 'index.php');<br>

and replace with:<br>
if(isset($_GET['restApi']) && !empty($_GET['restApi']) && $_GET['restApi'] == 'Sesapi'){ <br>
  //define ses api file in application folder <br>
  define('_ENGINE_R_TARG', 'sesapi.php'); <br>
  define('_SESAPI_R_TARG', 'sesapi.php');<br>
  if(!empty($_GET['sesapi_platform']))<br>
      define('_SESAPI_PLATFORM_SERVICE', $_GET['sesapi_platform']);<br>
  else<br>
      define('_SESAPI_PLATFORM_SERVICE',0);<br>
}else <br>
    define('_ENGINE_R_TARG', 'index.php');<br>
if(!empty($_GET['sesapiPaymentModel']))<br>
  $_SESSION['sesapiPaymentModel']  = true;<br>
