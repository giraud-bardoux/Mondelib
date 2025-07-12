<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Languages.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

class Core_Api_Languages extends Core_Api_Abstract {

  public function getLanguages($params = array()) {
  
    $table = Engine_Api::_()->getDbTable('languages', 'core');
    $tableName = $table->info('name');
    
    $select = $table->select()
                    ->from($tableName);
    if(!isset($params['admin']) && empty($params['admin'])) {
      $select->where('enabled = ?', 1);
    }
    $select->order('order ASC');
    
    $languages = $table->fetchAll($select);
    $localeMultiOptions = array();
    foreach ($languages as $language) {
      $localeMultiOptions[$language->code] = $language->name;
    }
    return $localeMultiOptions;
  }
}
