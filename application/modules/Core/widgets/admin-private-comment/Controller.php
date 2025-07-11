<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_AdminPrivateCommentController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $manifest = Zend_Registry::get('Engine_Manifest');
    if (null === $manifest) {
      throw new Engine_Api_Exception('Manifest data not loaded!');
    }
    $itemTypes = [];
    foreach ($manifest as $module => $config) {
      if (!isset($config['items'])) {
          continue;
      }
      $itemTypes = array_merge($itemTypes, $config['items']);
    }

    $table = Engine_Api::_()->getDbTable('comments', 'core');
    $select = $table->select()
            ->from($table->info('name'))
            ->where('resource_id <> ?', 0)
            ->where('resource_type IN (?)', $itemTypes)
            ->order('comment_id DESC')
            ->limit(2);
    $this->view->comments = $comments = $table->fetchAll($select);
  
    $activityCommentsTable = Engine_Api::_()->getDbTable('comments', 'activity');
    $select = $activityCommentsTable->select()
            ->from($activityCommentsTable->info('name'))
            ->order('comment_id DESC')
            ->limit(2);
    $this->view->activityComments = $activityComments = $activityCommentsTable->fetchAll($select);
  }
}
