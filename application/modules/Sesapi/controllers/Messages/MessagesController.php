<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: MessagesController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Messages_MessagesController extends Sesapi_Controller_Action_Standard {
  public function inboxAction() {
     if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));  
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $sm_read = $db->query('SHOW COLUMNS FROM engine4_messages_recipients LIKE \'sm_read\'')->fetch();
    if($sm_read){
      Engine_Api::_()->getDbTable('recipients', 'messages')->update(array('sm_read' => 1), array('`user_id` = ?' => $viewer->getIdentity(), 'sm_read = ?' => 0, 'inbox_read = ?' => 0));  
    }
    $result = $paginator = Engine_Api::_()->getItemTable('messages_conversation')
        ->getInboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
     $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $this->getInfo($result,$viewer);    
  }
  public function sentAction() {
     if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));  
    $viewer = Engine_Api::_()->user()->getViewer();
    $result = $paginator = Engine_Api::_()->getItemTable('messages_conversation')
        ->getOutboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $this->getInfo($result,$viewer,'sent');    
  }
  public function markReadAction(){
    $conversation_id = $this->_getParam('conversation_id',false);
    $conversation = Engine_Api::_()->getItem('messages_conversation',$conversation_id);
    $conversation->setAsRead($this->view->viewer()); 
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => true));
  }
  public function composeAction()
  {
    // Make form
    $this->view->form = $form = new Messages_Form_Compose();
    
    // Get params
    $multi = $this->_getParam('multi');
    $to = $this->_getParam('to');
    $viewer = Engine_Api::_()->user()->getViewer();
    $toObject = null;
    
    // Build
    $isPopulated = false;
    if( !empty($to) && (empty($multi) || $multi == 'user') ) {
      $multi = null;
      // Prepopulate user
      $toUser = Engine_Api::_()->getItem('user', $to);
      $isMsgable = ( 'friends' != Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth') ||
          $viewer->membership()->isMember($toUser) );
      if( $toUser instanceof User_Model_User &&
          (!$viewer->isBlockedBy($toUser) && !$toUser->isBlockedBy($viewer)) &&
          isset($toUser->user_id) &&
          $isMsgable ) {
        $this->view->toObject = $toObject = $toUser;
        $form->toValues->setValue($toUser->getGuid());
        $isPopulated = true;
      } else {
        $multi = null;
        $to = null;
      }
    } else if( !empty($to) && !empty($multi) ) {
      // Prepopulate group/event/etc
      $item = Engine_Api::_()->getItem($multi, $to);
      // Potential point of failure if primary key column is something other
      // than $multi . '_id'
      $item_id = $multi . '_id';
      if( $item instanceof Core_Model_Item_Abstract &&
          isset($item->$item_id) && (
            $item->isOwner($viewer) ||
            $item->authorization()->isAllowed($viewer, 'edit')
          )) {
        $this->view->toObject = $toObject = $item;
        $form->toValues->setValue($item->getGuid());
        $isPopulated = true;
      } else {
        $multi = null;
        $to = null;
      }
    }
    $this->view->isPopulated = $isPopulated;

    // Build
    $isPopulated = false;    

    // Get config
    $maxRecipients = 100;


    // Check method/data
    // if( !$this->getRequest()->isPost() ) {
    //   return;
    // }
    $form->removeElement("to");
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (is_countable($validateFields) && engine_count($validateFields))
          $this->validateFormFields($validateFields);
    }

    // Process
    $db = Engine_Api::_()->getDbTable('messages', 'messages')->getAdapter();
    $db->beginTransaction();

    try {
      // Try attachment getting stuff
      $attachment = null;
      $attachmentUploadType = $this->getRequest()->getParam('attachment_type');
      $attachementVariable = $this->getRequest()->getParam('attachementVariable');
      $attachmentid = $this->getRequest()->getParam('attachmentid');
      if($attachmentUploadType == 'video' || $attachmentUploadType == 'link'){
        $attachmentData['type'] = $attachmentUploadType;
        $attachmentData[$attachementVariable] = $attachmentid;
      }
     
      if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
        $type = $attachmentData['type'];
        $attachment = Engine_Api::_()->getApi('attachment','sesapi')->{'onAttach'.ucfirst($type)}($attachmentData);
        $parent = $attachment->getParent();
        if($parent->getType() === 'user'){
          $attachment->search = 0;
          $attachment->save();
        }else {
          $parent->search = 0;
          $parent->save();
        }
      }
       ini_set("memory_limit","240M");
      if(!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0)
        $attachment = $this->uploadPhoto($_FILES);
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = $form->getValues();

      // Prepopulated
      if( $toObject instanceof User_Model_User ) {
        $recipientsUsers = array($toObject);
        $recipients = $toObject;
        // Validate friends
        if( 'friends' == Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth') ) {
          if( !$viewer->membership()->isMember($recipients) ) {
            // return $form->addError('One of the members specified is not in your friends list.');
          }
        }
        
      } else if( $toObject instanceof Core_Model_Item_Abstract &&
          method_exists($toObject, 'membership') ) {
        $recipientsUsers = $toObject->membership()->getMembers();
//        $recipients = array();
//        foreach( $recipientsUsers as $recipientsUser ) {
//          $recipients[] = $recipientsUser->getIdentity();
//        }
        $recipients = $toObject;
      }
      // Normal
      else {
        //$recipients = preg_split('/[,. ]+/', $values['toValues']);
        $recipients = explode(',', $_POST['to']);
        // clean the recipients for repeating ids
        // this can happen if recipient is selected and then a friend list is selected
        $recipients = array_unique($recipients);
        // Slice down to 10
        $recipients = array_slice($recipients, 0, $maxRecipients);
        
        // Get user objects
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $recipients);

        // Validate friends
        if( 'friends' == Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth') ) {
          foreach( $recipientsUsers as &$recipientUser ) {
            if( !$viewer->membership()->isMember($recipientUser) ) {
              //return $form->addError('One of the members specified is not in your friends list.');
            }
          }
        }
      }

      // Create conversation
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
        $viewer,
        $recipients,
        $values['title'],
        $values['body'],
        $attachment
      );

      // Send notifications
      foreach( $recipientsUsers as $user ) {
        if( $user->getIdentity() == $viewer->getIdentity() ) {
          continue;
        }
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification(
          $user,
          $viewer,
          $conversation,
          'message_new'
        );
      }
      
      

      // Increment messages counter
      Engine_Api::_()->getDbTable('statistics', 'core')->increment('messages.creations');

      if($conversation->recipients == 1){
        if( $conversation->hasResource() && ($resource = $conversation->getResource()) ) {
        $sender = $resource;
      } else if( $conversation->recipients > 1 ) {
        $sender = $viewer;
      } else {
        foreach( $conversation->getRecipients() as $tmpUser ) {
          if( $tmpUser->getIdentity() != $viewer->getIdentity() ) {
            $sender = $tmpUser;
          }
        }
      }
      
      if( (!isset($sender) || !$sender) && $viewer->getIdentity() !== $conversation->user_id ){
        $sender = Engine_Api::_()->user()->getUser($conversation->user_id);
      }
      if( !isset($sender) || !$sender ) {
        //continue;
        $sender = new User_Model_User(array());
      }
      
      $senderName = $sender->getTitle();
      }else {
        $senderName = $this->view->translate(array('%s person', '%s people', $conversation->recipients),
                    $this->view->locale()->toNumber($conversation->recipients));
      }
      
      // Commit
      $db->commit();
      $result["message"] = array_merge($conversation->toArray(),array('recipients'=>$senderName));
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }   
    
  }
  public function viewAction()
  {
   
    $id = $this->_getParam('id');
    $viewer = Engine_Api::_()->user()->getViewer();
     
    // Get conversation info
    $conversation = Engine_Api::_()->getItem('messages_conversation', $id);
    $conversationRecepients = array();
    // Make sure the user is part of the conversation
    if( !$conversation || !$conversation->hasRecipient($viewer) ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array())); 

    // Check for resource
    if( !empty($conversation->resource_type) &&
        !empty($conversation->resource_id) ) {
      $resource = Engine_Api::_()->getItem($conversation->resource_type, $conversation->resource_id);
      if( !($resource instanceof Core_Model_Item_Abstract) ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array())); 
      }
    }
    
    // Otherwise get recipients
    else {
      $recipients = $conversation->getRecipients();
      $blocked = false;
      // This is to check if the viewered blocked a member
      $viewer_blocked = false;
      $viewer_blocker = "";
      $blocker = false;
      
      foreach($recipients as $recipient){
        if($viewer->getIdentity() != $recipient->getIdentity())
                $conversationRecepients[] = $recipient;
        if ($viewer->isBlockedBy($recipient)){
          $blocked = true;
          $blocker = $recipient;
        }
        elseif ($recipient->isBlockedBy($viewer)){
          $viewer_blocked = true;
          $viewer_blocker = $recipient;
        }
      }
      $blocked = $blocked;
      $viewer_blocked = $viewer_blocked;
      $blocker = $blocker;
      $viewer_blocker = $viewer_blocker;
    }
    
    // Can we reply?
    $locked = $conversation->locked;
    if( !$conversation->locked ) {      
      // Process form
      $this->view->form = $form = new Messages_Form_Reply();      
      if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
      {
        
        $db = Engine_Api::_()->getDbTable('messages', 'messages')->getAdapter();
        $db->beginTransaction();
        try
        {
          // Try attachment getting stuff
      $attachment = null;
      $attachmentUploadType = $this->getRequest()->getParam('attachment_type');
      $attachementVariable = $this->getRequest()->getParam('attachementVariable');
      $attachmentid = $this->getRequest()->getParam('attachmentid');
      if($attachmentUploadType == 'video' || $attachmentUploadType == 'link'){
        $attachmentData['type'] = $attachmentUploadType;
        if(strpos($attachmentid,'https://') === false && strpos($attachmentid,'http://') === false && intval($attachmentid) == 0) {
          $attachmentid= 'http://'.$attachmentid;
        } 
        $attachmentData[$attachementVariable] = $attachmentid;
      }
     
      if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
        $type = $attachmentData['type'];
        $attachment = Engine_Api::_()->getApi('attachment','sesapi')->{'onAttach'.ucfirst($type)}($attachmentData);
        $parent = $attachment->getParent();
        if($parent->getType() === 'user'){
          $attachment->search = 0;
          $attachment->save();
        }else {
          $parent->search = 0;
          $parent->save();
        }
      }

      if(!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0)
        $attachment = $this->uploadPhoto($_FILES);
          $values = $form->getValues();
          $values['conversation'] = (int) $id;
          
          $body = html_entity_decode($values['body'], ENT_QUOTES, 'UTF-8');
          $bodyEmojis = explode(' ', $body);
          foreach($bodyEmojis as $bodyEmoji) {
            $emojisCode = Engine_Api::_()->sesapi()->encode($bodyEmoji);
            $body = str_replace($bodyEmoji,$emojisCode,$body);
          }

          $conversation->reply(
            $viewer,
            $body,
            $attachment
          );
          

          // Send notifications
          foreach( $recipients as $user )
          {
            if( $user->getIdentity() == $viewer->getIdentity() )
            {
              continue;
            }
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification(
              $user,
              $viewer,
              $conversation,
              'message_new'
            );
          }

          // Increment messages counter
          Engine_Api::_()->getDbTable('statistics', 'core')->increment('messages.creations');

          $db->commit();
        }
        catch( Exception $e )
        {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array())); 
        }
      }else if($this->getRequest()->isPost() && isset($_POST['body'])){
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        //$formFields[4]['name'] = "file";
        if(is_countable($validateFields) && engine_count($validateFields))
          $this->validateFormFields($validateFields);
      }
      
      
    }


    // Make sure to load the messages after posting :P
      $messages = $conversation->getMessages($viewer);
      $this->sendMessages($messages,$viewer,$conversation,$conversationRecepients,$blocked,$viewer_blocked);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array())); 
  }
  public function sendMessages($messages,$viewer,$conversation,$recipients,$blocked,$viewer_blocked){
    $result = array();
      $counter = 0;
      $coreApi = Engine_Api::_()->sesapi();
      foreach($messages as $message){
        $result[$counter]['message_id'] = $message->getIdentity();
        $result[$counter]['conversation_id'] = $message->conversation_id;
        $result[$counter]['user_id'] = $message->user_id;
        if($message->user_id == $viewer->getIdentity())
          $result[$counter]['mine'] = 1;
        else
          $result[$counter]['mine'] = 0;
        $result[$counter]['user_image'] = $this->userImage($message->user_id,'thumb.icon');
        $result[$counter]['title'] = $message->title;
        $result[$counter]['body'] = $coreApi->decode($message->body);
        $result[$counter]['date'] = $message->date;
        if($message->attachment_type){
          $attachment = Engine_Api::_()->getItem($message->attachment_type,$message->attachment_id)  ;
          if($attachment){
            $result[$counter]['attachment']['message_id'] = $message->getIdentity();
            $result[$counter]['attachment']['attachment_id'] = $message->attachment_id;
            if($attachment->getType() == 'core_link')
            $result[$counter]['attachment']['attachment_uri'] = $attachment->uri;
            $result[$counter]['attachment']['attachment_type'] = $message->attachment_type;
            $result[$counter]['attachment']['attachment_title'] = $attachment->getTitle();
            $photoUrl = $this->getBaseUrl(false,$attachment->getPhotoUrl());
            list($width,$height) = getimagesize($photoUrl);
            $result[$counter]['attachment']['attachment_photo_width'] = $width;
            $result[$counter]['attachment']['attachment_photo_height'] = $height;
            $result[$counter]['attachment']['attachment_description'] = $attachment->getDescription();
            $result[$counter]['attachment']['attachment_photo'] = $photoUrl;
          }
        }
        $counter++;  
      }
      $results['message'] = $result;
      $locked = 0;
      // $blocked = false;
      // $viewer_blocked = false;
      $messageLocked = "";
      if( !$locked ){
        if( (!$blocked && !$viewer_blocked) || (engine_count($recipients)>1)){
          $locked = 0;  
        }else if($viewer_blocked){
          $locked = 1;
          //  $messageLocked = $this->view->translate('You can no longer respond to this message because you have blocked %1$s.', $viewer_blocker->getTitle());
        }else{
          $locked = 1;
          //  $messageLocked = $this->view->translate('You can no longer respond to this message because %1$s has blocked you.', $blocker->getTitle());
        }
      }
      $results['lock'] = $locked;
      // $results['lock_message'] = $messageLocked;
      $conversation->setAsRead($viewer);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $results));   
  }
  public function uploadPhoto($file)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$this->_helper->requireUser()->checkRequire() )
    {
      
      $error = $this->view->translate('Max file size limit exceeded (probably).');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array())); 
    }

    if( !$this->getRequest()->isPost() )
    {
      $error = $this->view->translate('Invalid request method');
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array())); 
    }
    if( !isset($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name']) )
    {
      $error =  $this->view->translate('Invalid Upload');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array())); 
    }

    $db = Engine_Api::_()->getItemtable('photo')->getAdapter();
    //$db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();

      $photoTable = Engine_Api::_()->getItemTable('photo');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'user',
        'owner_id' => $viewer->getIdentity()
      ));
      $photo->save();

      $photo->setPhoto($file['image']);

      $table = Engine_Api::_()->getItemtable('album');
      $album = $table->getSpecialAlbum($viewer, 'message');

      $photo->album_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id )
      {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      $auth      = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($photo, 'everyone', 'view',    true);
      $auth->setAllowed($photo, 'everyone', 'comment', true);
      $auth->setAllowed($album, 'everyone', 'view',    true);
      $auth->setAllowed($album, 'everyone', 'comment', true);


      //$db->commit();
      return $photo;
    } catch( Exception $e ) {
      //$db->rollBack();
      $this->view->status = false;
      $error = $this->view->translate($e->getMessage());
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array())); 
    } catch( Exception $e ) {
      //$db->rollBack();
      $this->view->status = false;
      $error = $this->view->translate('An error occurred.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array())); 
    }
  }
   public function deleteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) 
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));  
    
    $message_id = $this->_getParam('conversation_id');
    //$messages = explode(',', $message_ids);
    
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    
    $db = Engine_Api::_()->getDbTable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      //foreach ($messages as $message_id) {
        $recipients = Engine_Api::_()->getItem('messages_conversation', $message_id)->getRecipientsInfo();
        //$recipients = Engine_Api::_()->getApi('core', 'messages')->getConversationRecipientsInfo($message_id);
        foreach ($recipients as $r) {
          if ($viewer_id == $r->user_id) {
            $this->view->deleted_conversation_ids[] = $r->conversation_id;
            $r->inbox_deleted  = true;
            $r->outbox_deleted = true;
            $r->save();
          }
        }
      //}
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => true)); 
    }
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => true)); 
  }
  
  public function getInfo($result,$viewer,$sent = false){
    $results = array();
    $counter = 0;
    foreach($result as $message){
      if(!$sent)
        $messageInfo = $message->getInboxMessage($viewer);
      else
        $messageInfo = $message->getOutboxMessage($viewer);
      $recipient = $message->getRecipientInfo($viewer);
      $resource = "";
      $sender   = "";
      if( $message->hasResource() &&
                ($resource = $message->getResource()) ) {
        $sender = $resource;
      } else if( $message->recipients > 1 ) {
        $sender = $viewer;
      } else {
        foreach( $message->getRecipients() as $tmpUser ) {
          if( $tmpUser->getIdentity() != $viewer->getIdentity() ) {
            $sender = $tmpUser;
          }
        }
      }
      if( (!isset($sender) || !$sender) && $viewer->getIdentity() !== $message->user_id ){
        $sender = Engine_Api::_()->user()->getUser($message->user_id);
      }
      if( !isset($sender) || !$sender ) {
        //continue;
        $sender = new User_Model_User(array());
      }
      if(!$sender->getIdentity())
        continue;
      $results['message'][$counter]['conversation_id'] = $message['conversation_id'];
      if($message->recipients == 1){
        $senderName =   $sender->getTitle();
      }else {
        $senderName = $this->view->translate(array('%s person', '%s people', $message->recipients),
                    $this->view->locale()->toNumber($message->recipients));
      }
      
      $results['message'][$counter]['sender'] = $senderName;
      $results['message'][$counter]['title'] = $message['title'];
      $results['message'][$counter]['read'] = $recipient['inbox_read'];
      $results['message'][$counter]['user_id'] = $message['user_id'];
      $results['message'][$counter]['sender_id'] = $sender->getIdentity();
      $results['message'][$counter]['user_image'] = $this->userImage($sender->getIdentity());
      $results['message'][$counter]['body'] = $messageInfo['body'];
      $results['message'][$counter]['date'] = $messageInfo['date'];      
      $counter++;
    } 
    $extraParams['pagging']['total_page'] = $result->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $result->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
  }
}
