<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9791 2012-09-28 20:41:41Z pamela $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Widget_ListRecentAlbumsController extends Engine_Content_Widget_Abstract
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
        $params = array('search' => true);

        // Get paginator
        $table = Engine_Api::_()->getItemTable('album');
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('album.allow.unauthorized', 0))
            $select = $table->getItemsSelect($params);
        else
            $select = $table->select()->where('search = ?',1);
        $select->where('approved =?',1);
        if( $recentType == 'creation' ) {
            // using primary should be much faster, so use that for creation
            $select->order('album_id DESC');
        } else {
            $select->order($recentCol . ' DESC');
        }

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select);
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('album.allow.unauthorized', 0))
            $select = $table->getAuthorisedSelect($select);
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Do not render if nothing to show
        if( $paginator->getTotalItemCount() <= 0 ) {
            return $this->setNoRender();
        }
    }
}
