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

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Plugin_Task_Storage extends Core_Plugin_Task_Abstract {

  public function execute() {
  
    $storageTable = Engine_Api::_()->getDbTable('files', 'storage');
    $results = $storageTable->fetchAll($storageTable->select()->from($storageTable->info('name'))->where('height IS NULL')->limit('200')->where('mime_major =?','image'));
    if(engine_count($results) > 0) {
      foreach($results as $result) {
        $path = $result->map();
        // Try to get image info
        if (function_exists('getimagesize') && ($imageinfo = getimagesize($path))) {
          $result->width = isset($imageinfo[0]) ? $imageinfo[0] : NULL;
          $result->height = isset($imageinfo[1]) ? $imageinfo[1] : NULL;
          $result->save();
        }else{
          $result->width = 0;
          $result->height = 0;
          $result->save();
        }
      }
    } else {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->query('DELETE FROM engine4_core_tasks WHERE `engine4_core_tasks`.`plugin` = "Core_Plugin_Task_Storage";');
    }
  }
}
