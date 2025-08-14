<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
return array(
    array(
        'title' => 'Blogs Hashtag Search',
        'description' => 'Displays blogs on hashtag results page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Blogs',
            'titleCount' => true,
        ),
        'isPaginated' => true,
        'name' => 'blog.hashtag-search-results',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Profile Blogs',
        'description' => 'Displays a member\'s blog entries on their profile.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.profile-blogs',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Blogs',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'user',
        ),
    ),
    array(
        'title' => 'Popular Blog Entries',
        'description' => 'Displays a list of most viewed blog entries.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.list-popular-blogs',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Popular Blog Entries',
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
                  'value' => 'view_count',
                )
              ),
            )
        ),
    ),
    array(
        'title' => 'Recent Blog Entries',
        'description' => 'Displays a list of recently posted blog entries.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.list-recent-blogs',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Recent Blog Entries',
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
        'title' => 'Blog Gutter Search',
        'description' => 'Displays a search form in the blog gutter.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.gutter-search',
    ),
    array(
        'title' => 'Blog Gutter Menu',
        'description' => 'Displays a menu in the blog gutter.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.gutter-menu',
    ),
    array(
        'title' => 'Blog Gutter Photo',
        'description' => 'Displays owner\'s or/and blog\'s photo in the blog gutter.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.gutter-photo',
    ),

    array(
        'title' => 'Blog Browse Search',
        'description' => 'Displays a search form in the blog browse page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.browse-search',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Blog Browse Menu',
        'description' => 'Displays a menu in the blog browse page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.browse-menu',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Blog Browse Quick Menu',
        'description' => 'Displays a small menu in the blog browse page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.browse-menu-quick',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Blog Categories',
        'description' => 'Display a list of categories for blogs.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.list-categories',
    ),
		array(
        'title' => 'Landing Page Popular Blog Entries',
        'description' => 'Displays Popular Blog Entries on the Landing Page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.landing-page-blogs',
        'defaultParams' => array(
            'title' => 'Popular Blog Entries',
        ),
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'popularType',
                    array(
                        'label' => 'Popular Type',
                        'multiOptions' => array(
                            'view' => 'Views',
                            'comment' => 'Comments',
                        ),
                        'value' => 'comment',
                    )
                ),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Count (number of items to show)',
                    )
                ),
            )
        ),
    ),
		array(
        'title' => 'Breadcrumb for Blog View Page',
        'description' => 'Displays Breadcrumb for Blog view page.',
        'category' => 'Blogs',
        'type' => 'widget',
        'name' => 'blog.breadcrumb',
    ),
) ?>
