<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ProfileController.php 10169 2014-04-17 16:29:44Z andres $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_ProfileController extends Core_Controller_Action_Standard
{
    public function init()
    {
        // @todo this may not work with some of the content stuff in here, double-check
        $subject = null;
        if( !Engine_Api::_()->core()->hasSubject() &&
            ($id = $this->_getParam('id')) ) {
            $subject = Engine_Api::_()->getItem('event', $id);
            if( $subject && $subject->getIdentity() ) {
                Engine_Api::_()->core()->setSubject($subject);
            }
        }

        $this->_helper->requireSubject();
        $this->_helper->requireAuth()->setNoForward()->setAuthParams(
            $subject,
            Engine_Api::_()->user()->getViewer(),
            'view'
        );

    }

    public function indexAction()
    {
        $subject = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();

        if( !$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid() ) {
            return;
        }
        // Check block
        if( $viewer->isBlockedBy($subject) )
        {
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Increment view count
        if( !$subject->getOwner()->isSelf($viewer) )
        {
            $subject->view_count++;
            $subject->save();
        }
        
        if($subject->parent_type == 'group' && $subject->parent_id) {
          $group = Engine_Api::_()->getItem($subject->parent_type, $subject->parent_id);
          
          if( !$group || !$group->getIdentity() || ((!$group->approved) && !$group->isOwner($viewer)) ) {
            if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
            } else
              return $this->_forward('requireauth', 'error', 'core');
          }
          $viewPermission = $group->authorization()->isAllowed($viewer, 'view');
          if(empty($viewPermission)) {
            return $this->_forward('requireauth', 'error', 'core');
          }
        }
        
        if( !$subject || !$subject->getIdentity() || ((!$subject->approved) && !$subject->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
        $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($subject, 'user_id');
        if(empty($networkPrivacy))
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Get styles
        $table = Engine_Api::_()->getDbtable('styles', 'core');
        $select = $table->select()
            ->where('type = ?', $subject->getType())
            ->where('id = ?', $subject->getIdentity())
            ->limit();

        $row = $table->fetchRow($select);

        if( null !== $row && !empty($row->style) ) {
            $this->view->headStyle()->appendStyle($row->style);
        }

        // Render
        $this->_helper->content
            ->setNoRender()
            ->setEnabled()
        ;
    }
}
