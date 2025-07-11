<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Currencies.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Model_DbTable_Currencies extends Engine_Db_Table {

  protected $_rowClass = 'Payment_Model_Currency';
  
  public function getCurrencies($params = array()) {
  
    $tableName = $this->info('name');
    $select = $this->select();

    if(isset($params['enabled']) && !empty($params['enabled']))
      $select->where('enabled =?', $params['enabled']);
    
    $select->order('order ASC');
    
    return $this->fetchAll($select);
  }
  
  public function getCurrency($code) {
  
    $tableName = $this->info('name');
    $select = $this->select()
                  ->where('code =?', $code);

    return $this->fetchRow($select);
  }
}
