<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Locations.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Locations extends Engine_Db_Table {

  protected $_name = 'core_locations';
  protected $_rowClass = 'Core_Model_Location';

  public function getLocationData($params = array()) {
    $name = $this->info('name');
    $select = $this->select()
            ->from($name)
            ->where('resource_id = ?', $params['resource_id'])
            ->where('resource_type = ?', $params['resource_type']);
    return $this->fetchRow($select);
  }
}
