<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10180 2014-04-28 21:02:01Z lucas $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @todo SignupController.php: integrate invite-only functionality (reject if invite code is bad)
 * @todo AdminController.php: add in stricter settings for admin level checking
 */
class Invite_IndexController extends Core_Controller_Action_Standard
{

  public function init()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;
    if( null === $id ) {
      $subject = Engine_Api::_()->user()->getViewer();
      Engine_Api::_()->core()->setSubject($subject);
    } else {
      $subject = Engine_Api::_()->getItem('user', $id);
      Engine_Api::_()->core()->setSubject($subject);
    }

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setAuthParams(
      $subject,
      null,
      'edit'
    );

    // Set up navigation
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));
  }
  
  public function indexAction() {
    
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    
    $settings = Engine_Api::_()->getApi('settings', 'core');

    if(!$settings->getSetting('invite.enable', 1)) {
      return $this->_forward('requireauth', 'error', 'core');
    } else {
      if($viewer->getIdentity()) {
        $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
        $levelsvalue = unserialize($levels);
        if(!engine_in_array($viewer->level_id, $levelsvalue))
          return $this->_forward('requireauth', 'error', 'core');
      }
    }
    
    $is_ajax = $this->_getParam('is_ajax', 0);

    if(empty($is_ajax) && $settings->getSetting('invite.referralforsingup', 1)) {
      if (empty($viewer->referral_code)) {
        $referralCode = substr(md5(rand(0, 999) . $viewer->email), 10, 7);
        $viewer->referral_code = $referralCode;
        $viewer->save();
      }
      $this->view->referral_code = $viewer->referral_code;
      $this->view->referral = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'signup'), 'default', true) . '?referral_code=' . $viewer->referral_code;
    }

    // Check for users only
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    if(empty($is_ajax)) {
      // Make form
      $this->view->form = $form = new Invite_Form_Invite();

      if( !$this->getRequest()->isPost() ) {
        return;
      }

      if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
      }
    }

    if (isset($_POST['params']) && $_POST['params'])
      parse_str($_POST['params'], $searchArray);
      
    if(!empty($is_ajax)) {
      $this->view->form = $form = new Invite_Form_Invite();
      // Process
      $values = $searchArray;
      $this->view->recipients = $values['recipients'];
      $this->view->allInvites = Engine_Api::_()->getDbTable('invites', 'invite')->getAllInvites($values['recipients']);
      $recipients = explode(',', $values['recipients']);
      $recipients = array_map('trim', $recipients);

      $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
      if(engine_count($recipients) == 1) {
        $this->view->canInvite = Engine_Api::_()->getDbTable('invites', 'invite')->canInvite($recipients);
      }
    }

    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    try {
      $inviteTable->setDefaultAlreadyMembers([]);
      $emailsSent = $inviteTable->sendInvites($viewer, $searchArray['recipients'], @$searchArray['message'],$searchArray['friendship'], '', 'Invite');

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      if( APPLICATION_ENV == 'development' ) {
        throw $e;
      }
    }
    $this->view->already_members = $inviteTable->getAlreadyMembers();
    $this->view->emails_sent = $emailsSent;
    return $this->render('sent');
    echo json_encode(array('emails_sent' => $emailsSent, 'data' => $this->render('sent')));die;
  }

  public function importinviteAction() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if(!$settings->getSetting('invite.enable', 1)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    // Check for users only
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    $importEmails = $_POST['importEmails'];
    $importEmails = explode(',', $importEmails);


    if($_POST['import_subject']) {
      $import_subject = $_POST['import_subject'];
    } else {
      $import_subject = 'You have received an invitation to join our social network.';
    }
    $import_message = $_POST['import_message'];

    $viewer = Engine_Api::_()->user()->getViewer();
    
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    try {
      $recipients = implode(',', $importEmails);

      $emailsSent = $inviteTable->sendInvites($viewer, $recipients, $import_message, $_POST['friendship'], '', 'CSV');

      $db->commit();
      echo json_encode(array('emails_sent' => 1));die;
    } catch( Exception $e ) {
      $db->rollBack();
      if( APPLICATION_ENV == 'development' ) {
        throw $e;
      }
    }
  }
  
  public function csvimportAction() {

    $socialMediaName = $this->_getParam('socialMediaName', null);
    $is_ajax = $this->_getParam('is_ajax', null);
    
    $settings = Engine_Api::_()->getApi('settings', 'core');

    if($socialMediaName == 'gmail' && !empty($is_ajax)) {

      $socialEmails = $this->_getParam('socialEmails', null);
      if(empty($socialEmails)) {
        echo json_encode(array('status'=>"false"));die;
      } else {
        $socialEmails = explode(',', $socialEmails);
        $importedData = array();
        foreach($socialEmails as $key => $socialEmail) {
          $socialContent = explode('||', $socialEmail);
          if(!$socialContent[0] && !$socialContent[1]) continue;
          if (filter_var($socialContent[0], FILTER_VALIDATE_EMAIL)) {
            $importedData[] = array('name' => $socialContent[1], 'email' => $socialContent[0]);
          }
        }

        if($importedData) {
          $showData =  $this->view->partial('_csvimport.tpl','invite',array('importedData' => $importedData, 'importmethod' => 'gmail'));
        }
        echo Zend_Json::encode(array('status' => 1, 'message' => $showData, 'importmethod' => 'gmail'));exit();
      }
    } else if($socialMediaName == 'hotmail' && !empty($is_ajax)) {

      $socialEmails = $this->_getParam('socialEmails', null);
      $socialEmails = Zend_Json::decode($socialEmails);
      if(empty($socialEmails)) {
        echo json_encode(array('status'=>"false"));die;
      } else {
        $importedData = $socialEmails;
        if($importedData) {
          $showData =  $this->view->partial('_csvimport.tpl','invite',array('importedData' => $importedData, 'importmethod' => 'hotmail'));
        }
        echo Zend_Json::encode(array('status' => 1, 'message' => $showData, 'importmethod' => 'hotmail'));exit();
      }
    } else if($socialMediaName == 'hotmail' && empty($is_ajax)) {

			$client_id = 	$settings->getSetting('invite.hotmailclientid',false);
			$client_secret = $settings->getSetting('invite.hotmailclientsecret',false);

			$baseURL = Zend_Registry::get('StaticBaseUrl');

			if($baseURL)
				$baseurl = $baseURL;
			else
				$baseurl = '/';

			$redirect_uri = (_ENGINE_SSL ? 'https://' : 'http://')  . $_SERVER['HTTP_HOST'] .$baseurl.'invite/index/csvimport/socialMediaName/hotmail/';
      if(!isset($_GET['code'])) {
        $urls_ = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize?client_id='.$client_id.'&&response_mode=query&scope=https%3A%2F%2Fgraph.microsoft.com%2Fcontacts.read%20&response_type=code&redirect_uri='.$redirect_uri;
        header('location:'.$urls_);
      }
      
			if(isset($_GET['code'])) {
				$auth_code = $_GET["code"];
				$fields=array(
          'code'=>  urlencode($auth_code),
          'client_id'=>  urlencode($client_id),
          'client_secret'=>  urlencode($client_secret),
          'redirect_uri'=>  urlencode($redirect_uri),
          'grant_type'=>  urlencode('authorization_code')
				);
				$post = '';
				foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
				$post = rtrim($post,'&');
				$curl = curl_init();
				curl_setopt($curl,CURLOPT_URL,'https://login.microsoftonline.com/consumers/oauth2/v2.0/token');
				curl_setopt($curl,CURLOPT_POST,5);
				curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
				$result = curl_exec($curl);
				curl_close($curl);
				$response =  json_decode($result);
				$accesstoken = $response->access_token;
        $url = 'https://graph.microsoft.com/v1.0/me/contacts/'.$response->user_id;
        $curl = curl_init();
        curl_setopt_array( $curl, 
          array( CURLOPT_CUSTOMREQUEST => 'GET'
                , CURLOPT_URL => $url
                , CURLOPT_HTTPHEADER => array(  'Authorization: Bearer '.$accesstoken )
                , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
          ) );
          $response = curl_exec($curl);
          $http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
          curl_close($curl);
				
				$xml = json_decode($response,true);
				$msn_email = "";
				$counter = 0;
				foreach($xml['value'] as $emails) {

					$execute = false;
					$name = $emails['displayName'];

          $emailNew = array();
          foreach($emails['emailAddresses'] as $email){
            $emailNew[] = $email['address'];
          }
					$email_ids = implode(",",array_unique($emailNew));//will get more email primary,sec etc with comma separate
					$email_ids = trim($email_ids,',');
					if(engine_count(explode(',',$email_ids))){
						$dataEx = explode(',',$email_ids);
						foreach($dataEx as $val){
							if(!$val)
								continue;
							$importedData[$counter]['email'] = $val;
							$importedData[$counter]['name'] = $name;
							$execute = true;
							$counter++;
						}
					}
          if(($email_ids)){
            $importedData[$counter]['email'] = $email_ids;
            $importedData[$counter]['name'] = $name;
            $counter++;
          }
				}
        ?>
        <script type="text/javascript">
            window.opener.inviteHotmailData('<?php echo Zend_Json::encode($importedData); ?>');
            window.close();
          </script>
        <?php
			}
    }

    if(isset($_FILES['contact']) && $_FILES['contact']['name'] != '') {

      $csv_file = $_FILES['contact']['tmp_name']; // specify CSV file path

      $csvfile = fopen($csv_file, 'r');
      $theData = fgets($csvfile);
      $thedata = explode(',',$theData);
      $name = $email = $counter = 0;

      foreach($thedata as $data) {

        //Direct CSV
        if(trim(strtolower($data)) == 'name'){
          $name = $counter;
        } else if(trim(strtolower($data)) == 'email'){
          $email = $counter;
        }

        //Outlook
        if($data == 'First Name'){
          $name = $counter;
        } else if($data == 'E-mail Address'){
          $email = $counter;
        }

        //Yahoo
        if($data == '"First"'){
          $name = $counter;
        } else if($data == '"Email"'){
          $email = $counter;
        }

        $counter++;
      }

      $i = 0;
      $importedData = array();
      while (!feof($csvfile))
      {
        $csv_data[] = fgets($csvfile, 1024);
        $csv_array = explode(",", $csv_data[$i]);
        if(!engine_count($csv_array))
          continue;

        if($_FILES['contact']['name'] == 'yahoo_contacts.csv') {

          $email = trim($csv_array[$email], '"');
          if(empty($email)) continue;
          $importedData[$i]['name'] = trim($csv_array[$name], '"');

          if(isset($email))
            $importedData[$i]['email'] = $email;

          if(!$importedData[$i]['email'] && !$importedData[$i]['name'])
            unset($importedData[$i]);

        } else {
          if(isset($csv_array[$name]))
            $importedData[$i]['name'] = $csv_array[$name];
          if(isset($csv_array[$email]))
            $importedData[$i]['email'] = $csv_array[$email];
          if(!$importedData[$i]['email'] || !$importedData[$i]['name'])
            unset($importedData[$i]);
        }
        $i++;
      }
      fclose($csvfile);


      if($importedData) {
        $showData =  $this->view->partial('_csvimport.tpl','invite',array('importedData' => $importedData, 'importmethod' => 'csv'));
      }
      echo Zend_Json::encode(array('status' => 1, 'message' => $showData, 'importmethod' => 'csv'));exit();
    }
    echo json_encode(array('status'=>"false"));die;
  }
  
  public function resendinviteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->invite_id = $invite_id = $this->_getParam('invite_id', null);
    $this->view->invite = $invite = Engine_Api::_()->getItem('invite', $invite_id);

    // Process
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    try {
      $emailsSent = $inviteTable->sendInvites($viewer, $invite->recipient, 'You are being invited to join our social network.', '', 'resend');
      if($invite)
				$invite->delete();
      $db->commit();
      echo json_encode(array('status' => 'true', 'message' => 'Resend Invite Successfully.'));die;
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
  
  public function notifyadminAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->invite_id = $invite_id = $this->_getParam('invite_id', null);
    $this->view->invite = $invite = Engine_Api::_()->getItem('invite', $invite_id);

    // Process
    $table = $invite->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
    
      $adminLink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'manage'), 'admin_default', true);

      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      $allAdmins = Engine_Api::_()->getItemTable('user')->getAllAdmin();
      $adminSideLink = '<a href="'.$adminLink.'" >'.$this->view->translate("cancel").'</a>';

      foreach ($allAdmins as $admin) {
        if($viewer->isSelf($admin)){
          continue;
        }
        $useProfileLink = '<a href="'.$viewer->getHref().'" >'.$viewer->getTitle().'</a>';
        $notifyApi->addNotification($admin, $viewer, $admin, 'invite_notify_admin',array('userprofilelink'=>$useProfileLink,'adminsidelink'=>$adminSideLink,'recipientemail' => $invite->recipient));
      }
      $db->commit();
      echo json_encode(array('status' => 'true', 'message' => 'Notify Admin Successfully.'));die;
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
}
