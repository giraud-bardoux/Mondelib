<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Plugin_Menus
{
  public function canCreateEvents()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create events
    if( !Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewEvents()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    // Must be able to view events
    if( !Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_EventMainManage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$viewer->getIdentity() ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_EventMainCreate()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$viewer->getIdentity() ) {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('event', null, 'create') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_EventProfileEdit()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    return array(
      'label' => 'Edit Event Details',
      'class' => 'icon_event_edit',
      'route' => 'event_specific',
      'params' => array(
        'controller' => 'event',
        'action' => 'edit',
        'event_id' => $subject->getIdentity(),
        'ref' => 'profile'
      )
    );
  }

  public function onMenuInitialize_EventProfileStyle()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'style') ) {
      return false;
    }

    return array(
      'label' => 'Edit Event Style',
      'class' => 'smoothbox icon_style',
      'route' => 'event_specific',
      'params' => array(
        'action' => 'style',
        'event_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      )
    );
  }

  public function onMenuInitialize_EventProfileMember()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() ) {
      return false;
    }

    $row = $subject->membership()->getRow($viewer);

    // Not yet associated at all
    if( null === $row ) {
      if( $subject->membership()->isResourceApprovalRequired() ) {
        return array(
          'label' => 'Request Invite',
          'class' => 'smoothbox icon_invite',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'event_id' => $subject->getIdentity(),
          ),
        );
      } else {
        return array(
          'label' => 'Join Event',
          'class' => 'smoothbox icon_event_join',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'event_id' => $subject->getIdentity()
          ),
        );
      }
    } elseif( $row->active ) {
      if( !$subject->isOwner($viewer) ) {
        return array(
          'label' => 'Leave Event',
          'class' => 'smoothbox icon_event_leave',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'event_id' => $subject->getIdentity()
          ),
        );
      } else {
        return false;
      }
    } elseif( !$row->resource_approved && $row->user_approved ) {
      return array(
        'label' => 'Cancel Invite Request',
        'class' => 'smoothbox icon_event_reject',
        'route' => 'event_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'event_id' => $subject->getIdentity()
        ),
      );
    } elseif( !$row->user_approved && $row->resource_approved ) {
      return array(
        array(
          'label' => 'Accept Event Invite',
          'class' => 'smoothbox icon_event_accept',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'event_id' => $subject->getIdentity()
          ),
        ), array(
          'label' => 'Ignore Event Invite',
          'class' => 'smoothbox icon_event_reject',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'event_id' => $subject->getIdentity()
          ),
        )
      );
    } else {
      throw new Event_Model_Exception('An error has occurred.');
    }


    return false;
  }

  public function onMenuInitialize_EventProfileReport()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('This event does not exist.');
    }
    
    if( !$viewer->getIdentity() ||
        $subject->isOwner($viewer) ) {
      return false;
    } else {
      return array(
        'label' => 'Report',
        'class' => 'smoothbox icon_report',
        'route' => 'default',
        'params' => array(
          'module' => 'core',
          'controller' => 'report',
          'action' => 'create',
          'subject' => $subject->getGuid(),
          'format' => 'smoothbox',
        ),
      );
    }
  }

  public function onMenuInitialize_EventProfileInvite()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('This event does not exist.');
    }
    if( !$subject->authorization()->isAllowed($viewer, 'invite') ) {
      return false;
    }

    return array(
      'label' => 'Invite Guests',
      'class' => 'smoothbox icon_invite',
      'route' => 'event_extended',
      'params' => array(
        //'module' => 'event',
        'controller' => 'member',
        'action' => 'invite',
        'event_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_EventProfileShare()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('This event does not exist.');
    }

    if( !$viewer->getIdentity() ) {
      return false;
    }

    return array(
      'label' => 'Share This Event',
      'class' => 'smoothbox icon_share',
      'route' => 'default',
      'params' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'share',
        'type' => $subject->getType(),
        'id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_EventProfileMessage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('This event does not exist.');
    }

    if( !$viewer->getIdentity() || !$subject->isOwner($viewer) ) {
      return false;
    }

    return array(
      'label' => 'Message Members',
      'class' => 'icon_message',
      'route' => 'messages_general',
      'params' => array(
        'action' => 'compose',
        'to' => $subject->getIdentity(),
        'multi' => 'event'
      )
    );
  }

  public function onMenuInitialize_EventProfileDelete()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' ) {
      throw new Event_Model_Exception('This event does not exist.');
    } elseif( !$subject->authorization()->isAllowed($viewer, 'delete') ) {
      return false;
    }

    return array(
      'label' => 'Delete Event',
      'class' => 'smoothbox icon_event_delete',
      'route' => 'event_specific',
      'params' => array(
        'action' => 'delete',
        'event_id' => $subject->getIdentity(),
      //'format' => 'smoothbox',
      ),
    );
  }
}
