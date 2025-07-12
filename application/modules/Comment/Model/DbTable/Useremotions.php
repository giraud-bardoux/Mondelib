<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Useremotions.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Useremotions extends Engine_Db_Table
{
  protected $_rowClass = 'Comment_Model_Useremotion';
  public function getEmotion($params = array())
  {
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if (!$viewer_id)
      return array();
    $select = $this->select()->from($this->info('name'), '*')->where('user_id =?', $viewer_id)->setIntegrityCheck(false);
    $galleryTableName = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->info('name');
    $select->joinLeft($galleryTableName, $galleryTableName . '.gallery_id =' . $this->info('name') . '.gallery_id', array('title', 'file_id'))->where($galleryTableName . '.gallery_id IS NOT NULL');
    if (!empty($params['gallery_id']))
      $select->where($this->info('name') . '.gallery_id =?', $params['gallery_id']);
    // if($params['type'] && $params['type'] == 'user')
    // $select->where('enabled = ?', 1);
    return $this->fetchAll($select);
  }
}
