<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9966 2013-03-19 00:00:35Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Album_Widget_BreadcrumbPhotoController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photo = $photo = Engine_Api::_()->core()->getSubject('album_photo');

    if(!$photo) 
      return $this->setNoRender();
      
    $this->view->album = $album = $photo->getAlbum();
    
    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_id = $this->_getParam('message');
    $message_view = false;
    if ($message_id) {
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) $message_view = true;
    }
    
    $this->view->message_view = $message_view;
    $this->view->isprivate = 0;
    if(engine_in_array($album->type, array("group","event"))){
        $this->view->isprivate = 1;
        if($album->getOwner()->getIdentity() == $viewer->getIdentity()){
            $this->view->isprivate = 0;
        }
    }
  }
}
