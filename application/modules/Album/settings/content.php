<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Breadcrumb for Album View Page',
    'description' => 'Displays Breadcrumb for Album view page.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.breadcrumb-album',
  ),
  array(
    'title' => 'Breadcrumb for Photo View Page',
    'description' => 'Displays Breadcrumb for Photo view page.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.breadcrumb-photo',
  ),
  array(
    'title' => 'Profile Albums',
    'description' => 'Displays a member\'s albums on their profile.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.profile-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Albums',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
    array(
        'title' => 'Photos Hashtag Search',
        'description' => 'Displays photos on hashtag results page.',
        'category' => 'Albums',
        'type' => 'widget',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Photos',
            'titleCount' => true,
        ),
        'isPaginated' => true,
        'name' => 'album.hashtag-search-results',
        'requirements' => array(
            'no-subject',
        ),
    ),
  array(
    'title' => 'Popular Albums',
    'description' => 'Display a list of the most popular albums.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-popular-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Albums',
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
            ),
            'value' => 'comment_count',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Popular Photos',
    'description' => 'Display a list of the most popular photos.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-popular-photos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Photos',
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
            ),
            'value' => 'comment_count',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Albums',
    'description' => 'Display a list of the most recent albums.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-recent-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Albums',
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
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Photos',
    'description' => 'Display a list of the most recent photos.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-recent-photos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Photos',
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
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Album Browse Search',
    'description' => 'Displays a search form in the album gutter.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.browse-search',
  ),
  array(
    'title' => 'Album Photos Browse Search',
    'description' => 'Displays a search form in the Album Photos Browse Page.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.search-photos',
  ),
  array(
    'title' => 'Album Browse Quick Menu',
    'description' => 'Displays a menu in the album gutter.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.browse-menu-quick',
  ),
  array(
    'title' => 'Album Browse Menu',
    'description' => 'Displays a menu in the album browse page.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.browse-menu',
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Album Categories',
    'description' => 'Display a list of categories for albums.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-categories',
  ),
);