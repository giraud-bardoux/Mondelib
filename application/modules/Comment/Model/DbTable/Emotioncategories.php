<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Emotioncategories.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Emotioncategories extends Engine_Db_Table
{
  protected $_rowClass = 'Comment_Model_Category';

  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getCategories($params));
  }
  public function getCategories($params = array())
  {
    $select = ($this->select());
    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
  public function searchResult($text = '')
  {
    $galleryTableName = Engine_Api::_()->getItemTable('comment_emotiongallery')->info('name');
    $fileTable = Engine_Api::_()->getItemTable('comment_emotionfile');
    $fileTableName = $fileTable->info('name');

    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');

    $select = $this->select()
      ->setIntegrityCheck(false)
      ->from($this->info('name'), array())

      ->joinLeft($galleryTableName, $galleryTableName . '.category_id =' . $this->info('name') . '.category_id', array('file_id', 'gallery_id'))
      ->joinLeft($fileTableName, $fileTableName . '.gallery_id =' . $galleryTableName . '.gallery_id', array('photo_id', 'files_id', 'gallery_id'))

      ->where($this->info('name') . '.title LIKE ?', $text . '%');

    $tagsTable = Engine_Api::_()->getDbtable('tags', 'core');
    $tagsTableName = $tagsTable->info('name');
    $results = $tagsTable->select()->from($tagsTableName, array('tag_id'))->where($tagsTableName . '.text =?', $text)->limit(1)->query()->fetchColumn();
    if ($results) {
      $select->orwhere($tmName . '.tag_id = ?', $results);
    } else {
      $select->orwhere($tmName . '.tag_id = ?', 0);
    }
    $select->joinLeft($tmName, "$tmName.resource_id = $fileTableName.files_id")
      ->where($tmName . '.resource_type = ?', 'comment_files');

    $select->where($fileTableName . '.photo_id IS NOT NULL')
      ->limit(25)
      ->order('Rand()');
    return $this->fetchAll($select);
  }
}
