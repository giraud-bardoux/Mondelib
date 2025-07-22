<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('album_admin_main', array(), 'album_admin_main_settings');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->view->form = $form = new Album_Form_Admin_Global();
    $form->album_page->setValue($settings->getSetting('album_page', 25));
    $form->album_searchable->setValue($settings->getSetting('album_searchable', 0));
    $form->album_defaultsearch->setValue($settings->getSetting('album_defaultsearch', 0));
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $values = $form->getValues();
      foreach( $values as $key => $value ) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function editCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $category_id = $this->_getParam('id');
    $this->view->album_id = $this->view->category_id = $category_id;
    $categoriesTable = Engine_Api::_()->getDbtable('categories', 'album');
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
    
    $this->view->form = $form = new Album_Form_Admin_Category();
    $form->setAction($this->view->url(array()));
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
        $categoryItem = Engine_Api::_()->getItem('album_category', $_POST['parentcategory_id']);
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

  public function flushPhotoAction() {
    $this->view->form = $form = new Album_Form_Admin_Settings_FlushAlbum();
    $this->_helper->layout->setLayout('admin-simple');
    if( !$this->getRequest()->isPost() )
    {
      return;
    }
    $dbObject = Engine_Db_Table::getDefaultAdapter();
    try {
      $flushData = Engine_Api::_()->album()->getFlushPhotosData();
      foreach($flushData as $photo) {
        $photo->delete();
      }
      //$dbObject->query('DELETE  FROM `engine4_album_photos` WHERE (album_id =0) AND (DATE(NOW()) != DATE(creation_date))');
    }catch(Exception $e){
      throw $e;
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_("Unmapped photos remove successfully.");
    return 
    $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'album', 'controller' => 'settings', 'action' => 'index'), 'admin_default', true),
      'messages' => Array($this->view->message)
    ));
  }
  
  

  public function categoriesAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('album_admin_main', array(), 'album_admin_main_categories');
    Engine_Api::_()->getApi('categories', 'core')->categories(array('module' => 'album'));
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
      $checkTypeCategory = $dbObject->query("SELECT * FROM engine4_album_categories WHERE category_id = " . $category_id)->fetchAll();
      if (isset($checkTypeCategory[0]['subcat_id']) && $checkTypeCategory[0]['subcat_id'] != 0) {
        $categoryType = 'subcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subcat_id'];
      } else if (isset($checkTypeCategory[0]['subsubcat_id']) && $checkTypeCategory[0]['subsubcat_id'] != 0) {
        $categoryType = 'subsubcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subsubcat_id'];
      } else
        $categoryType = 'category_id';
      if ($checkTypeCategory)
        $currentOrder = Engine_Api::_()->getDbtable('categories', 'album')->order($categoryTypeId, $categoryType);
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
      $categoryTable = Engine_Api::_()->getDbtable('categories', 'album');
      for ($i = 0; $i < engine_count($order); $i++) {
        $category_id = $order[$i - $start];
        $categoryTable->update(array('order' => $i), array('category_id = ?' => $category_id));
      }
      $checkCategoryChildrenCondition = $dbObject->query("SELECT * FROM engine4_album_categories WHERE subcat_id = '" . $id . "' || subsubcat_id = '" . $id . "' || subcat_id = '" . $nextid . "' || subsubcat_id = '" . $nextid . "'")->fetchAll();
      if (empty($checkCategoryChildrenCondition)) {
        echo 'done';
        die;
      }
      echo "children";
      die;
    }
  }
}
