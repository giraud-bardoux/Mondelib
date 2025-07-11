<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Search.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Search extends Core_Api_Abstract
{
  protected $_types;
  
  public function index(Core_Model_Item_Abstract $item)
  {
    // Check if not search allowed
    if( isset($item->search) && !$item->search )
    {
      return false;
    }

    // Get info
    $type = $item->getType();
    $id = $item->getIdentity();
    $title = substr(trim($item->getTitle()), 0, 255);
    
    if(isset($item->description))
      $description = trim(strip_tags($item->description));
    else if(isset($item->body))
      $description = trim(strip_tags($item->body));
    else 
      $description = substr(trim(strip_tags($item->getDescription())), 0, 255);

    $keywords = substr(trim($item->getKeywords()), 0, 255);
    $hiddenText = substr(trim($item->getHiddenSearchData()), 0, 255);
    
    if (method_exists($item, 'getUsername')) {
      $username = $item->getUsername();
    }
    
    // Ignore if no title and no description
    if( !$title && !$description )
    {
      return false;
    }
    
    //If child item then do not enter in search table
    if(isset($item->parent_type) && isset($item->parent_id) && !empty($item->parent_type) && ($item->parent_type != 'user') && !empty($item->parent_id)) {
      return false;
    }

    // Check if already indexed
    $table = Engine_Api::_()->getDbtable('search', 'core');
    $select = $table->select()
      ->where('type = ?', $type)
      ->where('id = ?', $id)
      ->limit(1);

    $row = $table->fetchRow($select);

    if( null === $row )
    {
      $row = $table->createRow();
      $row->type = $type;
      $row->id = $id;
    }

    if(isset($item->approved)) {
      $row->approved = $item->approved;
    }

    if(isset($item->approved) && is_null($item->approved)) {
      $row->approved = 1;
    }
    
    $row->title = $title;
    $row->description = $description;
    $row->keywords = $keywords;
    $row->hidden = $hiddenText;
    $row->username = isset($username) && $username ? $username : '';
    $row->save();
  }

  public function unindex(Core_Model_Item_Abstract $item)
  {
    $table = Engine_Api::_()->getDbtable('search', 'core');

    $table->delete(array(
      'type = ?' => $item->getType(),
      'id = ?' => $item->getIdentity(),
    ));

    return $this;
  }

  public function getPaginator($text, $type = null)
  {
    return Zend_Paginator::factory($this->getSelect($text, $type));
  }

  public function getSelect($text, $type = null)
  {
    // Build base query
    $table = Engine_Api::_()->getDbtable('search', 'core');
    $db = $table->getAdapter();
    $select = $table->select()
      ->where('approved = ?', 1)
      ->order('search_id DESC');

    $select->where("( title LIKE ? || username LIKE ? || description LIKE ? || keywords LIKE ? || hidden LIKE ?)", $text . '%');
    
    // Filter by item types
    $availableTypes = Engine_Api::_()->getItemTypes();
    if( $type && engine_in_array($type, $availableTypes) ) {
      $select->where('type = ?', $type);
    } else {
      $select->where('type IN(?)', $availableTypes);
    }
    
    return $select;
  }

  public function getAvailableTypes()
  {
    if( null === $this->_types ) {
      $this->_types = Engine_Api::_()->getDbtable('search', 'core')->getAdapter()
        ->query('SELECT DISTINCT `type` FROM `engine4_core_search`')
        ->fetchAll(Zend_Db::FETCH_COLUMN);
      $this->_types = array_intersect($this->_types, Engine_Api::_()->getItemTypes());
    }

    return $this->_types;
  }
}
