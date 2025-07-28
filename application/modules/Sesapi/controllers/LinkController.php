<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: LinkController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_LinkController extends Core_Controller_Action_Standard
{
  public function previewAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));
    // clean URL for html code
    $uri = trim(strip_tags($this->_getParam('uri')));
    if(strpos($uri,'https://') === false && strpos($uri,'http://') === false) {
      $uri= 'http://'.$uri;
    } 
    try
    {
//       $client = new Zend_Http_Client($uri, array(
//         'maxredirects' => 2,
//         'timeout'      => 10,
//       ));
//       // Try to mimic the requesting user's UA
//       $client->setHeaders(array(
//         'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
//         'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//         'X-Powered-By' => 'Zend Framework'
//       ));
//       $response = $client->request();
      $result =  Engine_Api::_()->getApi('attachment','sesapi')->previewHtml($uri);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
    }
    catch( Exception $e )
    {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));      
    }
  }

  
}
