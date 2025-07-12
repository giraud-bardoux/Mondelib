<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: States.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_States extends Engine_Db_Table {

  protected $_rowClass = 'Core_Model_State';
  
  function getCount($country_id){
    $select = $this->select()->where('country_id =?',$country_id);
    return engine_count($this->fetchAll($select));
  }
  
  function getStates($params = array()){
    $select = $this->select()->where('enabled =?',1)->order('name ASC')->where('country_id =?',$params['country_id']);
    return $this->fetchAll($select);
  }
}
