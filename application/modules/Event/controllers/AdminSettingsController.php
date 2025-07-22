<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9802 2012-10-20 16:56:13Z pamela $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('event_admin_main', array(), 'event_admin_main_settings');

    $this->view->form = $form = new Event_Form_Admin_Global();

    if( $this->getRequest()->isPost()&& $form->isValid($this->getRequest()->getPost()))
    {
      $values = $form->getValues();
      foreach ($values as $key => $value){
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function levelAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('event_admin_main', array(), 'event_admin_main_level');

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
    $this->view->form = $form = new Event_Form_Admin_Settings_Level(array(
      'public' => ( engine_in_array($level->type, array('public')) ),
      'moderator' => ( engine_in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($level_id);

    if (isset($form->coverphoto_dummy)) {
      $eventId = Engine_Api::_()->getItemTable('event')->select()->query()->fetchColumn();
      if (empty($eventId)) {
        $description = '<div class="tip" style="margin-top:-9px"><span>Please create atleast one event first and then set the default cover photo.</div>';
      } else {
        $href = Engine_Api::_()->getItem('event', $eventId)->getHref() . '?uploadDefaultCover=1&level_id='.$level_id;
        $description = sprintf(
          "%1sClick here%2s to upload and set default cover photo for events",
          "<a href='$href' target='_blank'>", "</a>"
        );
      }
      $form->coverphoto_dummy->setDescription($description);
    }

    if(!empty( $eventCover = Engine_Api::_()->getApi("settings", "core")->getSetting("eventcoverphoto.preview.level.id.$id"))) {
      $image = Engine_Api::_()->storage()->get($eventCover, 'thumb.cover')->map();
      $description = sprintf("%1sPreview Default Cover Photo%2s",
        "<a onclick='showPreview();'>",
        "</a><div id='show_default_preview' class='is_hidden'>"
          . "<img src='$image' style='max-height:600px;max-width:600px;'></div>"
      );
      $form->addElement('dummy', 'coverphoto_preview', array(
        'description' => $description,
      ));

      $form->coverphoto_preview->addDecorator(
        'Description',
        ['placement' => 'PREPEND', 'class' => 'description', 'escape' => false]
      );
    }

    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('event', $level_id, array_keys($form->getValues())));

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
      if( $level->type != 'public' ) {
        // Set permissions
        $values['auth_comment'] = (array) $values['auth_comment'];
        $values['auth_photo'] = (array) $values['auth_photo'];
        $values['auth_view'] = (array) $values['auth_view'];
      }

      // coverphoto work
      unset($values['coverphoto_dummy']);
      unset($values['coverphoto_preview']);
      $permissionsTable->setAllowed('event', $level_id, $values, '', $nonBooleanSettings);

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }

  public function editCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->event_id = $id;
    $categoryTable = Engine_Api::_()->getDbtable('categories', 'event');
    $category = $categoryTable->find($id)->current();

    // Generate and assign form
    $form = $this->view->form = new Event_Form_Admin_Category();
    $form->setAction($this->view->url());
    $form->setField($category);

    // Check post
    if( !$this->getRequest()->isPost() ) {
      $this->renderScript('admin-settings/form.tpl');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->renderScript('admin-settings/form.tpl');
      return;
    }

    // Ok, we're good to add field
    $values = $form->getValues();

    $db = $categoryTable->getAdapter();
    $db->beginTransaction();

    try {
      $category->title = $values['label'];
      $category->save();
      
      if(isset($_POST['parentcategory_id']) && !empty($_POST['parentcategory_id'])) {
        $categoryItem = Engine_Api::_()->getItem('event_category', $_POST['parentcategory_id']);
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
      'parentRefresh' => 10,
      'messages' => array('')
    ));
  }
  

  public function categoriesAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('event_admin_main', array(), 'event_admin_main_categories');
    Engine_Api::_()->getApi('categories', 'core')->categories(array('module' => 'event'));
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
      $checkTypeCategory = $dbObject->query("SELECT * FROM engine4_event_categories WHERE category_id = " . $category_id)->fetchAll();
      if (isset($checkTypeCategory[0]['subcat_id']) && $checkTypeCategory[0]['subcat_id'] != 0) {
        $categoryType = 'subcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subcat_id'];
      } else if (isset($checkTypeCategory[0]['subsubcat_id']) && $checkTypeCategory[0]['subsubcat_id'] != 0) {
        $categoryType = 'subsubcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subsubcat_id'];
      } else
        $categoryType = 'category_id';
      if ($checkTypeCategory)
        $currentOrder = Engine_Api::_()->getDbtable('categories', 'event')->order($categoryTypeId, $categoryType);
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
      $categoryTable = Engine_Api::_()->getDbtable('categories', 'event');
      for ($i = 0; $i < engine_count($order); $i++) {
        $category_id = $order[$i - $start];
        $categoryTable->update(array('order' => $i), array('category_id = ?' => $category_id));
      }
      $checkCategoryChildrenCondition = $dbObject->query("SELECT * FROM engine4_event_categories WHERE subcat_id = '" . $id . "' || subsubcat_id = '" . $id . "' || subcat_id = '" . $nextid . "' || subsubcat_id = '" . $nextid . "'")->fetchAll();
      if (empty($checkCategoryChildrenCondition)) {
        echo 'done';
        die;
      }
      echo "children";
      die;
    }
  }
}
