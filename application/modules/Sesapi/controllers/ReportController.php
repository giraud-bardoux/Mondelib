<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ReportController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_ReportController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {  
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
  }
  
  public function createAction()
  {
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();
    $this->view->form = $form = new Sesapi_Form_Report();
    if($this->_getParam('getForm')){
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (is_countable($validateFields) && engine_count($validateFields))
          $this->validateFormFields($validateFields);
    }

    // Process
    $table = Engine_Api::_()->getItemTable('core_report');
    $db = $table->getAdapter();
    $db->beginTransaction(); 
    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $report = $table->createRow();
      $report->setFromArray(array_merge($form->getValues(), array(
        'description'=>$_POST['des'],
        'subject_type' => $subject->getType(),
        'subject_id' => $subject->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      )));
      $report->save();
      // Increment report count
      Engine_Api::_()->getDbTable('statistics', 'core')->increment('core.reports');
      $db->commit();

      // Increment report count
      Engine_Api::_()->getDbTable('statistics', 'core')->increment('core.reports');
      $adminLink = 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'report'), 'admin_default', true);

      $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      $allAdmins = Engine_Api::_()->getItemTable('user')->getAllAdmin();
      $authTable = Engine_Api::_()->authorization()->getAdapter('levels');
      $adminSideLink = '<a href="'.$adminLink.'" >'.$this->view->translate("site").'</a>';

      foreach ($allAdmins as $admin) {
        if($viewer->isSelf($admin)){
          continue;
        }
        if($authTable->getAllowed('user', $admin, 'abuseNotifi')){
          $useProfileLink = '<a href="'.$viewer->getHref().'" >'.$this->view->translate("User").'</a>';
          $senderName = '<a href="'.$viewer->getHref().'" >'.$viewer->getTitle().'</a>';

          $notifyApi->addNotification($admin, $viewer, $admin, 'abuse_report',array('userprofilelink'=>$useProfileLink,'adminsidelink'=>$adminSideLink));
        }
        if($authTable->getAllowed('user', $admin, 'abuseEmail')){
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,
                    "abuse_report",array("admin_link"=>$adminLink,'sender_name'=>$senderName));
        }
      }

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>"Your report has been submitted."));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));
    }
    
    
  }
}
