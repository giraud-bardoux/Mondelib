<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Widget_ListPopularEventsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider views or comments popular?
        $this->view->popularType = $popularType = $this->_getParam('popularType', 'view_count');
        $params = array('search' => true);

        // Get paginator
        $table = Engine_Api::_()->getItemTable('event');
        $select = $table->getItemsSelect($params)
            ->order($popularType . ' DESC');
        $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select,'user_id');
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized', 0))
            $select = $table->getAuthorisedSelect($select);
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Hide if nothing to show
        if( $paginator->getTotalItemCount() <= 0 ) {
            return $this->setNoRender();
        }
    }
}
