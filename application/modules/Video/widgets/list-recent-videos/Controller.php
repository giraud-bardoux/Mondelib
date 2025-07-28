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
class Video_Widget_ListRecentVideosController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Should we consider creation or modified recent?
        $recentType = $this->_getParam('recentType', 'creation');
        if( !engine_in_array($recentType, array('creation', 'modified')) ) {
            $recentType = 'creation';
        }
        $this->view->recentType = $recentType;
        $this->view->recentCol = $recentCol = $recentType . '_date';

        // Get paginator
        $table = Engine_Api::_()->getItemTable('video');
        $params['search'] = 1;
        $video = Engine_Api::_()->getApi('core', 'video');
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getItemsSelect($table->select(), $params);
        }else{
            $select = Engine_Api::_()->getDbTable('videos','video')->select();
            $select->where('search =?',1);
        }
        $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        $select->where('feedupload = ?', 0)->where('uploadtype IS NULL');
        $select->where('status = ?', 1);

        if( $recentType == 'creation' ) {
            // using primary should be much faster, so use that for creation
            $select->order('video_id DESC');
        } else {
            $select->order($recentCol . ' DESC');
        }

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
