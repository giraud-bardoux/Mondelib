<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Category.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Model_Category extends Core_Model_Category
{
  // Properties
  protected $_route = 'video_general';
  protected $_searchTriggers = false;

  // General
  public function getTitle()
  {
    return $this->category_name;
  }

  public function getTable()
  {
    if( is_null($this->_table) )
    {
      $this->_table = Engine_Api::_()->getDbtable('categories', 'video');
    }

    return $this->_table;
  }

  public function getUsedCount(){
    $table  = Engine_Api::_()->getDbTable('videos', 'video');
    $rName = $table->info('name');
    $select = $table->select()
                    ->from($rName)
                    ->where($rName.'.category_id = ?', $this->category_id);
    $row = $table->fetchAll($select);
    $total = engine_count($row);
    return $total;
  }

  public function getHref($params = array())
  {
    return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble($params, $this->_route, true) . '?category=' . $this->category_id;
  }
}
