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
class Blog_Widget_ListPopularBlogsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider views or comments popular?
        $this->view->popularType = $popularType = $this->_getParam('popularType', 'view_count');
        $params = array('search' => true);

        // Get paginator
        $table = Engine_Api::_()->getItemTable('blog');
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.allow.unauthorized', 0)) {
            $select = $table->getItemsSelect($params);
        }else{
            $select = $table->select();
            $select->where('search =?',1);
        }
        $select->where('draft = ?', 0)
            ->where('approved = ?', 1)
            ->where('parent_type = ?', 'user')
            ->order($popularType . ' DESC');

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select);
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.allow.unauthorized', 0)) {
            $select = $table->getAuthorisedSelect($select);
        }else{
            $select = $table->select();
        }
        
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
