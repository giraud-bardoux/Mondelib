<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9832 2012-11-28 01:31:18Z richard $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Forum Topics',
    'description' => 'Displays a member\'s forum topics on their profile.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.profile-forum-topics',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Forum Topics',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Profile Forum Posts',
    'description' => 'Displays a member\'s forum posts on their profile.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.profile-forum-posts',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Forum Posts',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Recent Forum Topics',
    'description' => 'Displays recently created forum topics.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.list-recent-topics',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Forum Topics',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Recent Forum Posts',
    'description' => 'Displays recent forum posts.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.list-recent-posts',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Forum Posts',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Forum Browse Search',
    'description' => 'Displays a search form in the forum browse page.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.browse-search',
    'requirements' => array(
      'no-subject',
    ),
  ),
) ?>
