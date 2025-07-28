<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: customroutes.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


$customRoutes = array();
$customRoutes["sesmember"] = array(
    'sesmember_general' => array(
      'route' => 'members/action/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'index',
        'action' => 'browse'
      ),
    ),
);
$customRoutes["activity"] = array(
    'recent_activity' => array(
      'route' => 'activity/composer-options/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action'=>'composer-options'
      )
    )
  );
  
  
$customRoutes["gif_activity"] = array(
    'gif_activity' => array(
      'route' => 'activity/gif/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action'=>'gif'
      )
    )
  );

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('music')) {
  $customRoutes["music"] = array(
    'music_general' => array(
        'route' => 'music/:action/*',
        'defaults' => array(
            'module' => 'music',
            'controller' => 'index',
            'action' => 'browse',
        ),
    ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')) {
  $customRoutes["video"] = array(
    'video_general' => array(
        'route' => 'videos/:action/*',
        'defaults' => array(
            'module' => 'video',
            'controller' => 'index',
            'action' => 'browse',
        ),
    ),
  );
}
 
if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesforum')) {
  $customRoutes["forum"] = array(
    'sesforum_general' => array(
      'route' => 'forums/:action/*',
      'defaults' => array(
        'module' => 'sesforum',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
  );
}


if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('forum')) {
  $customRoutes["forum"] = array(
    'forum_general' => array(
      'route' => 'forums/:action/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
    'forum_photo' => array(
      'route' => 'forums/:action/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album')) {
  $customRoutes["album"] = array(
    'album_general' => array(
        'route' => 'albums/:action/*',
        'defaults' => array(
            'module' => 'album',
            'controller' => 'index',
            'action' => 'browse',
        ),
    ),
    'album_specific_1' => array(
      'route' => 'album/:action/:album_id/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'album',
        'action' => 'view',
      ),
    ),
    'album_photo_specific' => array(
        'route' => 'photos/:action/*',
        'defaults' => array(
            'module' => 'album',
            'controller' => 'photo',
            'action' => 'index',
        ),
    ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum')) {
  $customRoutes["sesalbum"] = array(
    'sesalbum_general' => array(
        'route' => 'albums/:action/*',
        'defaults' => array(
            'module' => 'sesalbum',
            'controller' => 'index',
            'action' => 'browse',
        ),
    ),
    'sesalbum_specific_album_custom' => array(
        'route' =>  'album/view/:album_id',
        'defaults' => array(
            'module' => 'sesalbum',
            'controller' => 'album',
            'action' => 'view',
            'slug' =>''
        ),
    ),
    'sesalbum_specific' => array(
        'route' => 'album/:action/:album_id/*',
        'defaults' => array(
            'module' => 'sesalbum',
            'controller' => 'album',
            'action' => 'view'
        ),
    ),
    'sesalbum_photo_specific' => array(
        'route' => 'photos/:action/*',
        'defaults' => array(
            'module' => 'sesalbum',
            'controller' => 'photo',
            'action' => 'index',
        ),
    ),
  );
}
if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesvideo')) {
$customRoutes["sesvideo"] = array(
  'sesvideo_general' => array(
    'route' => 'videos/:action/*',
    'defaults' => array(
        'module' => 'sesvideo',
        'controller' => 'index',
        'action' => 'browse',
    ),
  ),
  'sesvideo_chanel' => array(
    'route' => 'channels/:action/*',
    'defaults' => array(
        'module' => 'sesvideo',
        'controller' => 'chanel',
        'action' => 'browse',
    ),
  ),
  'sesvideo_playlist' => array(
    'route' => 'videos/playlists/:action/*',
    'defaults' => array(
        'module' => 'sesvideo',
        'controller' => 'playlist',
        'action' => 'browse',
    ),
  ),
  'sesvideo_playlist_view' => array(
    'route' => 'videos/playlists/:action/*',
    'defaults' => array(
        'module' => 'sesvideo',
        'controller' => 'playlist',
        'action' => 'browse',
    ),
  ),
  'sesvideo_artists' => array(
      'route' => 'videos/artists/:action/*',
      'defaults' => array(
          'module' => 'sesvideo',
          'controller' => 'artist',
          'action' => 'browse',
      ),
  ),
  'sesvideo_artist' => array(
      'route' => 'videos/artist/:action/*',
      'defaults' => array(
          'module' => 'sesvideo',
          'controller' => 'artist',
          'action' => 'view',
      ),
  ),
);
}
if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmusic')) {
$customRoutes['sesmusic'] =
 array(
  'sesmusic_extended' => array(
      'route' => 'musics/albums/:action/*',
      'defaults' => array(
          'module' => 'sesmusic',
          'controller' => 'index',
          'action' => 'browse',
      ),
  ),
  'sesmusic_songs' => array(
      'route' => 'musics/songs/:action/*',
      'defaults' => array(
          'module' => 'sesmusic',
          'controller' => 'song',
          'action' => 'browse',
      ),
  ),
  'sesmusic_artists' => array(
      'route' => 'musics/artists/:action/*',
      'defaults' => array(
          'module' => 'sesmusic',
          'controller' => 'artist',
          'action' => 'browse',
      ),
  ),
  'sesmusic_playlists' => array(
      'route' => 'musics/playlists/:action/*',
      'defaults' => array(
          'module' => 'sesmusic',
          'controller' => 'playlist',
          'action' => 'browse',
      ),
  ),
);
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('classified')) {
  $customRoutes['classified'] =
  array(
   'classified_general' =>
    array(
      'route' => 'classifieds/:action/*',
      'defaults' => array(
        'module' => 'classified',
        'controller' => 'index',
        'action' => 'browse',
      ),
    ),
    'classified_entry_view' => array(
      'route' => 'classifieds/:classified_id/*',
      'defaults' => array(
        'module' => 'classified',
        'controller' => 'index',
        'action' => 'view',
      ),
      'reqs' => array(
        'classified_id' => '\d+'
      )
    )
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('group')) {

  $customRoutes['group']  =
  array(
   'group_general' =>
    array(
      'route' => 'groups/:action/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'index',
        'action' => 'browse',
      ),
    ),
//     'group_profile' => array(
//       'route' => 'group/:action/:id/*',
//       'defaults' => array(
//         'module' => 'group',
//         'controller' => 'profile',
//         'action' => 'view',
//       ),
//     ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('event')) {
  $customRoutes['event']  =
  array(
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
    'event_general' => array(
      'route' => 'events/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
      )
    ),
//     'event_profile' => array(
//       'route' => 'event/:action/:id/*',
//       'defaults' => array(
//         'module' => 'event',
//         'controller' => 'profile',
//         'action' => 'view',
//       ),
//     ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poll')) {
  $customRoutes['poll']  =
  array(
    'poll_general' => array(
      'route' => 'polls/:action/*',
      'defaults' => array(
        'module' => 'poll',
        'controller' => 'index',
        'action' => 'browse',
      ),
    ),
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('blog')) {
  $customRoutes['blog']  =
  array(
   'blog_general' =>
    array(
      'route' => 'blogs/:action/*',
      'defaults' => array(
        'module' => 'blog',
        'controller' => 'index',
        'action' => 'browse',
      ),
    ),
    'blog_entry_view' => array(
      'route' => 'blog/:blog_id/*',
      'defaults' => array(
        'module' => 'blog',
        'controller' => 'index',
        'action' => 'view',
      ),
    )
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesnews')) {
  $customRoutes['sesnews']  =
  array(
   'sesnews_general' =>
    array(
        'route' => 'news/:action/*',
        'defaults' => array(
          'module' => 'sesnews',
          'controller' => 'index',
          'action' => 'browse',
        ),
    ),
    'sesnews_entry_view' => array(
        'route' => 'news/:news_id/*',
        'defaults' => array(
          'module' => 'sesnews',
          'controller' => 'index',
          'action' => 'view',
        ),
      )
  );
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesblog')) {
  $customRoutes['sesblog']  =
  array(
   'sesblog_general' =>
    array(
        'route' => 'blogs/:action/*',
        'defaults' => array(
          'module' => 'sesblog',
          'controller' => 'index',
          'action' => 'browse',
        ),
    ),
    'sesblog_entry_view' => array(
        'route' => 'blog/:blog_id/*',
        'defaults' => array(
          'module' => 'sesblog',
          'controller' => 'index',
          'action' => 'view',
        ),
      )
  );
}


$customRoutes['sesarticle']  =
array(
 'sesarticle_general' =>
  array(
      'route' => 'articles/:action/*',
      'defaults' => array(
        'module' => 'sesarticle',
        'controller' => 'index',
        'action' => 'browse',
      ),
  ),
  'sesarticle_entry_view' => array(
      'route' => 'article/:article_id/*',
      'defaults' => array(
        'module' => 'sesarticle',
        'controller' => 'index',
        'action' => 'view',
      ),
    )
);

if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('elivestreaming')) {
  $customRoutes['elivestreaming']  =
    array(
      'elivestreaming_notification' =>
      array(
        'route' => 'elivestreaming/notification/:action/*',
        'defaults' => array(
          'module' => 'elivestreaming',
          'controller' => 'notification',
          'action' => 'send',
        ),
      ),
    );
}
return $customRoutes;
