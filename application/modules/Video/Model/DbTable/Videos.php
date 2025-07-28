<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Videos.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Model_DbTable_Videos extends Core_Model_Item_DbTable_Abstract
{
  protected $_rowClass = "Video_Model_Video";
  
  public function getVideosPaginator($params = array())
  {
    $paginator = Zend_Paginator::factory($this->getVideosSelect($params));
    if( !empty($params['page']) )
    {
        $paginator->setCurrentPageNumber($params['page']);
    }
    if( !empty($params['limit']) )
    {
        $paginator->setItemCountPerPage($params['limit']);
    }
    return $paginator;
  }

  public function getVideosSelect($params = array())
  {   
    $viewer = Engine_Api::_()->user()->getViewer();

    $rName = $this->info('name');

    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');

    $select = $this->select()
        ->setIntegrityCheck(false)
        ->from($rName)
        ;
    
    //Location search
    $select = $this->getLocationItemsSelect(array_merge($params, array('modulename' => 'video', 'resource_type' => 'video')), $select);
    
    if(!empty($params['orderby']) && $params['orderby'] == 'atoz') {
      $select->order($rName .'.title ASC');
    } else if(!empty($params['orderby']) && $params['orderby'] == 'ztoa') {
      $select->order($rName .'.title DESC');
    } else  {
      $select->order( !empty($params['orderby']) ? $rName.'.'.$params['orderby'].' DESC' : $rName.'.creation_date DESC' );
    }
    
    if( !empty($params['user_id']) && is_numeric($params['user_id']) )
    {
      $owner = Engine_Api::_()->getItem('user', $params['user_id']);
      $select = $this->getProfileItemsSelect($owner, $select);
    } elseif( !empty($params['user']) && $params['user'] instanceof User_Model_User ) {
      $owner = $params['user'];
      $select = $this->getProfileItemsSelect($owner, $select);
    } else if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)){
      $param = array();
      $select = $this->getItemsSelect($param, $select);
    }

    if( !empty($params['text']) ) {
        $searchTable = Engine_Api::_()->getDbtable('search', 'core');
        $db = $searchTable->getAdapter();
        $sName = $searchTable->info('name');
        $select
            ->joinRight($sName, $sName . '.id=' . $rName . '.video_id', null)
            ->where($sName . '.type = ?', 'video')
            ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['text'])))
            //->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['text'])))
        ;
    }

    if( !empty($params['status']) && is_numeric($params['status']) )
    {
        $select->where($rName.'.status = ?', $params['status']);
    }
    if( !empty($params['search']) && is_numeric($params['search']) )
    {
        $select->where($rName.'.search = ?', $params['search']);
    }
    
    if(!isset($params['showvideo']) && empty($params['showvideo'])) {
      $select->where($rName.'.approved =?', 1);
      $select->where($rName.'.parent_type IS NULL OR parent_type = ?', 'user');
    }
    if(isset($params['actionName']) && $params['actionName'] == 'manage') {
      $select->where($rName.'.parent_type IS NULL OR parent_type = ?', 'user');
    }

    if( !empty($params['category']) )
    {
        $select->where($rName.'.category_id = ?', $params['category']);
    }
    
    if( !empty($params['category_id']) )
    {
        $select->where($rName.'.category_id = ?', $params['category_id']);
    }

    if( !empty($params['subcat_id']) )
    {
        $select->where($rName.'.subcat_id = ?', $params['subcat_id']);
    }
    if( !empty($params['subsubcat_id']) )
    {
        $select->where($rName.'.subsubcat_id = ?', $params['subsubcat_id']);
    }

    $select->where($rName.'.feedupload = ?', 0)->where($rName.'.uploadtype IS NULL');

    if( !empty($params['tag']) )
    {
        $select
            // ->setIntegrityCheck(false)
            // ->from($rName)
            ->joinLeft($tmName, "$tmName.resource_id = $rName.video_id", NULL)
            ->where($tmName.'.resource_type = ?', 'video')
            ->where($tmName.'.tag_id = ?', $params['tag']);
    }

    $select = Engine_Api::_()->network()->getNetworkSelect($rName, $select);

    if( !empty($owner) ) {
        return $select;
    }
    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0))
        return $this->getAuthorisedSelect($select);
    else
        return $select;
  }
    
  public function isVideoExists($category_id, $categoryType = 'category_id') {
    return $this->select()
      ->from($this->info('name'), 'video_id')
      ->where($categoryType . ' = ?', $category_id)
      ->query()
      ->fetchColumn();
  }

  public function fetchWallVideos($params = array()) {
    $select = $this->select()
                  ->from($this->info('name'))
                  ->where('feedupload = ?', $params['feedupload'])
                  ->where('uploadtype = ?', 'wall');
    return $this->fetchAll($select);
  }
}
