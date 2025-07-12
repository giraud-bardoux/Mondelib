<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Core.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Api_Core extends Core_Api_Abstract 
{
    /**
   * Loader for parsers
   *
   * @var Zend_Loader_PluginLoader
   */
  protected $_pluginLoader;

  /**
   * Activity template parsing
   *
   * @param string $body
   * @param array $params
   * @return string
   */
  public function fetchAction($action_ids, $param = 0) {

    $table = Engine_Api::_()->getDbTable('actions','activity');
    $tableName = $table->info('name');
    $select = $table->select()->from($tableName,array("action_id","subject_type","subject_id"))->group("subject_id");
    if(empty($param)) {
      $select->where("action_id IN (".$action_ids.")");
    } else {
      $select->where("action_id IN (?)", $action_ids);
    }

    return ($table->fetchAll($select));
  }

  public function assemble($body, array $params = array(),$break = true,$group_feed = false)
  {
    $paramsArray = $params['params'];
    if(is_array($paramsArray) && engine_count($paramsArray)){
        if(!empty($paramsArray['owner']) && empty($params['owner'])){
           unset($params['owner']);
           $params =  array_merge(array('owner'=> Engine_Api::_()->getItemByGuid($paramsArray['owner'])),$params);
        }
    }
    // Translate body
    $body = $this->getHelper('translate')->direct($body);
    $body =  $body.'|||||---|||++'.$break;
 
    preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);

    foreach( $matches as $match )
    {
      $tag = $match[0];
      $args = explode(':', $match[1]);
      $helper = array_shift($args);

      $helperArgs = array();
      foreach( $args as $arg )
      {
        if( substr($arg, 0, 1) === '$' )
        {
          $valid = true;
          $arg = substr($arg, 1);
          if($arg == "subject" && !empty($params['resource_id']) && !empty($params['resource_type'])){
            $item = Engine_Api::_()->getItem($params['resource_type'],$params['resource_id']);
            if($item){
              $helperArgs[] =  $item;
              $valid = false;
            }
          }
          if($valid)
            $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
        }
        else
        {
          $helperArgs[] = $arg;
        }
      }
      $helper = $this->getHelper($helper);
      $r = new ReflectionMethod($helper, 'direct');

      $content = $r->invokeArgs($helper, $helperArgs);
      $content = preg_replace('/\$(\d)/', '\\\\$\1', $content);
      $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
    }
    $body = str_replace('|||||---|||++'.$break,'',$body);
    if($break)
		  $body = explode('BODYSTRING',$body);
    else
      $body = str_replace('BODYSTRING','',$body);
    return $body;
  }
  
  /**
   * Gets the plugin loader
   *
   * @return Zend_Loader_PluginLoader
   */
  public function getPluginLoader()
  {
    if( null === $this->_pluginLoader )
    {
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
          . 'modules' . DIRECTORY_SEPARATOR
          . 'Activity';
      $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
        'Activity_Model_Helper_' => $path . '/Model/Helper/'
      ));
    }

    return $this->_pluginLoader;
  }

  /**
   * Get a helper
   *
   * @param string $name
   * @return Activity_Model_Helper_Abstract
   */
  public function getHelper($name)
  {
    $name = $this->_normalizeHelperName($name);
    if( !isset($this->_helpers[$name]) )
    {
      $helper = $this->getPluginLoader()->load($name);
      $this->_helpers[$name] = new $helper;
    }

    return $this->_helpers[$name];
  }

  /**
   * Normalize helper name
   *
   * @param string $name
   * @return string
   */
  protected function _normalizeHelperName($name)
  {
    $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
    //$name = strtolower($name);
    $name = ucfirst($name);
    return $name;
  }
  
  public function getNetworks($type, $viewer) {
    $ids = array();
    $viewer_id = $viewer->getIdentity();
    if (empty($type) || empty($viewer_id)) {
        return;
    }

    if( $type == 1 ) {
        $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
        $ids = $networkTable->getMembershipsOfIds($viewer);
        $count = engine_count($ids);
        if( empty($count) ) {
            return;
        }

        $ids = array_unique($ids);
    }

    $table = Engine_Api::_()->getItemTable('network');
    $select = $table->select()
        ->order('title ASC');
    if ($type == 1 && !empty($ids)) {
        $select->where('network_id IN(?)', $ids);
    }
    return $table->fetchAll($select);
  }

  public function isNetworkBasePrivacy($string) {
    if (empty($string)) {
        return;
    }

    $arr = explode(',', $string);
    return preg_match("/network_/", $arr[0]);
  }

  public function getNetworkBasePrivacyIds($string) {
    if (empty($string)) {
        return;
    }

    $ids = array();
    $arr = explode(',', $string);
    foreach ($arr as $val) {
        $ids[] = str_replace('network_', '', $val);
    }
    return $ids;
  }

  public function getSpecialAlbum(User_Model_User $user, $type, $auth_view) {
  
    if (!engine_in_array($type, array('wall_friend', 'wall_network', 'wall_onlyme'))) {
      throw new Album_Model_Exception('Unknown special album type');
    }
    $table = Engine_Api::_()->getDbtable('albums', 'album');
    $select = $table->select()
      ->where('owner_type = ?', $user->getType())
      ->where('owner_id = ?', $user->getIdentity())
      ->where('type = ?', $type)
      ->order('album_id ASC')
      ->limit(1);

    $album = $table->fetchRow($select);

    // Create wall photos album if it doesn't exist yet
    if (null === $album) {
      $translate = Zend_Registry::get('Zend_Translate');
      $album = $table->createRow();
      $album->owner_type = 'user';
      $album->owner_id = $user->getIdentity();
      $album->title = $translate->_(ucfirst(str_replace("_", " ", $type)) . ' Photos');
      $album->type = $type;
      $album->save();

      // Authorizations
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
          $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($album, $role, 'comment', ($i <= $viewMax));
      }
    }

    return $album;
  }
  
  public function editContentPrivacy($item, $user, $auth_view = null) {
    $type = null;
    switch ($auth_view) {
        case 'everyone':
            $auth_view = "everyone";
            break;
        case 'networks':
            $auth_view = "owner_network";
            $type = '_network';
            break;
        case 'friends':
            $auth_view = 'owner_member';
            $type = '_friend';
            break;
        case 'onlyme':
            $auth_view = 'owner';
            $type = '_onlyme';
            break;
    }
    if (empty($auth_view)) {
        $auth_view = "everyone";
    }

    // Work For Album
    if ($item->getType() == 'album_photo') {
        $parent = $item->getParent();
        if ($auth_view != "everyone") {
            $type = 'wall' . $type;
            $album = $this->getSpecialAlbum($user, $type, $auth_view);
            if (isset($item->album_id)) {
                $item->album_id = $album->album_id;
            } else {
                $item->collection_id = $album->album_id;
            }

            $item->save();
        }
    }


    // Work For Music
    if ($item->getType() == 'music_playlist_song') {
        $parent = $item->getParent();
        if ($auth_view != "everyone") {
            $type = 'wall' . $type;
            $playlist = $this->getSpecialPlaylist($user, $type, $auth_view);
            $item->playlist_id = $playlist->playlist_id;
            $item->save();
        }
    }

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    $viewMax = array_search($auth_view, $roles);
    foreach ($roles as $i => $role) {
        $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
    }
  }
  
  public function getSpecialPlaylist(User_Model_User $user, $type, $auth_view)
  {
    if (!engine_in_array($type, array('wall_friend', 'wall_network', 'wall_onlyme'))) {
      throw new Music_Model_Exception('Unknown special album type');
    }
    
    $table = Engine_Api::_()->getDbtable('playlists', 'music');
    $select = $table->select()
        ->where('owner_type = ?', $user->getType())
        ->where('owner_id = ?', $user->getIdentity())
        ->where('special = ?', $type)
        ->order('playlist_id ASC')
        ->limit(1);

    $playlist = $table->fetchRow($select);

    // Create if it doesn't exist yet
    if( null === $playlist ) {
      $translate = Zend_Registry::get('Zend_Translate');

      $playlist = $table->createRow();
      $playlist->owner_type = 'user';
      $playlist->owner_id = $user->getIdentity();
      $playlist->special = $type;

      if( $type == 'message' ) {
        $playlist->title = $translate->_('_MUSIC_MESSAGE_PLAYLIST');
        $playlist->search = 0;
      } else {
        $playlist->title = $translate->_('_MUSIC_DEFAULT_PLAYLIST');
        $playlist->search = 1;
      }
      //approve setting work
      $playlist->approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('music_playlist', $user, 'approve');
      $playlist->save();

      // Authorizations
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
          $auth->setAllowed($playlist, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($playlist, $role, 'comment', ($i <= $viewMax));
      }
    }

    return $playlist;
  }
  
  public function getHashTags($string) {
    preg_match_all("/\s(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/", ' ' . $string, $hashtags);
    if (!empty($hashtags[0])) {
        foreach ($hashtags[0] as $key => $hashtag) {
            $hashtag = str_replace('#', '', $hashtag);
            $hashtags[0][$key] = trim($hashtag);
        }
    }

    return $hashtags;
  }

  public function getEmoticons($withIcons = false, $tinyEditor = false, $chatEmotions = false)
  {
    $filePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
        . 'modules' . DIRECTORY_SEPARATOR
        . "Activity/externals/emoticons/emoticons.php";
    $emoticons = file_exists($filePath) ? include $filePath : NULL;

    if (!$withIcons && !$tinyEditor && !$chatEmotions) {
        return $emoticons;
    }

    if($tinyEditor) {
        $emoticonString = '[';
        foreach($emoticons as $emoticon) {
            $emoticonString .= '"'.$emoticon.'",';
        }
        $emoticonString .= ']';
        return $emoticonString;
    }

    //Chat emoticon
    if($chatEmotions) {
        $emoticonArray = array();
        foreach($emoticons as $symbol => $emoticon) {
            $emoticonArray[$symbol] = $emoticon;
        }
        return json_encode($emoticonArray, JSON_HEX_QUOT | JSON_HEX_TAG);
    }

    $emoticonIcons = array();
    foreach ($emoticons as $symbol => $icon) {
      $emoticonIcons[" ".$symbol." "] = "<img class = \"emoticon_img\" src=\"" . Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . "application/modules/Activity/externals/emoticons/images/$icon\" border=\"0\" />";
      $emoticonIcons[$symbol." "] = "<img class = \"emoticon_img\" src=\"" . Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . "application/modules/Activity/externals/emoticons/images/$icon\" border=\"0\" />";
      $emoticonIcons[" ". $symbol] = "<img class = \"emoticon_img\" src=\"" . Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . "application/modules/Activity/externals/emoticons/images/$icon\" border=\"0\" />";
    }
    return $emoticonIcons;
  }
  function convertEmojiIcon($string){
		$emojiIcon = "";
		foreach(explode('_',$string) as $icon){
			$emojiIcon .= "\u{$icon}";
		}
		return preg_replace("/\\\\u([0-9A-F]{2,5})/i", "&#x$1;", $emojiIcon);
	}

 
  public function uploadBackgrounds() {

    $backgroundTable = Engine_Api::_()->getDbtable('backgrounds', 'activity');
    
    $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Activity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "backgrounds" . DIRECTORY_SEPARATOR;

    $file_display = array('jpg', 'jpeg', 'png', 'gif');
    if (file_exists($PathFile)) {
      $dir_contents = scandir( $PathFile );
      foreach ( $dir_contents as $file ) {
        $explode = explode('.', @$file );
        $class = end( $explode );
        $file_type = strtolower($class);
        if ( ($file !== '.') && ($file !== '..') && (engine_in_array( $file_type, $file_display)) ) {
          $images = explode('.', $file);
          
          //$db = Engine_Db_Table::getDefaultAdapter();
          //$db->beginTransaction();
          // If we're here, we're done
          try {
            $item = $backgroundTable->createRow();
            $values['enabled'] = 1;
            $values['starttime'] = date('Y-m-d');
            $values['enableenddate'] = 1;

            $item->setFromArray($values);
            $item->save();
            $item->order = $item->background_id;
            $item->save();
            if(!empty($file)) {
              $file_ext = pathinfo($file);
              $file_ext = $file_ext['extension'];
              $storage = Engine_Api::_()->getItemTable('storage_file');
              $pngFile = $PathFile . $file;
              $storageObject = $storage->createFile($pngFile, array(
                'parent_id' => $item->background_id,
                'parent_type' => 'activity_background',
                'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
              ));
              // Remove temporary file
              ////@unlink($file['tmp_name']);
              $item->file_id = $storageObject->file_id;
              $item->save();
            }
            //$db->commit();
          } catch(Exception $e) {
            //$db->rollBack();
            //throw $e;
          }
        }
      }
    }
  }


  public function uploadFeelingsMainIconsActivity() {

    $paginator = Engine_Api::_()->getDbTable('feelings','activity')->getFeelings(array('fetchAll' => 1, 'admin' => 1));
    foreach($paginator as $item) {

      $feelings = explode(' ',strtolower($item->title));

      $foldername = '';
      if(@$feelings[0]) {
        $foldername .= @$feelings[0];
      }

      if(@$feelings[1]) {
        $foldername .= '_'.@$feelings[1];
      }

      //Main Feeling icon work
      $mainFeelingIcon = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Activity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "feeling_activity" . DIRECTORY_SEPARATOR . 'feeling_activity_tittle_icons' . DIRECTORY_SEPARATOR . $foldername.'.png';

      if (file_exists($mainFeelingIcon)) {

        $file_ext = pathinfo($mainFeelingIcon);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($mainFeelingIcon, array(
          'parent_id' => $item->feeling_id,
          'parent_type' => 'activity_feeling',
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));

        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }
      //Main Feeling icon work
    }
  }

  public function uploadFeelingsActivity() {

    $feelingiconsTable = Engine_Api::_()->getDbtable('feelingicons', 'activity');

    $paginator = Engine_Api::_()->getDbTable('feelings','activity')->getFeelings(array('fetchAll' => 1, 'admin' => 1));
    foreach($paginator as $item) {

      $feelings = explode(' ',strtolower($item->title));
      $foldername = '';

      if(@$feelings[0]) {
        $foldername .= @$feelings[0];
      }

      if(@$feelings[1]) {
        $foldername .= '_'.@$feelings[1];
      }

      $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Activity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "feeling_activity" . DIRECTORY_SEPARATOR . $foldername . DIRECTORY_SEPARATOR;

      // Get all existing log files
      $logFiles = array();
      $file_display = array('jpg', 'jpeg', 'png', 'gif');
      if (file_exists($PathFile)) {

        $dir_contents = scandir( $PathFile );

        foreach ( $dir_contents as $file ) {

          $fileex = explode('.', $file );
          $fileend = end( $fileex );
          $file_type = strtolower( $fileend );
          if ( ($file !== '.') && ($file !== '..') && (engine_in_array( $file_type, $file_display)) ) {

            $images = explode('.', $file);
            //$db = Engine_Db_Table::getDefaultAdapter();
            //$db->beginTransaction();
            // If we're here, we're done

            try {

              $values['title'] = str_replace('_', ' ', $images[0]);
              $values['type'] = 1;
              $values['feeling_id'] = $item->feeling_id;

              $getEmojiIconExist = Engine_Api::_()->getDbTable('feelingicons', 'activity')->getFeelingIconExist(array('title' => str_replace('_', ' ', $images[0])));

              if(empty($getEmojiIconExist)) {

                $item = $feelingiconsTable->createRow();

                $item->setFromArray($values);
                $item->save();

                if(!empty($file)) {
                  $file_ext = pathinfo($file);
                  $file_ext = $file_ext['extension'];
                  $storage = Engine_Api::_()->getItemTable('storage_file');

                  $pngFile = $PathFile . $file;

                  $storageObject = $storage->createFile($pngFile, array(
                    'parent_id' => $item->getIdentity(),
                    'parent_type' => $item->getType(),
                    'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                  ));

                  // Remove temporary file
                  ////@unlink($file['tmp_name']);
                  $item->feeling_icon = $storageObject->file_id;
                  $item->save();
                }
                //$db->commit();
              }
            } catch(Exception $e) {
              //$db->rollBack();
              //throw $e;
            }
          }
        }
      }
    }
  }

  public function uploadReactions() {

    $mangereaction = Engine_Api::_()->getApi('settings', 'core')->getSetting('comment.managereactions', 0);
    if(empty($mangereaction)) {

      //Upload Reactions
      $reactionsTable = Engine_Api::_()->getDbTable('reactions', 'comment');
      $emotiongalleriesselect = $reactionsTable->select()->order('reaction_id ASC');
      $paginator = $reactionsTable->fetchAll($emotiongalleriesselect);
      $db = Engine_Db_Table::getDefaultAdapter();

      if(engine_count($paginator) > 0) {
        foreach($paginator as $result) {

          $title = $result->title;
          if($title == 'Like') {
            $title = 'icon-like';
          } elseif($title == 'Love') {
            $title = 'icon-love';
          } elseif($title == 'Sad') {
            $title = 'icon-sad';
          } elseif($title == 'Wow') {
            $title = 'icon-wow';
          } elseif($title == 'Haha') {
            $title = 'icon-haha';
          } elseif($title == 'Angry') {
            $title = 'icon-angery';
          }

          $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;

          if (is_file($PathFile . $title . '.png'))  {
            $pngFile = $PathFile . $title . '.png';
            $photo_params = array(
                'parent_id' => $result->reaction_id,
                'parent_type' => "comment_reaction",
            );
            $photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
            if (!empty($photoFile->file_id)) {
              $db->update('engine4_comment_reactions', array('file_id' => $photoFile->file_id), array('reaction_id = ?' => $result->reaction_id));
            }
          }
        }
        Engine_Api::_()->getApi('settings', 'core')->setSetting('comment.managereactions', 1);
      }
    }
  }
}
