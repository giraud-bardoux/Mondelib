<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Widget_PhotoVideoInfoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();

    if ($subject->getType() == 'album_photo') {
      $this->view->album = $album = $subject->getAlbum();
      $this->view->canEdit = $album->authorization()->isAllowed($viewer, 'edit');
      $this->view->canDelete = $album->authorization()->isAllowed($viewer, 'delete');
      $this->view->canTag = $album->authorization()->isAllowed($viewer, 'tag');
    } elseif($subject->getType() == 'video') {
      // Check if edit/delete is allowed
      $this->view->canEdit = $subject->authorization()->isAllowed($viewer, 'edit');
      $this->view->canDelete = $subject->authorization()->isAllowed($viewer, 'delete');
    }
  }
}
