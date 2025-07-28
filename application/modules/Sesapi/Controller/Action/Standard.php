<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Standard.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

abstract class Sesapi_Controller_Action_Standard extends Engine_Controller_Action
{
  public $autoContext = true;
  
  public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
  {
        // Pre-init setSubject
        try {
          if( '' !== ($subject = trim((string) $request->getParam('subject'))) ) {
            $subject = Engine_Api::_()->getItemByGuid($subject);
            if( ($subject instanceof Core_Model_Item_Abstract) && $subject->getIdentity() && !Engine_Api::_()->core()->hasSubject() ) {
              Engine_Api::_()->core()->setSubject($subject);
            }
          }
        } catch( Exception $e ) {
          // Silence
          //throw $e;
        }

        // Parent
        parent::__construct($request, $response, $invokeArgs);
  }
  public function userImage($userid = '',$type="thumb.profile"){
    if(!$userid)
      return '';
    $user = Engine_Api::_()->getItem('user', $userid);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;;
    $photo = $view->itemPhoto($user, $type);
    $doc = new DOMDocument();
    @$doc->loadHTML($photo);
    $tags = $doc->getElementsByTagName('img');
    $image = '';
    foreach($tags as $tag){
      $image = $tag->getAttribute('src');
      if(strpos($image,'http') === false){
        $image = $this->getBaseUrl(true,$image);
      }
    }
    return $image;
  }
  public function getBaseUrl($staticBaseUrl = true,$url = ""){
    return Engine_Api::_()->sesapi()->getBaseUrl($staticBaseUrl,$url);
  }
  
  
  public function generateFormFields($formFields = array(),$customParams = array()){
     $result['formFields'] = $formFields;
     if(!empty($customParams)){
      $result['customParams'] = $customParams;  
     }
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result)); 
  }
   public function validateFormFields($formFields = array()){
     $result['valdateFieldsError'] = $formFields;
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result)); 
  }
}
