<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Categories.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Model_DbTable_Categories extends Engine_Db_Table
{
  protected $_rowClass = 'Video_Model_Category';
  
  
  public function getCategory($params = array()) {

    if (isset($params['column_name']))
      $column = $params['column_name'];
    else
      $column = '*';

    $tableName = $this->info('name');
    $select = $this->select()
            ->from($tableName, $column)
            ->where($tableName . '.subcat_id = ?', 0)
            ->where($tableName . '.subsubcat_id = ?', 0)
            ->order('order DESC');
    return $this->fetchAll($select);
  }
  
  public function deleteCategory($params = array()) {
    $isValid = false;
    if (!empty($params)) {
      if ($params->subcat_id != 0) {
        $subsubcategory = $this->getSubsubcategory(array('column_name' => '*', 'category_id' => $params->category_id));
        if (engine_count($subsubcategory) > 0)
          $isValid = false;
        else
          $isValid = true;
      }else if ($params->subsubcat_id != 0) {
        $isValid = true;
      } else {
        $subcategory = $this->getSubcategory(array('column_name' => '*', 'category_id' => $params->category_id));
        if (engine_count($subcategory) > 0)
          $isValid = false;
        else
          $isValid = true;
      }
    }
    return $isValid;
  }
  
  public function order($categoryTypeId, $categoryType = 'category_id') {
    $select = $this->select()
            ->from($this->info('name'), 'category_id')
            ->order('order DESC');
    if ($categoryType != 'category_id')
      $currentOrder = $select->where($categoryType . ' = ?', $categoryTypeId);
    else
      $select = $select->where('subcat_id = ?', 0)->where('subsubcat_id = ?', 0);
    return $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
  }
  
  public function orderNext($params = array()) {
    $select = $this->select()
            ->from($this->info('name'), '*')
            ->limit(1)
            ->order('order DESC');
    if (isset($params['category_id'])) {
      $select = $select->where('subcat_id = ?', 0)->where('subsubcat_id = ?', 0);
    } else if (isset($params['subsubcat_id'])) {
      $select = $select->where('subsubcat_id = ?', $params['subsubcat_id']);
    } else if (isset($params['subcat_id'])) {
      $select = $select->where('subcat_id = ?', $params['subcat_id']);
    }
    $select = $this->fetchRow($select);
    if (empty($select))
      $order = 1;
    else
      $order = $select['order'] + 1;
    return $order;
  }
  
  public function getSubcategory($params = array()) {
    $tableName = $this->info('name');
    if(isset($params['column_name'])) {
      $column_name = $params['column_name'];
    } else {
      $column_name = '*';
    }
    $select = $this->select()->from($tableName, $column_name);
    if (isset($params['category_id']))
      $select = $select->where($tableName . '.subcat_id = ?', $params['category_id']);
    $select = $select->order('order DESC');
    return $this->fetchAll($select);
  }

  public function getSubsubcategory($params = array()) {
    $tableName = $this->info('name');
    if(isset($params['column_name'])) {
      $column_name = $params['column_name'];
    } else {
      $column_name = '*';
    }
    $select = $this->select()
                  ->from($this->info('name'), $column_name);
    if (isset($params['category_id']))
      $select = $select->where($tableName . '.subsubcat_id = ?', $params['category_id']);
    $select = $select->order('order DESC');
    return $this->fetchAll($select);
  }
  
  public function getEditCategories($params = array()) {
    $stmt = $this->select()
        ->from($this, array('category_id', 'subcat_id', 'category_name'))
        ->where('subsubcat_id = ?', 0)
        ->order('order DESC');
        
    if(isset($params['category_id']) && !empty($params['category_id'])) {
      $stmt->where('category_id <> ?', $params['category_id']);
    }
    
    $data = array('' => '');
    foreach( $stmt->query()->fetchAll() as $category ) {
      if(empty($category['subcat_id']))
        $data[$category['category_id']] = $category['category_name'];
      else if(!empty($category['subcat_id'])) {
        $data[$category['category_id']] = '-- ' . $category['category_name'];
      }
    }
    return $data;
  }
}
