<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Sitemaps.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Sitemaps extends Engine_Db_Table {

  public function hasType($params = array()) {

    return $this->select()
          ->from($this->info('name'), array('sitemap_id'))
          ->where('resource_type =?', $params['resource_type'])
          ->query()
          ->fetchColumn();
  }
}
