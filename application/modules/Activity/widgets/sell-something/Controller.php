<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Widget_SellSomethingController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    if (isset($_POST['params']))
      $params = json_decode($_POST['params'], true);
     
    $this->view->viewmore = $this->_getParam('viewmore', 0);

    if ($this->view->viewmore)
      $this->getElement()->removeDecorator('Container');
      
    $identity = isset($_GET['identity']) ? $_GET['identity'] : (isset($params['identity']) ? $params['identity'] : $this->view->identity);
    
    $limit = isset($_GET['limit']) ? $_GET['limit'] : (isset($params['limit']) ? $params['limit'] : $this->_getParam('limit', 10));

    $this->view->all_params = $values = array('identity' => $identity, 'limit' => $limit);
    
    $paginator = Engine_Api::_()->getDbTable('buysells', 'activity')->getSellPaginator($values);
    $paginator->setItemCountPerPage($limit);
    $this->view->paginator = $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $this->view->count = $paginator->getTotalItemCount();

  }
}
