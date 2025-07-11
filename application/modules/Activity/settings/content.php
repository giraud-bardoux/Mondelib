<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: content.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
return array(
  array(
    'title' => 'Activity Feed',
    'description' => 'Displays the activity feed.',
    'category' => 'Activity',
    'type' => 'widget',
    'name' => 'activity.feed',
    'defaultParams' => array(
      'title' => 'What\'s New',
    ),
    'autoEdit' => true,
    'adminForm' => 'Activity_Form_Admin_Settings_FeedSettings',
  ),
  array(
    'title' => 'Requests',
    'description' => 'Displays the current logged-in member\'s requests (i.e. friend requests, group invites, etc).',
    'category' => 'Core',
    'type' => 'widget',
    'name' => 'activity.list-requests',
    'defaultParams' => array(
      'title' => 'Requests',
    ),
    'requirements' => array(
      'viewer',
    ),
  ),
  array(
    'title' => 'Memories On This Day Banner',
    'description' => 'Displays the banner on the Memories On This Day page.',
    'category' => 'Activity',
    'type' => 'widget',
    'name' => 'activity.onthisday-banner',
    'defaultParams' => array(
      'title' => '',
    ),
    'autoEdit' => false,
  ),
	array(
    'title' => 'Sell Something Page Widget',
    'description' => 'Displays the selling items on sell page.',
    'category' => 'Activity',
    'type' => 'widget',
    'name' => 'activity.sell-something',
    'defaultParams' => array(
      'title' => '',
    ),
    'autoEdit' => true,
    'adminForm' => array(
      'elements' => array(
        array(
          'Text',
          'limit',
          array(
            'label' => 'How many buy sell content you want to show at a time in this widget? After the count you set below it will show View More button to load more items.',
            'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
            ),
            'value'=>10,
          ),
        ),
      ),
    ),
  ),
  array(
    'title' => 'View Photo / Video Information',
    'description' => 'This widget displays the photo / video information on Activity Attachment View Page. You can place this widget on Activity Attachment View Page in sidebar.',
    'category' => 'Activity',
    'type' => 'widget',
    'name' => 'activity.photo-video-info',
  ),
);
