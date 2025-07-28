<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Widget_ListPopularVideosController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider views or comments popular?
        $this->view->popularType = $popularType = $this->_getParam('popularType', 'view_count');

        // Get paginator
        $table = Engine_Api::_()->getItemTable('video');
        $video = Engine_Api::_()->getApi('core', 'video');
        $params['search'] = 1;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getItemsSelect($table->select(), $params);
        }else{
            $select = Engine_Api::_()->getDbTable('videos','video')->select();
            $select->where('search =?',1);
        }
        $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        $select->where('feedupload = ?', 0)->where('uploadtype IS NULL');
        $select->where('status = ?', 1)
            ->order($popularType . ' DESC');

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select);
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getAuthorisedSelect($select);
        }
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Hide if nothing to show
        if( $paginator->getTotalItemCount() <= 0 ) {
            return $this->setNoRender();
        }
    }
}
