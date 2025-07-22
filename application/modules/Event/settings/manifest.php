<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Sami
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'event',
    'version' => '7.4.0',
    'revision' => '$Revision: 10271 $',
    'path' => 'application/modules/Event',
    'repository' => 'socialengine.com',
    'title' => 'Events',
    'description' => 'Events are a great way to bring people together. Members can organize community events for online and offline activities. Pick a time and place, invite people, discuss event details in the activity feed, and share content.',
    'author' => '<a href="https://socialengine.com/" style="text-decoration:underline;" target="_blank">SocialEngine</a>',
    'thumb' => 'application/modules/Event/externals/images/thumb.png',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
      'enable',
      'disable',
    ),
    'callback' => array(
      'path' => 'application/modules/Event/settings/install.php',
      'class' => 'Event_Installer',
    ),
    'directories' => array(
      'application/modules/Event',
    ),
    'files' => array(
      'application/languages/en/event.csv',
    ),
  ),
  //Load by default css / js file ---------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
    ),
    'css' => array(
    )
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Event_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Event_Plugin_Core',
    ),
    array(
      'event' => 'getActivity',
      'resource' => 'Event_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Event_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'event',
    'event_album',
    'event_category',
    'event_photo',
    'event_post',
    'event_topic',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'event_extended' => array(
      'route' => 'events/:controller/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'event_general' => array(
      'route' => 'events/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|create|delete|list|manage|edit|subcategory|subsubcategory)',
      )
    ),
    'event_specific' => array(
      'route' => 'events/:action/:event_id/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'event',
        'action' => 'index',
      ),
      'reqs' => array(
        'action' => '(edit|delete|join|leave|invite|accept|style|reject)',
        'event_id' => '\d+',
      )
    ),
    'event_profile' => array(
      'route' => 'event/:id/:slug/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'profile',
        'action' => 'index',
        'slug'=>''
      ),
      'reqs' => array(
        'id' => '\d+',
      )
    ),
    'event_upcoming' => array(
      'route' => 'events/upcoming/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
        'filter' => 'future'
      )
    ),
    'event_past' => array(
      'route' => 'events/past/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
        'filter' => 'past'
      )
    ),
    'event_photo' => array(
      'route' => 'event/:action',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'upload-photo',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
   'event_topic' => array(
      'route' => 'event/:controller/:action/:event_id/:topic_id/:slug/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'topic',
        'action' => 'index',
        'topic_id'=>'',
        'slug' => '',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'event_coverphoto' => array(
      'route' => 'event/coverphoto/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'coverphoto',
      ),
      'reqs' => array(
          'action' => '(get-cover-photo|get-main-photo|reset-cover-photo-position|upload-cover-photo|choose-from-albums|remove-cover-photo)'
      ),
    ),
  )
) ?>
