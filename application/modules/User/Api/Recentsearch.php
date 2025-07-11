<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Search.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
class User_Api_Recentsearch extends Core_Api_Abstract
{
  protected $_types;
  
  public function index($query, $type = '', $id = 0)
  {
    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // Check if already indexed
    $table = Engine_Api::_()->getDbtable('recentsearch', 'user');
    $select = $table->select()
                  ->where('user_id = ?', $user_id);
    if(!empty($query)) {
      $select->where('query = ?', $query);
    }
    if(!empty($type)) {
      $select->where('type = ?', $type);
    }
    if(!empty($id)) {
      $select->where('id = ?', $id);
    }
    $select->limit(1);

    $row = $table->fetchRow($select);

    if( null === $row )
    {
      $row = $table->createRow();
      $row->query = $query;
      $row->type = $type;
      $row->id = $id;
      $row->creation_date = date('Y-m-d H:i:s');
      $row->user_id = $user_id;
    }
    $row->save();
  }

  public function unindex($query = '', $type = '')
  {
    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $table = Engine_Api::_()->getDbtable('recentsearch', 'user');

    if($query) {
      $table->delete(array('query = ?' => $query,'user_id = ?' => $user_id));
    } else {
      $table->delete(array('user_id = ?' => $user_id));
    }

    //return $this;
  }

  public function getResults($query = null, $type = null)
  {
    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // Build base query
    $table = Engine_Api::_()->getDbtable('recentsearch', 'user');

    $select = $table->select()
      ->where('user_id = ?', $user_id)
      ->order('recentsearch_id DESC')
      ->limit(5)
      ;

    return $table->fetchAll($select);
  }
}