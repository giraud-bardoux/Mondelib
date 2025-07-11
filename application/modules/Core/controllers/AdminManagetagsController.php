<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminManagetagsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->formFilter = $formFilter = new Core_Form_Admin_Managetags_Filter();
    $page = $this->_getParam('page', 1);

    $tagmap = Engine_Api::_()->getDbTable('tagMaps', 'core');
    $tagmapName = $tagmap->info('name');
    
    $tableTag = Engine_Api::_()->getDbTable('tags', 'core');
    $tableTagName = $tableTag->info('name');
    
    $select = $tableTag->select()
            ->from($tableTagName);

    // Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'tagmap_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    if(@$values['text'] || @$values['resource_type']) {
      $select->setIntegrityCheck(false)
            ->where($tagmapName.'.tag_type =?', 'core_tag')
            ->join($tagmapName, $tagmapName . '.tag_id=' . $tableTagName . '.tag_id')
            ->group($tagmapName . '.tag_id');
    }

    if (!empty($values['text']))
      $select->where($tableTagName . ".text LIKE ?", $values['text'] . '%');
      
    if(!empty($values['resource_type']))
      $select->where($tagmapName . ".resource_type LIKE ?", $values['resource_type'] . '%');
      
    if( !empty($_GET['tag_id']) ) {
      $select->where($tableTagName.'.tag_id = ?', (int) $_GET['tag_id']);
    }

    $select->order($tableTagName.'.tag_id DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;
  }
  
  public function addAction() {

    $this->_helper->layout->setLayout('admin-simple');

    //Generate and assign form
    $this->view->form = $form = new Core_Form_Admin_Managetags_Add();
    $form->setTitle('Add New Tags');
    $form->text->setLabel('Tag Name');
    $form->text->setDescription('Enter tags seprate by comma.');

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();

      $table = Engine_Api::_()->getDbTable('tags', 'core');
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $texts = preg_split('/[,]+/', $values['text']);
        foreach($texts as $text) {
          if(empty($text)) continue;
          $isExist = $table->isExist(array('text' => $text));
          if(empty($isExist)) {
            $values['text'] = $text;
            $row = $table->createRow();
            $row->setFromArray($values);
            $row->save();
          }
        }
        $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully added tag.')
      ));
    }
  }
  
  public function editAction() {

    $this->_helper->layout->setLayout('admin-simple');
		
		$id = $this->_getParam('id', null);
    $tag = Engine_Api::_()->getItem('core_tag', (int) $id);
    
    //Generate and assign form
    $this->view->form = $form = new Core_Form_Admin_Managetags_Edit();
    $form->setTitle('Edit Tags');
    $form->text->setLabel('Tag Name');
    
    $form->populate($tag->toArray());
    //$form->text->setDescription('Enter tags seprate by comma.');

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
				$tag->text = $values['text'];
				$tag->save();
        $db->commit();
      } catch (Exception $e) {
				$db->rollBack();
				throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully edited tag.')
      ));
    }
  }

  public function multiModifyAction() {
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      
      foreach ($values as $key=>$value) {
        if( $key == 'modify_' . $value ) {
          $tag = Engine_Api::_()->getItem('core_tag', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $tag->delete();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $tag = Engine_Api::_()->getItem('core_tag', (int) $id);
    $this->view->form = $form = new Core_Form_Admin_Managetags_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('tags', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $tag->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This tag has been successfully deleted.')
      ));
    }
  }
}
