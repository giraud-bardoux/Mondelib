<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Categories.php 9747 2016-12-15 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Categories extends Core_Api_Abstract
{
  public function getNavigation($module, array $options = array())
  {
    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $activeItem = null;
    if( isset($params['category_id']) ) {
      $activeItem = $params['category_id'];
    } elseif( isset($params['category']) ) {
      $activeItem = $params['category'];
    }
    $pages = $this->getCategoryParams($module, $options, $activeItem);
    $navigation = new Zend_Navigation();
    $navigation->addPages($pages);
    return $navigation;
  }

  public function getCategoryParams($module, array $options = array(), $activeItem = null)
  {
    $category = $this->getCategory($module);
    $pages = array();
    $count = 0;

    foreach( $category as $row ) {
      $page = null;

      // Add label
      $page['label'] = $row->getTitle();

      // Add type for URI
      $page['type'] = 'uri';
      $page['uri'] = $row->getHref();

      // Set page as active, if necessary
      if( null !== $activeItem && $activeItem == $row->category_id ) {
        $page['active'] = true;
      }

      $page['class'] = (!empty($page['class']) ? $page['class'] . ' ' : '' ) . 'category_' . $module;
      $page['class'] .= " category_" . str_replace('-', '_', $row->getSlug());

      // Maintain category item order
      if( isset($row->order) ) {
        $page['order'] = $row->order;
      } else {
        $page['order'] = $count;
        $count++;
      }

      $pages[] = $page;
    }

    return $pages;
  }

  public function getCategory($module)
  {
    $categoriesTable = Engine_Api::_()->getDbtable('categories', $module);
    return $categoriesTable->fetchAll();
  }
  
  public function categories($params = array()) {
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $type = !empty($params['type']) ? $params['type'] : '';
    $module = $params['module'];
    $route = $module.'_category';
    $categoryTable = Engine_Api::_()->getDbtable('categories', $module);
    $controller = !empty($params['controller']) ? $params['controller'] : 'settings';
    
    if (isset($_POST['selectDeleted']) && $_POST['selectDeleted']) {
      if (isset($_POST['data']) && is_array($_POST['data'])) {
        $deleteCategoryIds = array();
        foreach ($_POST['data'] as $key => $valueSelectedcategory) {
          $categoryDelete = Engine_Api::_()->getItem($route, $valueSelectedcategory);
          $deleteCategory = $categoryTable->deleteCategory($categoryDelete, $type);
          if ($deleteCategory) {
            $deleteCategoryIds[] = $categoryDelete->category_id;
            $categoryDelete->delete();
          }
        }
        echo json_encode(array('diff_ids' => array_diff($_POST['data'], $deleteCategoryIds), 'ids' => $deleteCategoryIds));die;
      }
    }
    
    if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
      $value['category_name'] = isset($_POST['category_name']) ? $_POST['category_name'] : '';
      $value['title'] = isset($_POST['title']) ? $_POST['title'] : '';
      $value['parent'] = $cat_id = isset($_POST['parent']) ? $_POST['parent'] : '';
      if ($cat_id != -1) {
        $categoryData = Engine_Api::_()->getItem($route, $cat_id);
        if ($categoryData->subcat_id == 0) {
          $value['subcat_id'] = $cat_id;
          $seprator = '&nbsp;&nbsp;&nbsp;';
          $tableSeprator = '-&nbsp;';
          $parentId = $cat_id;
          $value['order'] = $categoryTable->orderNext(array('subcat_id' => $cat_id, 'type' => $type));
        } else {
          $value['subsubcat_id'] = $cat_id;
          $seprator = '3';
          $tableSeprator = '--&nbsp;';
          $value['order'] = $categoryTable->orderNext(array('subsubcat_id' => $cat_id, 'type' => $type));
          $parentId = $cat_id;
        }
      } else {
        $parentId = 0;
        $seprator = '';
        $value['order'] = $categoryTable->orderNext(array('category_id' => true, 'type' => $type));
        $tableSeprator = '';
      }
      $value['user_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
      $value['type'] = $type;
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $category = $categoryTable->createRow();
        $category->setFromArray($value);
        $category->save();
        $category->order = $category->getIdentity();
        $category->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      
      if(isset($category->category_name) && !empty($category->category_name)) {
				$category_name = $category->category_name;
      } else if(isset($category->title) && !empty($category->title)) {
				$category_name = $category->title;
      }
        
      $tableData = '<tr id="categoryid-' . $category->category_id . '"><td><input type="checkbox" name="delete_tag[]" class="checkbox" value="' . $category->getIdentity() . '" /></td><td>' . $tableSeprator . $category_name . ' <div class="hidden" style="display:none" id="inline_' . $category->category_id . '"><div class="parent">' . $parentId . '</div></div></td><td>' . $view->htmlLink(array("route" => "admin_default", "module" => $module, "controller" => $controller, "action" => "edit-category", "id" => $category->category_id, "catparam" => "subsub", "type" => $type), $view->translate("Edit"), array('class' => 'openSmoothbox')) . ' | ' . $view->htmlLink('javascript:void(0);', $view->translate("Delete"), array("class" => "deleteCat", "data-url" => $category->category_id)) . '</td></tr>';
      echo json_encode(array('seprator' => $seprator, 'tableData' => $tableData, 'id' => $category->category_id, 'name' => $category_name));
      die;
    }
    $view->categories = $categoryTable->getCategory(array('column_name' => '*', 'type' => $type));
  }
}
