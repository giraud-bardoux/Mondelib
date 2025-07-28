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
class Video_Widget_ShowAlsoLikedController extends Engine_Content_Widget_Abstract
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
            $this->getElement()->setTitle('People Also Liked');
        }

        // Get likes
        $itemTable = Engine_Api::_()->getItemTable($subject->getType());
        $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
        $likesTableName = $likesTable->info('name');
        $params['search'] = 1;
        $video = Engine_Api::_()->getApi('core', 'video');
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
            ->joinLeft($likesTableName, $likesTableName.'.resource_id=video_id', null)
            ->joinLeft($likesTableName . ' as l2', $likesTableName.'.poster_id=l2.poster_id', null)
            ->where($likesTableName . '.poster_type = ?', 'user')
            ->where('l2.poster_type = ?', 'user')
            ->where($likesTableName . '.resource_type = ?', $subject->getType())
            ->where('l2.resource_type = ?', $subject->getType())
            ->where($likesTableName . '.resource_id != ?', $subject->getIdentity())
            ->where('l2.resource_id = ?', $subject->getIdentity())
            ->where('status = ?', 1)
            ->where('video_id != ?', $subject->getIdentity())
            //->order(new Zend_Db_Expr('COUNT(like_id)'))
        ;
        $select = Engine_Api::_()->network()->getNetworkSelect($likesTableName, $select,'poster_id');
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
