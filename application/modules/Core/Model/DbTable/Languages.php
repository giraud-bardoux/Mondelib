<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Languages.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_DbTable_Languages extends Engine_Db_Table
{ 
  public function isLanguageExist($language) {
    return $this->select()
              ->from($this->info('name'), 'language_id')
              ->where('code =?', $language)
              ->query()
              ->fetchColumn();
  }
  
  public function isEnabled($language) {
    return $this->select()
              ->from($this->info('name'), 'enabled')
              ->where('code =?', $language)
              ->query()
              ->fetchColumn();
  }
}
