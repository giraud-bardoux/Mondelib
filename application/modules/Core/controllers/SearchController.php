<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SearchController.php 9906 2013-02-14 02:54:51Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_SearchController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    $searchApi = Engine_Api::_()->getApi('search', 'core');

    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_portal;
    if (!$require_check) {
      if (!$this->_helper->requireUser()->isValid()) {
        return;
      }
    }

    // Prepare form
    $this->view->form = $form = new Core_Form_Search();

    // Get available types
    $availableTypes = $searchApi->getAvailableTypes();
    if (is_array($availableTypes) && engine_count($availableTypes) > 0) {
      $options = array();
      foreach ($availableTypes as $index => $type) {
        $options[$type] = strtoupper('ITEM_TYPE_' . $type);
      }
      $form->type->addMultiOptions($options);
    } else {
      $form->removeElement('type');
    }

    // Check form validity?
    $values = array();
    if ($form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
    }

    $this->view->query = $query = (string) @$values['query'];
    $this->view->type = $type = (string) @$values['type'];
    $this->view->page = $page = (int) $this->_getParam('page');

    //User Recent Search
    if (!empty($user_id) && ($query || $type)) {
      Engine_Api::_()->getApi('recentsearch', 'user')->index($query, $type);
    }

    if ($query) {
      $this->view->paginator = $searchApi->getPaginator($query, $type);
      $this->view->paginator->setCurrentPageNumber($page);
    }

    // Render the page
    $this->_helper->content
      // ->setNoRender()
      ->setEnabled();
  }

  public function addAction()
  {
    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $query = $this->getParam('query', null);
    $type = $this->getParam('type', null);
    $id = $this->getParam('id', null);

    //User Recent Search
    if (!empty($user_id)) {
      Engine_Api::_()->getApi('recentsearch', 'user')->index($query, $type, $id);
    }
    echo json_encode(array('status' => 'true', 'error' => '', 'message' => $this->view->string()->escapeJavascript(Zend_Registry::get('Zend_Translate')->_(''))));
    exit();
  }

  public function removeAction()
  {
    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $query = $this->getParam('query', null);

    //User Recent Search
    if (!empty($user_id)) {
      Engine_Api::_()->getApi('recentsearch', 'user')->unindex($query);
    }
    echo json_encode(array('status' => 'true', 'error' => '', 'message' => $this->view->string()->escapeJavascript(Zend_Registry::get('Zend_Translate')->_('You have successfully removed.'))));
    exit();
  }
  public function globalSearchAction()
  {
    $query = $this->_getParam('text', null);

    $table = Engine_Api::_()->getDbtable('search', 'core');
    $select = $table->select()
          ->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', $query . '%')
          ->where('title <> ?', '')
          ->order('search_id DESC')
          ->limit('10')
          ;
    $results = Zend_Paginator::factory($select);

    foreach ($results as $result) {
      $itemType = $result->type;
      if (Engine_Api::_()->hasItemType($itemType)) {
        $item = Engine_Api::_()->getItem($itemType, $result->id);
        $item_type = ucfirst($item->getShortType());
        $photo_icon_photo = $this->view->itemPhoto($item, 'thumb.icon');
        $data[] = array(
          'search_id' => $result->search_id,
          'label' => strip_tags($item->getTitle()),
          'photo' => $photo_icon_photo,
          'url' => $item->getHref(),
          'resource_type' => $itemType,
          'resource_id' => $item->getIdentity(),
        );
      }
    }
    return $this->_helper->json($data);
  }
}