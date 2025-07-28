<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Api_Core extends Core_Api_Abstract {

  public function file_types($type) {
  
    $counter = 0;
    $types = array(
    // Image formats
    'image_'.$counter++ => 'image/jpeg',
    'image_'.$counter++ => 'image/gif',
    'image_'.$counter++ => 'image/png',
    'image_'.$counter++ => 'image/bmp',
    'image_'.$counter++ => 'image/tiff',
    'image_'.$counter++ => 'image/x-icon',
    // Video formats
    'video_'.$counter++ => 'video/x-ms-asf',
    'video_'.$counter++ => 'video/x-ms-wmv',
    'video_'.$counter++ => 'video/x-ms-wmx',
    'video_'.$counter++ => 'video/x-ms-wm',
    'video_'.$counter++ => 'video/avi',
    'video_'.$counter++ => 'video/divx',
    'video_'.$counter++ => 'video/x-flv',
    'video_'.$counter++ => 'video/quicktime',
    'video_'.$counter++ => 'video/mpeg',
    'video_'.$counter++ => 'video/mp4',
    'video_'.$counter++ => 'video/ogg',
    'video_'.$counter++ => 'video/webm',
    'video_'.$counter++ => 'video/x-matroska',
    // Text formats
    'text_'.$counter++ => 'text/plain',
    'code_'.$counter++ => 'application/octet-stream',
    'csv_'.$counter++ => 'text/csv',
    'text_'.$counter++ => 'text/tab-separated-values',
    'calander_'.$counter++ => 'text/calendar',
    'text_'.$counter++ => 'text/richtext',
    'code_'.$counter++ => 'text/css',
    'code_'.$counter++ => 'text/html',
    // Audio formats
    'audio_'.$counter++ => 'audio/mpeg',
    'audio_'.$counter++ => 'audio/x-realaudio',
    'audio_'.$counter++ => 'audio/wav',
    'audio_'.$counter++ => 'audio/amr',
      'audio_'.$counter++ => 'audio/mp3',
    'audio_'.$counter++ => 'audio/ogg',
    'audio_'.$counter++ => 'audio/midi',
    'audio_'.$counter++ => 'audio/x-ms-wma',
    'audio_'.$counter++ => 'audio/x-ms-wax',
    'audio_'.$counter++ => 'audio/x-matroska',
    // Misc application formats
    'file_'.$counter++ => 'application/rtf',
    'code_'.$counter++ => 'application/javascript',
    'pdf_'.$counter++ => 'application/pdf',
    'file_'.$counter++ => 'application/x-shockwave-flash',
    'file_'.$counter++ => 'application/java',
    'archive_'.$counter++ => 'application/x-tar',
    'archive_'.$counter++ => 'application/zip',
    'archive_'.$counter++ => 'application/x-gzip',
    'archive_'.$counter++ => 'application/rar',
    'file_'.$counter++ => 'application/x-7z-compressed',
    'exe_'.$counter++ => 'application/x-msdownload',
    // MS Office formats
    'document_'.$counter++ => 'application/msword',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint',
    'document_'.$counter++ => 'application/vnd.ms-write',
    'document_'.$counter++ => 'application/vnd.ms-excel',
    'document_'.$counter++ => 'application/vnd.ms-access',
    'document_'.$counter++ => 'application/vnd.ms-project',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'document_'.$counter++ => 'application/vnd.ms-word.document.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    'document_'.$counter++ => 'application/vnd.ms-word.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'document_'.$counter++ => 'application/vnd.ms-excel.sheet.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    'document_'.$counter++ => 'application/vnd.ms-excel.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-excel.addin.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.template',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
    'document_'.$counter++ => 'application/onenote',
    // OpenOffice formats
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.text',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.presentation',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.spreadsheet',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.graphics',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.chart',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.database',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.formula',
    // WordPerfect formats
    'file_'.$counter++ => 'application/wordperfect',
    // iWork formats
    'file_'.$counter++ => 'application/vnd.apple.keynote',
    'file_'.$counter++ => 'application/vnd.apple.numbers',
    'file_'.$counter++ => 'application/vnd.apple.pages',
    );
    if(false !== $key = array_search($type, $types)){
      return $key;
    }else{
      return "";
    }

  }
  function addShortCut($params = array()){
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $resource_type = $params['resource_type'];
    $resource_id = $params['resource_id'];
    $viewer = Engine_Api::_()->user()->getViewer();
    if(empty($resource_id) && empty($resource_type))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $view->translate('parameter_missing'), 'result' => array()));

    
    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);

    $shortcutTable = Engine_Api::_()->getDbTable('shortcuts', 'sesshortcut');
    $db = $shortcutTable->getAdapter();
    $db->beginTransaction();
    try {
      $id = $shortcutTable->addShortcut($resource, $viewer)->shortcut_id;
      $db->commit();
      return $id;
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $view->translate('database_error'), 'result' => array()));
    }
  }
  function removeShortCut($params = array()){
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $resource_type = $params['resource_type'];
    $resource_id = $params['resource_id'];
    $shortcut_id = $params['shortcut_id'];
    $viewer = Engine_Api::_()->user()->getViewer();
    if(empty($resource_id) && empty($resource_type))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $view->translate('parameter_missing'), 'result' => array()));

    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
    $shortcutTable = Engine_Api::_()->getDbTable('shortcuts', 'sesshortcut');
    $db = $shortcutTable->getAdapter();
    $db->beginTransaction();
    try {
      $shortcutTable->delete(array('shortcut_id =?' => $shortcut_id));
      $db->commit();
      return true;
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $view->translate('database_error'), 'result' => array()));
    }
  }
  function checkCodeexists(){
    $path = APPLICATION_PATH.DIRECTORY_SEPARATOR.'index.php';
    if(file_exists($path)){
      $checkString = "restApi";
      $content = file_get_contents($path);
      if(strpos($content,$checkString) !== false)
        return true;
    }
    return false;  
  }
  
  public function hasCheckMessage($user) {

    // Not logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity() || $viewer->getGuid(false) === $user->getGuid(false)) {
      return false;
    }

    // Get setting?
    $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
    if (Authorization_Api_Core::LEVEL_DISALLOW === $permission) {
      return false;
    }
    $messageAuth = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'auth');
    if ($messageAuth == 'none') {
      return false;
    } else if ($messageAuth == 'friends') {
      // Get data
      $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
      if (!$direction) {
        //one way
        $friendship_status = $viewer->membership()->getRow($user);
      } else
        $friendship_status = $user->membership()->getRow($viewer);

      if (!$friendship_status || $friendship_status->active == 0) {
        return false;
      }
    }
    return true;
  }
  
  //upload photo
  public function setPhoto($photo, $isURL = false, $isUploadDirect = false, $modulename = "", $memberlevelType = "", $photoParams = array(), $item = "", $package = false, $sameThumbWatermark = false,$watermarkLabel = 'watermark') {
    if (!$isURL) {
      if ($photo instanceof Zend_Form_Element_File) {
        $file = $photo->getFileName();
        $fileName = $file;
      } else if ($photo instanceof Storage_Model_File) {
        $file = $photo->temporary();
        $fileName = $photo->name;
      } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
        $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $file = $tmpRow->temporary();
        $fileName = $tmpRow->name;
      } else if (is_array($photo) && !empty($photo['tmp_name'])) {
        $file = $photo['tmp_name'];
        $fileName = $photo['name'];
      } else if (is_string($photo) && file_exists($photo)) {
        $file = $photo;
        $fileName = $photo;
      } else {
        throw new User_Model_Exception('invalid argument passed to setPhoto');
      }
      $name = basename($file);
      $extension = ltrim(strrchr($fileName, '.'), '.');
      $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    } else {
      $fileName = time() . '_' . $modulename;
      $PhotoExtension = '.' . pathinfo($photo, PATHINFO_EXTENSION);
      $filenameInsert = $fileName . $PhotoExtension;
      $copySuccess = @copy($photo, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/' . $filenameInsert);
      if ($copySuccess)
        $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $filenameInsert;
      else
        return false;
      $name = basename($photo);
      $extension = ltrim(strrchr($name, '.'), '.');
      $base = rtrim(substr(basename($name), 0, strrpos(basename($name), '.')), '.');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => $item->getType(),
        'parent_id' => $item->getIdentity(),
        'name' => $fileName,
    );
    $extension = Engine_Api::_()->core()->convertImageToWebp($extension);
    // Save
    $filesTable = Engine_Api::_()->getDbTable('files', 'storage');
    /* setting of image dimentions from core settings */
    $core_settings = Engine_Api::_()->getApi('settings', 'core');
    $main_height = $core_settings->getSetting($modulename . '.mainheight', 1600);
    $main_width = $core_settings->getSetting($modulename . '.mainwidth', 1600);
    $normal_height = $core_settings->getSetting($modulename . '.normalheight', 500);
    $normal_width = $core_settings->getSetting($modulename . '.normalwidth', 500);
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize($main_width, $main_height)
            ->write($mainPath)
            ->destroy();
    // Resize image (normal) make same image for activity feed so it open in pop up with out jump effect.
    $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize($normal_width, $normal_height)
            ->write($normalPath)
            ->destroy();
    //watermark on main photo
    if (!$isUploadDirect) {
      $enableWatermark = $core_settings->getSetting($modulename . '.watermark.enable', 0);
      if ($enableWatermark == 1) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $watermarkImage = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, $watermarkLabel);
        if (is_file($watermarkImage)) {
          if (isset($extension))
            $type = $extension;
          else
            $type = $PhotoExtension;
          $mainFileUploaded = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $name;
          $fileName = current(explode('/', $name));
          $fileName = explode('.', $fileName);
          if (isset($fileName[0]))
            $name = $fileName[0];
          else
            $name = time();
          $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $name . ".jpg";
          $watemarkImageResult = $this->watermark_image($mainPath, $fileNew, $type, $watermarkImage, $modulename);
          if ($watemarkImageResult) {
            @unlink($mainPath);
            $image->open($fileNew)
                    ->resize($main_width, $main_height)
                    ->write($mainPath)
                    ->destroy();
            @unlink($fileNew);
          }
          $watermarkImageNew = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, 'watermarkthumb');
          if($sameThumbWatermark)
            $watermarkImageNew = $watermarkImage;
          if (!is_file($watermarkImageNew)) {
            $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $fileName . ".jpg";
            $watemarkImageResult = $this->watermark_image($normalPath, $fileNew, $type, $watermarkImage, $modulename);
            if ($watemarkImageResult) {
              @unlink($normalPath);
              $image->open($fileNew)
                      ->resize($main_width, $main_height)
                      ->write($normalPath)
                      ->destroy();
              @unlink($fileNew);
            }
          }
        }
      }
    }

    //thumb photo watermark
    if ($enableWatermark == 1) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $watermarkImage = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, 'watermarkthumb');
      if($sameThumbWatermark)
            $watermarkImageNew = $watermarkImage;
      if (is_file($watermarkImage)) {
        if (isset($extension))
          $type = $extension;
        else
          $type = $PhotoExtension;
        $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $fileName . ".jpg";
        $watemarkImageThumbResult = $this->watermark_image($normalPath, $fileNew, $type, $watermarkImage, $modulename);
        if ($watemarkImageThumbResult) {
          @unlink($normalPath);
          $image->open($fileNew)
                  ->resize($normal_width, $normal_height)
                  ->write($normalPath)
                  ->destroy();
          @unlink($fileNew);
        }
      }
    }
    // normal main  image resize
    $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_nm.' . $extension;
    $image = Engine_Image::factory();
    $image->open($normalPath)
            ->resize($normal_width, $normal_height)
            ->write($normalMainPath)
            ->destroy();
    // Resize image (icon)
    $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 150, 150)
            ->write($squarePath)
            ->destroy();
    // Store
    try {
      $iSquare = $filesTable->createFile($squarePath, $params);
      $iMain = $filesTable->createFile($mainPath, $params);
      $iIconNormal = $filesTable->createFile($normalPath, $params);
      $iNormalMain = $filesTable->createFile($normalMainPath, $params);
      $iMain->bridge($iNormalMain, 'thumb.normalmain');
      $iMain->bridge($iIconNormal, 'thumb.normal');
      $iMain->bridge($iSquare, 'thumb.icon');
    } catch (Exception $e) {
      @unlink($file);
      // Remove temp files
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($squarePath);
      @unlink($normalMainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    @unlink($file);
    // Remove temp files
    @unlink($mainPath);
    @unlink($normalPath);
    @unlink($squarePath);
    @unlink($normalMainPath);
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    if ($package)
      return $iMain->file_id;;
    $photoParams['file_id'] = $iMain->file_id; // This might be wrong
    $photoParams['photo_id'] = $iMain->file_id;
    $row = Engine_Api::_()->getDbTable('photos', $modulename)->createRow();

    $row->setFromArray($photoParams);
    $row->save();
    return $row;
  }
  
  function deleteFeed($params = array()) {
    $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    $select = $actionTable->select()
            ->where('type =?', $params['type'])
            ->where('subject_id =?', $params['subject_id'])
            ->where('object_type =?', $params['object_type'])
            ->where('object_id =?', $params['object_id']);
    $actionObject = $actionTable->fetchRow($select);
    if($actionObject)
    $actionObject->delete();
  }

  public function getViewerPrivacy($resourceType = null, $privacy = null) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $select = new Zend_Db_Select($db);
      $select->from('engine4_authorization_levels', 'level_id')->where('type = ?', 'public');
      $levelId = $select->query()->fetchColumn();
      return Engine_Api::_()->authorization()->getPermission($levelId, $resourceType, $privacy);
    } else {
      return Engine_Api::_()->authorization()->getPermission($viewer, $resourceType, $privacy);
    }
  }
  
  public function isModuleEnable($name = '') {
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    return $moduleTable->select()->from($moduleTable->info('name'), new Zend_Db_Expr('COUNT(*)'))->where('name In (?)', $name)->where('enabled =?', 1)->query()->fetchColumn();
  }
  
/**
   * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
   */
  protected $_subject;
  function getMentionTags($content){
    $contentMention = $content;
    $mentions = array();
    preg_match_all('/(^|\s)(@\w+)/', $contentMention, $result);
    $counter = 0;
    
    foreach($result[2] as $value){
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0) {
            $user = Engine_Api::_()->getItem('user', $user_id);
            if (!$user)
                continue;
        }else{
            $itemArray = explode('_',$user_id);
            $resource_id = $itemArray[count($itemArray) - 1];
            unset($itemArray[count($itemArray) - 1]);
            $resource_type = implode('_',$itemArray);
            try {
                $user = Engine_Api::_()->getItem($resource_type, $resource_id);
            }catch (Exception $e){
                continue;
            }
            if(!$user || !$user->getIdentity())
                continue;
        }
        $mentions[$counter]['word'] = $value;
        $mentions[$counter]['title'] = $user->getTitle();
        $mentions[$counter]['module'] = 'user';
        $mentions[$counter]['href'] = $this->getBaseUrl(false).$user->getHref();
        $mentions[$counter]['user_id'] = $user->getIdentity();
        $counter++;
    }    
    return $mentions;
  }
  function gethashtags($content)
  {
    $hashTagWords = array();
    preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", $content, $matches);
    $searchword = $replaceWord = array();
    foreach($matches[0] as $value){
       $hashTagWords[]=$value;
    }
    return $hashTagWords;
  }
  function privacyOptions(){
    $arrayPrivacy = array('everyone'=>'Everyone','networks'=>"Friends & Networks",'friends'=>"Friends Only",'onlyme'=>"Only Me");
    $privacyArray = array();
    $conter = 0;
    $privacyFeed = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.view.privacy');
    foreach($arrayPrivacy as $key=>$pri){
      if(engine_in_array($key,$privacyFeed)){
        $privacyArray[$conter]['name'] = $key;
        $privacyArray[$conter]['value'] = $pri;
        $conter++;
      }
    } 
    return $privacyArray;   
  }
  
  public function contentLike($subject)
  {
      $viewer = Engine_Api::_()->user()->getViewer();
      //return if non logged in user or content empty
      if (empty($subject) || empty($viewer))
          return;
      if ($viewer->getIdentity())
          $like = Engine_Api::_()->getDbTable("likes", "core")->isLike($subject, $viewer);
      return !empty($like) ? true : false;
  }
  public function getContentLikeCount($subject) {
      $getLikeCount = $subject->likes()->getLikePaginator()->getTotalItemCount();
      return (int) $getLikeCount;
  }
  public function getContentCommentCount($subject) {
      $getCommentCount = $subject->comments()->getCommentPaginator()->getTotalItemCount();
      return (int) $getCommentCount;
  }
  public function contentFollow($subject = null,$tableName = "",$modulename = "",$resource_type = "",$column_name = "user_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    //return if non logged in user or content empty
    if (empty($subject) || empty($viewer))
        return;
    if ($viewer->getIdentity())
    {
          $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
          $select->where('resource_id =?',$viewer->getIdentity())->where($column_name.' =?',$subject->getIdentity());
          if($resource_type)
            $select->where('resource_type =?',$resource_type);

          $follow = (int) Zend_Paginator::factory($select)->getTotalItemCount();
    }
    return !empty($follow) ? true : false;
  }
  
  public function getContentFollowCount($subject,$tableName = "",$modulename = "",$resources_type = "",$column_name = "user_id"){
      $viewer = Engine_Api::_()->user()->getViewer();
      if(!$tableName || !$modulename)
        return 0;
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where($column_name.' =?',$subject->getIdentity());
      if($resources_type)
            $select->where('resource_type =?',$resources_type);
      return (int) Zend_Paginator::factory($select)->getTotalItemCount();
  }
  
  public function contentFavoutites($subject,$tableName = "",$modulename = "",$resources_type = "",$column_name = 'user_id'){
    $viewer = Engine_Api::_()->user()->getViewer();
    //return if non logged in user or content empty
    if (empty($subject) || empty($viewer))
        return;
    if ($viewer->getIdentity())
    {
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where($column_name.' =?',$viewer->getIdentity())->where('resource_id =?',$subject->getIdentity());
      if($resources_type)
        $select->where('resource_type =?',$resources_type);
      $fav = (int) Zend_Paginator::factory($select)->getTotalItemCount();
    }
    return !empty($fav) ? true : false;
  }
  function parseCSSFile($file)
  {
    $css = file_get_contents($file);
    preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
    $result = array();
    foreach ($arr[0] as $i => $x){
        $selector = trim($arr[1][$i]);
        $rules = explode(';', trim($arr[2][$i]));
        $rules_arr = array();
        foreach ($rules as $strRule){
            if (!empty($strRule)){
                $rule = explode(":", $strRule);
                $rules_arr[trim(@($rule[0]))] = trim(@($rule[1]));
            }
        }
        $selectors = explode(',', trim($selector));
        foreach ($selectors as $strSel){
            $result[trim($strSel)] = $rules_arr;
        }
    }
    return $result;
}
  public function getContentFavouriteCount($subject,$tableName = "",$modulename = "")
  {
      $viewer = Engine_Api::_()->user()->getViewer();
      if(!$tableName || !$modulename)
        return 0;
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where('resource_id =?',$subject->getIdentity());
      return (int) Zend_Paginator::factory($select)->getTotalItemCount();
  }
  public function getBaseUrl($staticBaseUrl = true,$url = ""){

//      if(strpos($url,'http') !== false)
//          return $url;
//      $http = 'http://';
//      if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
//          $http = 'https://';
//      }
//      if(strpos($url,'http') !== false || strpos($url,'https') !== false){
//          return $url;
//      }
//      $baseUrl =  $_SERVER['HTTP_HOST'].'/';
//
//      //if($staticBaseUrl){
//      $baseUrl = $baseUrl;
//      //}
//      return $http.str_replace('//','/',$baseUrl.$url);

    if(strpos($url,'http') !== false)
      return $url;
    $http = 'http://';
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
       $http = 'https://';
    }
    $baseUrl =  $_SERVER['HTTP_HOST'];
    if(Zend_Registry::get('StaticBaseUrl') != "/")
    $url = str_replace(Zend_Registry::get('StaticBaseUrl'),'',$url);

    if(strpos(Zend_Registry::get('StaticBaseUrl'),'http') !== false){
      $baseUrl = $baseUrl;
    }else{
      $baseUrl = $baseUrl."/".Zend_Registry::get('StaticBaseUrl');
    }

    //if($staticBaseUrl){
      // $baseUrl = $baseUrl."/".Zend_Registry::get('StaticBaseUrl');
    //}
    return $http.str_replace('//','/',$baseUrl.$url);
  }
    public function getCoverPhotoUrls($resource , $type,$resource_type,$getImageSize = false){
      $getImageSize = false;
        try
        {
            if(is_int($resource)  || intval($resource) > 0)
            {
                $resource_id = $resource;

                if(!$resource_id)
                    return  array();
            }
            else if(isset($resource->cover) && $resource->getType() != "album_photo")
                $resource_id = $resource->cover;
            else if(isset($resource->thumbnail_id))
                $resource_id  = $resource->thumbnail_id;
            else
                $resource_id = $resource->file_id;

            if($resource_id)
            {
                $table = Engine_Api::_()->getItemTable('storage_file');
                $select = $table->select()->from($table)->where('file_id =?',$resource_id);
                if($type)
                    $select->where('type =?',$type);
                if($resource_type)
                    $select->where('parent_type =?',$resource_type);
 
                $result = $table->fetchRow($select);
                $photos = array();
                if($result){
                    $counter = 0;
                    $photos["main"] = $this->getBaseUrl(true,$result->map());
                    if($getImageSize)
                        list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
                    $select = $table->select()->from($table)->where('parent_file_id =?',$result->getIdentity());
                    $result = $table->fetchAll($select);
                    foreach($result as $photo){
                        $type = str_replace('thumb.','',$photo->type);
                        $photos[$type] = $this->getBaseUrl(true,$photo->map());
                        if($getImageSize)
                            list($photos[$type."_width_height"]['width'],$photos[$type."_width_height"]['height']) = @getimagesize($photos[$type]);
                        $counter++;
                    }
                }else if(!is_int($resource)){
                    $photos["main"] =  $this->getBaseUrl(false,$resource->getPhotoURL());
                    if($getImageSize)
                        list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
                }
                return $photos;
            }else{
                if($resource->getCoverPhotoUrls()){
                    $photos["main"] =  $this->getBaseUrl(false,$resource->getCoverPhotoUrls());
                    //list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
                    return $photos;
                }
            }
        }catch(Exception $e){
            return array();
        }
        return array();
    }
  public function getPhotoUrls($resource , $type,$resource_type,$getImageSize = false)
  {
    $getImageSize = false;
    try
    {
     if(is_int($resource))
     {
       $resource_id = $resource;

        if(!$resource_id)
          return  array();
     }
      else if(isset($resource->photo_id) && $resource->getType() != "album_photo")
       $resource_id = $resource->photo_id;
     else if(isset($resource->thumbnail_id))
      $resource_id  = $resource->thumbnail_id;
     else
      $resource_id = $resource->file_id;
    
     if($resource_id)
     {
        $table = Engine_Api::_()->getItemTable('storage_file');
        $select = $table->select()->from($table)->where('file_id =?',$resource_id);
        if($type)
          $select->where('type =?',$type);
        if($resource_type)
          $select->where('parent_type =?',$resource_type);
          
        $result = $table->fetchRow($select);
        $photos = array();
        if($result){
          $counter = 0;
          $photos["main"] = $this->getBaseUrl(true,$result->map());
          if($getImageSize)
            list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
          $select = $table->select()->from($table)->where('parent_file_id =?',$result->getIdentity());
          $result = $table->fetchAll($select);
          foreach($result as $photo){
            $type = str_replace('thumb.','',$photo->type);
            $photos[$type] = $this->getBaseUrl(true,$photo->map());
            if($getImageSize)
            list($photos[$type."_width_height"]['width'],$photos[$type."_width_height"]['height']) = @getimagesize($photos[$type]);
            $counter++;
          }
        }else if(!is_int($resource)){
          $photos["main"] =  $this->getBaseUrl(false,$resource->getPhotoURL());
          if($getImageSize)
            list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
        }
        return $photos;
     }else{
       if($resource->getPhotoURL()){
        $photos["main"] =  $this->getBaseUrl(false,$resource->getPhotoURL());
        //list($photos["main_width_height"]['width'],$photos["main_width_height"]['height']) = @getimagesize($photos['main']);
        return $photos;
       }
     }
    }catch(Exception $e){
      return array();  
    }
     return array();
  }
  public function getCoordinates($address){
    $address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern   
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $url = "https://maps.google.com/maps/api/geocode/json?key=". $settings->getSetting('core.mapApiKey')."&sensor=false&address=".$address;
    $response = file_get_contents($url);
    $json = json_decode($response,TRUE); //generate array object from the response from the web
    if(!empty($json['results'][0]['geometry']['location']['lat']))
      return array('lat'=>$json['results'][0]['geometry']['location']['lat'],'lng'=>$json['results'][0]['geometry']['location']['lng']);
    else 
      return '';
  }
  public function friendship($subject){
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    
    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return false;
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return false;
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbTable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    
    if( !$direction )
    {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        $params[] = array(
          'label' => $view->translate('Follow'),
          'name'=>'add',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
       $params[] = array(
          'label' => $view->translate('Cancel Follow Request'),
          'name'=>'cancel',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else {
        // Unfollow
        $params[] = array(
          'label' => $view->translate('Unfollow'),
          'name' => 'remove',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      }
      
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        $params[] = array(
          'label' => $view->translate('Approve Follow Request'),
          'name' => 'confirm',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else {
        // Remove as follower?
        $params[] = array(
          'label' => $view->translate('Remove as Follower'),
          'name' => 'remove',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      }
      if( engine_count($params) == 1 ) {
        return $params[0];
      } else if( engine_count($params) == 0 ) {
        return false;
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {
      $row = $viewer->membership()->getRow($subject);
      if( null === $row ) {
        // Add
        return array(
          'label' => $view->translate('Add Friend'),
          'name' => 'add',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $view->translate('Cancel Friend'),
          'name' => 'cancel',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $view->translate('Approve Friend'),
          'name' => 'confirm',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      } else {
        // Remove friend
        return array(
          'label' => $view->translate('Remove Friend'),
          'name' => 'remove',
          'params' => array(
              'user_id' => $subject->getIdentity()
            ),
        );
      }
    }
  
  }
  public function getIdentityWidget($name, $type, $corePages) {
    if((isset($_SESSION['sespwa']['sespwa']) && !empty($_SESSION['sespwa']['sespwa'])) || (isset($_SESSION['sespwa']['mobile']) && !empty($_SESSION['sespwa']['mobile']))) {
      $widgetTable = Engine_Api::_()->getDbTable('content', 'sespwa');
      $widgetPages = Engine_Api::_()->getDbTable('pages', 'sespwa')->info('name');
    } else {
      $widgetTable = Engine_Api::_()->getDbTable('content', 'core');
      $widgetPages = Engine_Api::_()->getDbTable('pages', 'core')->info('name');
    }
    $identity = $widgetTable->select()
            ->setIntegrityCheck(false)
            ->from($widgetTable, '*')
            ->where($widgetTable->info('name') . '.type = ?', $type)
            ->where($widgetTable->info('name') . '.name = ?', $name)
            ->where($widgetPages . '.name = ?', $corePages)
            ->joinLeft($widgetPages, $widgetPages . '.page_id = ' . $widgetTable->info('name') . '.page_id',null);
       return     $widgetTable->fetchRow($identity);
  }
  /**
   * Set the object that represents the subject of the page
   *
   * @param Core_Model_Item_Abstract|mixed $subject
   * @return Core_Api_Core
   */
  public function setSubject($subject)
  {
    if( null !== $this->_subject ) {
      throw new Core_Model_Exception("The subject may not be set twice");
    }

    if( !($subject instanceof Core_Model_Item_Abstract) ) {
      throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
    }
    
    $this->_subject = $subject;
    return $this;
  }

  /**
   * Get the previously set subject of the page
   *
   * @return Core_Model_Item_Abstract|null
   */
  public function getSubject($type = null)
  {
    if( null === $this->_subject ) {
      throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
    } else if( is_string($type) && $type !== $this->_subject->getType() ) {
      throw new Core_Model_Exception("getSubject was given a type other than the set subject");
    } else if( is_array($type) && !engine_in_array($this->_subject->getType(), $type) ) {
      throw new Core_Model_Exception("getSubject was given a type other than the set subject");
    }
    
    return $this->_subject;
  }

  /**
   * Checks if a subject has been set
   *
   * @return bool
   */
  public function hasSubject($type = null)
  {
    if( null === $this->_subject ) {
      return false;
    } else if( null === $type ) {
      return true;
    } else {
      return ( $type === $this->_subject->getType() );
    }
  }

  public function clearSubject()
  {
    $this->_subject = null;
    return $this;
  }
  
  // Get Count of item based on category
  public function getCategoryBasedItems($params = array()) {

    $table = Engine_Api::_()->getDbTable($params['table_name'], $params['module_name']);
    $tableName = $table->info('name');
    return $table->select()
                ->from($tableName, array('count(*) as albumCount'))
                ->where('category_id =?', $params['category_id'])
                ->query()
                ->fetchColumn();
      
  }
  public static function encode($text)
  {
    return self::convert($text, 'ENCODE');
  }
  public static function decode($text)
  {
    return self::convert($text, 'DECODE');
  }
  private static function convert($text, $op)
  {
    if ($op == 'ENCODE') {
      return preg_replace_callback('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{1F000}-\x{1FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{1F000}-\x{1FEFF}]?/u', array('self', 'encodeEmoji'), $text);
    } else {
      return preg_replace_callback('/(\\\u[0-9a-f]{4})+/', array('self', 'decodeEmoji'), $text);
    }
  }
  private static function encodeEmoji($match)
  {
    return str_replace(array('[', ']', '"'), '', json_encode($match));
  }
  private static function decodeEmoji($text)
  {
    if (!$text) {
      return '';
    }
    $text = $text[0];
    $decode = json_decode($text, true);
    if ($decode) {
      return $decode;
    }
    $text = '["' . $text . '"]';
    $decode = json_decode($text);
    if (engine_count($decode) == 1) {
      return $decode[0];
    }
    return $text;
  }
}
