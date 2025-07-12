<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Integratemodules.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
class Core_Model_DbTable_Integratemodules extends Engine_Db_Table {

  protected $_rowClass = 'Core_Model_Integratemodule';

  public function integrateModules($params = array()) {

    $select = $this->select()
                ->from($this->info('name'));

    if (isset($params['integratemodule_id']))
      $select = $select->where('integratemodule_id = ?', $params['integratemodule_id']);
      
    if (isset($params['content_type']))
      $select = $select->where('content_type = ?', $params['content_type']);

    if (isset($params['module_name']))
      $select = $select->where('module_name = ?', $params['module_name']);
      
    if (isset($params['content_id']))
      $select = $select->where('content_id = ?', $params['content_id']);

    if (isset($params['enabled']))
      $select = $select->where('enabled = ?', $params['enabled']);
    
    return $select->query()->fetchAll();
  }
}