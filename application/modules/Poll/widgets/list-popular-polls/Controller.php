<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Widget_ListPopularPollsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider views or comments popular?
        $this->view->popularType = $popularType = $this->_getParam('popularType', 'vote_count');
        $params = array('search' => true);

        // Get paginator
        $table = Engine_Api::_()->getItemTable('poll');
        $select = $table->getItemsSelect($params);
        $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        $select->order($popularType . ' DESC');

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select, 'user_id');

        $authorisedSelect = $table->getAuthorisedSelect($select);
        $this->view->paginator = $paginator = Zend_Paginator::factory($authorisedSelect);

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Hide if nothing to show
        if( $paginator->getTotalItemCount() <= 0 ) {
            return $this->setNoRender();
        }
    }
}
