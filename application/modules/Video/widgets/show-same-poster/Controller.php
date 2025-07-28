<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9995 2013-03-26 00:23:47Z alex $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Widget_ShowSamePosterController extends Engine_Content_Widget_Abstract
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
            $this->getElement()->setTitle('From the same member');
        }

        // Get tags for this video
        $itemTable = Engine_Api::_()->getItemTable($subject->getType());
        $params['search'] = 1;
        $video = Engine_Api::_()->getApi('core', 'video');

        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0)) {
            $select = $video->getItemsSelect($itemTable->select(), $params);
        }else{
            $select = $itemTable->select();
            $select->where('search =?',1);
        }
        $select->where('approved = ?', 1)->where('parent_type = ?', 'user');
        $select->where('feedupload = ?', 0)->where('uploadtype IS NULL');
        $select->where('owner_id = ?', $subject->owner_id)
            ->where('video_id != ?', $subject->getIdentity())
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
