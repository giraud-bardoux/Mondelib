<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_admin_main', array(), 'poll_admin_main_settings');

    $this->view->form = $form = new Poll_Form_Admin_Settings_Global();

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $form->populate($settings->poll);

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      $settings->poll = $values;
      $settings->poll_allow_unauthorized = $values['poll_allow_unauthorized'];
      $settings->poll_enable_rating = $values['poll_enable_rating'];
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }

    $form->addNotice('Your changes have been saved.');
  }

  public function levelAction()
  {
    // Make navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_admin_main', array(), 'poll_admin_main_level');

    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;

    // Make form
    $this->view->form = $form = new Poll_Form_Admin_Settings_Level(array(
      'public' => ( engine_in_array($level->type, array('public')) ),
      'moderator' => ( engine_in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($id);

    // Populate values
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('poll', $id, array_keys($form->getValues())));

    // Check post
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process

    $values = $form->getValues();

    // Form elements with NonBoolean values
    $nonBooleanSettings = $form->nonBooleanFields();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try {
      // Set permissions
      $permissionsTable->setAllowed('poll', $id, $values, '', $nonBooleanSettings);

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $form->addNotice('Your changes have been saved.');
  }
  

  public function editCategoryAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $category_id = $this->_getParam('id');
    $this->view->poll_id = $this->view->category_id = $category_id;
    $categoriesTable = Engine_Api::_()->getDbtable('categories', 'poll');
    $category = $categoriesTable->find($category_id)->current();
    
    if( !$category ) {
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh'=> 10,
        'messages' => array('')
      ));
    } else {
      $category_id = $category->getIdentity();
    }
    
    $form = $this->view->form = new Poll_Form_Admin_Category();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));
    $form->setField($category);
    
    if( !$this->getRequest()->isPost() ) {
      // Output
      $this->renderScript('admin-settings/form.tpl');
      return;
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      // Output
      $this->renderScript('admin-settings/form.tpl');
      return;
    }
    
    // Process
    $values = $form->getValues();

    $db = $categoriesTable->getAdapter();
    $db->beginTransaction();
    
    try {
      $category->category_name = $values['label'];
      $category->save();
      
      if(isset($_POST['parentcategory_id']) && !empty($_POST['parentcategory_id'])) {
        $categoryItem = Engine_Api::_()->getItem('poll_category', $_POST['parentcategory_id']);
        if(!empty($categoryItem->subcat_id)) {
          $category->subcat_id = 0;
          $category->subsubcat_id = $_POST['parentcategory_id'];
          $category->save();
        } else if(empty($categoryItem->subcat_id)) {
          $category->subcat_id = $_POST['parentcategory_id'];
          $category->subsubcat_id = 0;
          $category->save();
        } 
      } else if($_POST['parentcategory_id'] == '') {
        $category->subcat_id = 0;
        $category->subsubcat_id = 0;
        $category->save();
      }
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('')
    ));
  }

  public function categoriesAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('poll_admin_main', array(), 'poll_admin_main_categories');
    
    $categoryTable = Engine_Api::_()->getDbtable('categories', 'poll');
    
    if (isset($_POST['selectDeleted']) && $_POST['selectDeleted']) {
      if (isset($_POST['data']) && is_array($_POST['data'])) {
        $deleteCategoryIds = array();
        foreach ($_POST['data'] as $key => $valueSelectedcategory) {
          $categoryDelete = Engine_Api::_()->getItem('poll_category', $valueSelectedcategory);
          $deleteCategory = $categoryTable->deleteCategory($categoryDelete);
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
      $value['parent'] = $cat_id = isset($_POST['parent']) ? $_POST['parent'] : '';
      if ($cat_id != -1) {
        $categoryData = Engine_Api::_()->getItem('poll_category', $cat_id);
        if ($categoryData->subcat_id == 0) {
          $value['subcat_id'] = $cat_id;
          $seprator = '&nbsp;&nbsp;&nbsp;';
          $tableSeprator = '-&nbsp;';
          $parentId = $cat_id;
          $value['order'] = $categoryTable->orderNext(array('subcat_id' => $cat_id));
        } else {
          $value['subsubcat_id'] = $cat_id;
          $seprator = '3';
          $tableSeprator = '--&nbsp;';
          $value['order'] = $categoryTable->orderNext(array('subsubcat_id' => $cat_id));
          $parentId = $cat_id;
        }
      } else {
        $parentId = 0;
        $seprator = '';
        $value['order'] = $categoryTable->orderNext(array('category_id' => true));
        $tableSeprator = '';
      }
      $value['user_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
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

      $tableData = '<tr id="categoryid-' . $category->category_id . '"><td><input type="checkbox" name="delete_tag[]" class="checkbox" value="' . $parentId . '" /></td><td>' . $tableSeprator . $category->category_name . ' <div class="hidden" style="display:none" id="inline_' . $category->category_id . '"><div class="parent">' . $parentId . '</div></div></td><td>' . $this->view->htmlLink(array("route" => "admin_default", "module" => "poll", "controller" => "settings", "action" => "edit-category", "id" => $category->category_id, "catparam" => "subsub"), $this->view->translate("Edit"), array('class' => 'openSmoothbox')) . ' | ' . $this->view->htmlLink('javascript:void(0);', $this->view->translate("Delete"), array("class" => "deleteCat", "data-url" => $category->category_id)) . '</td></tr>';
      echo json_encode(array('seprator' => $seprator, 'tableData' => $tableData, 'id' => $category->category_id, 'name' => $category->category_name));
      die;
    }
    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'poll')->getCategory(array('column_name' => '*'));
  }
  
  public function changeOrderAction() {

    if ($this->_getParam('id', false) || $this->_getParam('nextid', false)) {
      $id = $this->_getParam('id', false);
      $order = $this->_getParam('categoryorder', false);
      $order = explode(',', $order);
      $nextid = $this->_getParam('nextid', false);
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      if ($id) {
        $category_id = $id;
      } else if ($nextid) {
        $category_id = $id;
      }
      $categoryTypeId = '';
      $checkTypeCategory = $dbObject->query("SELECT * FROM engine4_poll_categories WHERE category_id = " . $category_id)->fetchAll();
      if (isset($checkTypeCategory[0]['subcat_id']) && $checkTypeCategory[0]['subcat_id'] != 0) {
        $categoryType = 'subcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subcat_id'];
      } else if (isset($checkTypeCategory[0]['subsubcat_id']) && $checkTypeCategory[0]['subsubcat_id'] != 0) {
        $categoryType = 'subsubcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subsubcat_id'];
      } else
        $categoryType = 'category_id';
      if ($checkTypeCategory)
        $currentOrder = Engine_Api::_()->getDbtable('categories', 'poll')->order($categoryTypeId, $categoryType);
      // Find the starting point?
      $start = null;
      $end = null;
      $order = array_reverse(array_values(array_intersect($order, $currentOrder)));
      for ($i = 0, $l = engine_count($currentOrder); $i < $l; $i++) {
        if (engine_in_array($currentOrder[$i], $order)) {
          $start = $i;
          $end = $i + engine_count($order);
          break;
        }
      }
      if (null === $start || null === $end) {
        echo "false";
        die;
      }
      $categoryTable = Engine_Api::_()->getDbtable('categories', 'poll');
      for ($i = 0; $i < engine_count($order); $i++) {
        $category_id = $order[$i - $start];
        $categoryTable->update(array('order' => $i), array('category_id = ?' => $category_id));
      }
      $checkCategoryChildrenCondition = $dbObject->query("SELECT * FROM engine4_poll_categories WHERE subcat_id = '" . $id . "' || subsubcat_id = '" . $id . "' || subcat_id = '" . $nextid . "' || subsubcat_id = '" . $nextid . "'")->fetchAll();
      if (empty($checkCategoryChildrenCondition)) {
        echo 'done';
        die;
      }
      echo "children";
      die;
    }
  }
}
