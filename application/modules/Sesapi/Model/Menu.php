<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Menu.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Model_Menu extends Core_Model_Item_Abstract {
  public function getPhotoUrl($type = null) {
    $file = Engine_Api::_()->getItem('storage_file', $this->file_id);
    if ($file)
      return $file->map();
    else if($this->module_name != "" && file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "dashboardicons" . DIRECTORY_SEPARATOR.$this->module_name.'.png')){
      return 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "dashboardicons" . DIRECTORY_SEPARATOR.$this->module_name.'.png';
    }else
     return '';
  }
  public function setPhoto($file){
    $storage = Engine_Api::_()->getItemTable('storage_file');
    $filename = $storage->createFile($file, array(
        'parent_id' => $this->getIdentity(),
        'parent_type' => $this->getType(),
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
    ));
    $this->file_id = $filename->file_id;
    $this->save();
    return $this->file_id;
 }
}
