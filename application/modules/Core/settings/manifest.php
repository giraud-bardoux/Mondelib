<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
return array(
    // Package -------------------------------------------------------------------
    'package' => array(
        'type' => 'module',
        'name' => 'core',
        'version' => '7.4.0',
        'revision' => '$Revision: 10271 $',
        'path' => 'application/modules/Core',
        'repository' => 'socialengine.com',
        'title' => 'Core',
        'description' => 'Core',
        'author' => 'SocialEngine Core',
        'thumb' => 'application/modules/Core/externals/images/thumb.png',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            //'enable',
            //'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/Core/settings/install.php',
            'class' => 'Core_Install',
            'priority' => 9001,
        ),
        'dependencies' => array(
            array(
                'type' => 'library',
                'name' => 'engine',
                'required' => true,
                'minVersion' => '5.0.0',
            ),
        ),
        'directories' => array(
            'application/modules/Core',
        ),
        'files' => array(
            'application/languages/en/core.csv',
        ),
        'tests' => array(
            // MySQL Adapters
            array(
                'type' => 'Multi',
                'name' => 'MySQL',
                'allForOne' => true,
                'messages' => array(
                    'allTestsFailed' => 'Requires one of the following extensions: mysql, mysqli, pdo_mysql',
                ),
                'tests' => array(
                    array(
                        'type' => 'PhpExtension',
                        'extension' => 'mysql',
                    ),
                    array(
                        'type' => 'PhpExtension',
                        'extension' => 'mysqli',
                    ),
                    array(
                        'type' => 'PhpExtension',
                        'extension' => 'pdo_mysql',
                    ),
                ),
            ),
            // MySQL Server
            array(
                'type' => 'MysqlServer',
                'name' => 'MySQL 5.7',
                'minVersion' => '5.7',
            ),
            array(
                'type' => 'MysqlEngine',
                'name' => 'MySQL InnoDB Storage Engine',
                'engine' => 'innodb',
            ),
        ),
    ),
    // Composer -------------------------------------------------------------------
    'composer' => array(
//         'link' => array(
//             'script' => array('_composeLink.tpl', 'core'),
//             'plugin' => 'Core_Plugin_Composer',
//             'auth' => array('core_link', 'create'),
//         ),
//         'tag' => array(
//             'script' => array('_composeTag.tpl', 'core'),
//             'plugin' => 'Core_Plugin_Composer',
//             'allowEdit' => 1,
//         ),
    ),
    //Load by default css / js file ---------------------------------------------------------------------
    'loadDefault' => array(
      "js" => array(
        'externals/tinymce/tinymce.min.js',
        'externals/selectize/js/selectize.js',
        'externals/jQuery/wookmark.min.js',
        'externals/jQuery/imagesloaded.pkgd.js',
        'externals/jQuery/jquery.drag-n-crop.js',
        'externals/jQuery/sticky-sidebar.js',
        'externals/jQuery/owlcarousel/jquery.js',
        'externals/jQuery/owlcarousel/owl.carousel.js',
        'externals/mdetect/mdetect.js',
        'externals/tagger/tagger.js',
        'externals/jQuery/tooltip/jquery.tooltipster.js',
        'application/modules/Core/externals/scripts/coverphoto.js',
        'externals/uploader/uploader.js',
        'externals/simplelightbox/js/slick.min.js',
        'externals/simplelightbox/js/darkbox.js',
//         'application/modules/Core/externals/scripts/composer_link.js',
//         'application/modules/Core/externals/scripts/composer_tag.js',
//         'application/modules/Core/externals/scripts/comments_composer.js',
//         'application/modules/Core/externals/scripts/comments_composer_tag.js',
//         'application/modules/Core/externals/scripts/composer.js',
      ),
      'css' => array(
        'externals/selectize/css/normalize.css',
        'externals/fancyupload/fancyupload.css',
        'externals/jQuery/jquery.drag-n-crop.css',
        'application/modules/Core/externals/styles/coverphoto.css',
        'externals/uploader/uploader.css',
        'externals/simplelightbox/css/slick.css',
        'externals/simplelightbox/css/darkbox.css',
      )
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onItemDeleteBefore',
            'resource' => 'Core_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Core_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutDefaultSimple',
            'resource' => 'Core_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutMobileDefault',
            'resource' => 'Core_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutMobileDefaultSimple',
            'resource' => 'Core_Plugin_Core',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'core_ad', 'core_adcampaign', 'core_adphoto','core_banner','core_comment','core_geotag','core_link','core_like','core_list', 'core_list_item','core_page','core_report','core_mail_template', 'core_tag', 'core_tag_map','core_file','core_language','core_ticket','core_ticketreply','core_category','core_location','core_country','core_state','core_sitemap','core_recentlyviewitems', 'core_favourite',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        'home' => array(
            'route' => '/',
            'defaults' => array(
                'module' => 'core',
                'controller' => 'index',
                'action' => 'index'
            )
        ),
        'core_admin_settings' => array(
            'route' => "admin/core/settings/:action/*",
            'defaults' => array(
                'module' => 'core',
                'controller' => 'admin-settings',
                'action' => 'index'
            ),
            'reqs' => array(
                'action' => '\D+',
            )
        ),
        'core_rss_news' => array(
            'route' => "admin/core/rss/news",
            'defaults' => array(
                'module' => 'core',
                'controller' => 'admin-news',
                'action' => 'index'
            )
        ),
        'core_hashtags' => array(
            'route' => 'hashtags/*',
            'defaults' => array(
                'module' => 'core',
                'controller' => 'hashtag',
                'action' => 'index'
            )
        ),
        'core_tickets' => array(
            'route' => 'supports/:action/*',
            'defaults' => array(
                'module' => 'core',
                'controller' => 'support',
                'action' => 'index'
            ),
            'reqs' => array(
                'action' => '(index|resubmit)',
            ),
        ),
        'core_get_direction' => array(
            'route' => 'directions/:action/*',
            'defaults' => array(
                'module' => 'core',
                'controller' => 'location',
                'action' => 'get-direction',
            ),
        ),
        'core_admin_seo' => array(
            'route' => "admin/core/seo/:action/*",
            'defaults' => array(
                'module' => 'core',
                'controller' => 'admin-seo',
                'action' => 'index'
            ),
            'reqs' => array(
                'action' => '\D+',
            )
        ),
    )
);
