<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Widget_LandingPageBlogsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider views or comments popular?
        $popularType = $this->_getParam('popularType', 'view');
        if( !engine_in_array($popularType, array('comment', 'view')) ) {
            $popularType = 'view';
        }
        $this->view->popularType = $popularType;
        $this->view->popularCol = $popularCol = $popularType . '_count';
        $params = array('search' => true);

        // Get paginator
        $table = Engine_Api::_()->getItemTable('blog');
        $select = $table->getItemsSelect($params);
        $select->where('approved = ?', 1)
            ->where('parent_type = ?', 'user')
            ->where('draft = ?', 0)
            ->order($popularCol . ' DESC');

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select);
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized', 0))
            $select = $table->getAuthorisedSelect($select);
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 3));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Hide if nothing to show
        if( $paginator->getTotalItemCount() <= 0 ) {
            return $this->setNoRender();
        }
    }
}
