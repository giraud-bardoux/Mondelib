<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: RatingController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
class Core_RatingController extends Core_Controller_Action_Standard {
  public function rateAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $rating = $this->_getParam('rating');

    $modulename = $this->_getParam('modulename', null);
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $notificationType = $this->_getParam('notificationType', null);

    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);

    if(isset($resource->owner_id)) {
      $owner = Engine_Api::_()->getItem('user', $resource->owner_id);
    } else {
      $owner = Engine_Api::_()->getItem('user', $resource->user_id);
    }

    $table = Engine_Api::_()->getDbtable('ratings', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $table->setRating(array('resource_id' => $resource_id, 'rating' => $rating, 'resource_type' => $resource_type));

      $resource->rating = $table->getRating(array('resource_id' => $resource_id, 'resource_type' => $resource_type));
      $resource->save();

      if (!empty($notificationType) && $owner->getIdentity() != $viewer_id) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $resource, $notificationType);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $total = $table->ratingCount(array('resource_id' => $resource_id, 'resource_type' => $resource_type));

    $data = array();
    $data[] = array(
      'total' => $total,
      'rating' => $rating,
    );
    return $this->_helper->json($data);
  }
}
