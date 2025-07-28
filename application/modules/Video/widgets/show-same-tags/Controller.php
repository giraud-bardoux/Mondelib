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
class Video_Widget_ShowSameTagsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Check subject
        if( !Engine_Api::_()->core()->hasSubject('video') ) {
            return $this->setNoRender();
        }
        $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('video');

        // Set default title
        if( !$this->getElement()->getTitle() ) {
            $this->getElement()->setTitle('Similar Videos');
        }

        // Get tags for this video
        $itemTable = Engine_Api::_()->getItemTable($subject->getType());
        $tagMapsTable = Engine_Api::_()->getDbtable('tagMaps', 'core');
        $tagsTable = Engine_Api::_()->getDbtable('tags', 'core');

        // Get tags
        $tags = $tagMapsTable->select()
            ->from($tagMapsTable, 'tag_id')
            ->where('resource_type = ?', $subject->getType())
            ->where('resource_id = ?', $subject->getIdentity())
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

        // No tags
        if( empty($tags) ) {
            return $this->setNoRender();
        }

        $video = Engine_Api::_()->getApi('core', 'video');
        $params['search'] = 1;
        // Get other with same tag
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getItemsSelect($itemTable->select(), $params);
            $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        }else{
            $select = Engine_Api::_()->getDbTable('videos','video')->select();
            $select->where('search =?',1);
            $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        }
        $select->where('feedupload = ?', 0)->where('uploadtype IS NULL');
        $select->distinct(true)
            ->from($itemTable)
            ->joinLeft($tagMapsTable->info('name'), 'resource_id=video_id', null)
            ->where('resource_type = ?', $subject->getType())
            ->where('resource_id != ?', $subject->getIdentity())
            ->where('tag_id IN(?)', $tags)
            ->where('status = ?', 1);

        $select = Engine_Api::_()->network()->getNetworkSelect($itemTable->info('name'), $select);
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getAuthorisedSelect($select);
        }
        // Get paginator
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
