<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9995 2013-03-26 00:23:47Z alex $
 * @author     John
 */
return array(
    array(
        'title' => 'Videos Hashtag Search',
        'description' => 'Displays videos on hashtag results page.',
        'category' => 'Videos',
        'type' => 'widget',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Videos',
            'titleCount' => true,
        ),
        'isPaginated' => true,
        'name' => 'video.hashtag-search-results',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Profile Videos',
        'description' => 'Displays a member\'s videos on their profile.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.profile-videos',
        'isPaginated' => true,
        'requirements' => array(
            'subject' => 'user',
        ),
    ),
    array(
        'title' => 'Recent Videos',
        'description' => 'Displays a list of recently uploaded videos.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.list-recent-videos',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Recent Videos',
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
        'title' => 'Popular Videos',
        'description' => 'Displays a list of most viewed videos.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.list-popular-videos',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Popular Videos',
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
                      'rating' => 'Rating',
                    ),
                    'value' => 'view_count',
                  )
                ),
            )
        ),
    ),
    array(
        'title' => 'People Also Liked',
        'description' => 'Displays a list of other videos that the people who liked this video also liked.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.show-also-liked',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'People Also Liked',
        ),
        'requirements' => array(
            'subject' => 'video',
        ),
    ),
    array(
        'title' => 'Other Videos From Member',
        'description' => 'Displays a list of other videos that the member that uploaded this video uploaded.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.show-same-poster',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'From the same member',
        ),
        'requirements' => array(
            'subject' => 'video',
        ),
    ),
    array(
        'title' => 'Similar Videos',
        'description' => 'Displays a list of other videos that are similar to the current video, based on tags.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.show-same-tags',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Similar Videos',
        ),
        'requirements' => array(
            'subject' => 'video',
        ),
    ),

    array(
        'title' => 'Video Browse Search',
        'description' => 'Displays a search form in the video browse page.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.browse-search',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Video Browse Menu',
        'description' => 'Displays a menu in the video browse page.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.browse-menu',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Video Browse Quick Menu',
        'description' => 'Displays a small menu in the video browse page.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.browse-menu-quick',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Video Categories',
        'description' => 'Display a list of categories for videos.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.list-categories',
    ),
    
    array(
        'title' => 'Breadcrumb for Video View Page',
        'description' => 'Displays Breadcrumb for video view page.',
        'category' => 'Videos',
        'type' => 'widget',
        'name' => 'video.breadcrumb',
    ),
) ?>
