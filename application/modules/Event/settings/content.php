<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 * 
 */
return array(
  array(
    'title' => 'Upcoming Events',
    'description' => 'Displays the logged-in member\'s upcoming events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.home-upcoming',
    'isPaginated' => true,
    'requirements' => array(
      'viewer',
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Text',
          'title',
          array(
            'label' => 'Title'
          )
        ),
        array(
          'Radio',
          'type',
          array(
            'label' => 'Show',
            'multiOptions' => array(
              '1' => 'Any upcoming events.',
              '2' => 'Current member\'s upcoming events.',
              '0' => 'Any upcoming events when member is logged out, that member\'s events when logged in.',
            ),
            'value' => '0',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Profile Events',
    'description' => 'Displays a member\'s events on their profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Events',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Event Profile Discussions',
    'description' => 'Displays a event\'s discussions on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-discussions',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Discussions',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Cover Photo',
    'description' => 'Displays a event\'s cover photo on its profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.cover-photo',
    'requirements' => array(
      'viewer',
    ),
  ),
  array(
    'title' => 'Event Profile Info',
    'description' => 'Displays a event\'s info (creation date, member count, etc) on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-info',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Members',
    'description' => 'Displays a event\'s members on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-members',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Guests',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Options',
    'description' => 'Displays a menu of actions (edit, report, join, invite, etc) that can be performed on a event on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-options',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Photo',
    'description' => 'Displays a event\'s photo on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-photo',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Photos',
    'description' => 'Displays a event\'s photos on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-photos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Photos',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile RSVP',
    'description' => 'Displays options for RSVP\'ing to an event on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-rsvp',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Status',
    'description' => 'Displays a event\'s title on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-status',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Popular Events',
    'description' => 'Displays a list of most viewed events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.list-popular-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Events',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Select',
          'popularType',
          array(
            'label' => 'Popular Type',
            'multiOptions' => array(
              'creation_date' => 'Recently Created',
              'modified_date' => 'Recently Modified',
              'like_count' => 'Most Liked',
              'view_count' => 'Most Viewed',
              'comment_count' => 'Most Commented',
              'member_count' => 'Members',
            ),
            'value' => 'view_count',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Events',
    'description' => 'Displays a list of recently created events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.list-recent-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Events',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Radio',
          'recentType',
          array(
            'label' => 'Recent Type',
            'multiOptions' => array(
              'creation' => 'Creation Date',
              'modified' => 'Modified Date',
              'start' => 'Started',
              'end' => 'Ended',
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
  
  array(
    'title' => 'Event Browse Search',
    'description' => 'Displays a search form in the event browse page.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.browse-search',
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Event Browse Menu',
    'description' => 'Displays a menu in the event browse page.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.browse-menu',
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Event Browse Quick Menu',
    'description' => 'Displays a small menu in the event browse page.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.browse-menu-quick',
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Event Categories',
    'description' => 'Display a list of categories for events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.list-categories',
  ),
	array(
		'title' => 'Breadcrumb for Event View Page',
		'description' => 'Displays Breadcrumb for Event view page.',
		'category' => 'Events',
		'type' => 'widget',
		'name' => 'event.breadcrumb',
  ),
) ?>
