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
class Blog_Widget_HashtagSearchResultsController extends Engine_Content_Widget_Abstract {

    public function indexAction() {
        
        $viewer = Engine_Api::_()->user()->getViewer();
        $levelId = ($viewer->getIdentity() ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id);
        if(!Engine_Api::_()->authorization()->getPermission($levelId, 'blog', 'view')) 
          return $this->setNoRender();
          
        $tag = Zend_Controller_Front::getInstance()->getRequest()->getParam('search', null);

        $widgetId = Engine_Api::_()->getDbTable('content', 'core')->widgetId('blog.hashtag-search-results', 'core_hashtag_index');

        $this->view->formValues = array_filter(array('search' => $tag, 'tab' => $widgetId));

        $page = 1;
        if(Zend_Controller_Front::getInstance()->getRequest()->getParam('tab') == $widgetId) {
            $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
        }

        // Get paginator
        $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator(array(
            'orderby' => 'creation_date',
            'draft'  => '0',
            'tag' =>  Engine_Api::_()->getDbTable('tags', 'core')->getTagId($tag),
        ));

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $paginator->setCurrentPageNumber($page);
    }
}
