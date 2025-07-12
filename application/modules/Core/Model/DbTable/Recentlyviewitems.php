<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Recentlyviewitems.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
class Core_Model_DbTable_Recentlyviewitems extends Engine_Db_Table
{

  protected $_name = 'core_recentlyviewitems';
  protected $_rowClass = 'Core_Model_Recentlyviewitem';

  public function getResults($params = array())
  {
    $itemTable = Engine_Api::_()->getItemTable($params['type']);
    $itemTableName = $itemTable->info('name');
    $fieldName = current($itemTable->info("primary"));

    $name = $this->info('name');
    $viewer = Engine_Api::_()->user()->getViewer();

    $select = $this->select()
      ->setIntegrityCheck(false)
      ->from($name, array('*'))
      ->joinLeft($itemTableName, $itemTableName . ".$fieldName =  " . $name . '.resource_id', null)
      ->where($name . '.resource_type = ?', $params['type'])
      ->order($name . '.creation_date DESC')
      ->limit($params['limit']);

    if ($params['criteria'] == 'by_me') {
      $select->where($name . '.owner_id =?', $viewer->getIdentity());
    } else if ($params['criteria'] == 'by_myfriend') {
      /* friends array */
      $friendIds = $viewer->membership()->getMembershipsOfIds();
      if (engine_count($friendIds) == 0)
        return array();
      $select->where($name . ".owner_id IN ('" . implode(',', $friendIds) . "')");
    }

    if (!empty($params['paginator'])) {
      return Zend_Paginator::factory($select);
    }
    return $this->fetchAll($select);
  }

  public function insertRecentView($item)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!empty($viewer->getIdentity()) && !$item->getOwner()->isSelf($viewer) && !empty($item->getIdentity())) {
      Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_core_recentlyviewitems (resource_id, resource_type, owner_id, creation_date ) VALUES ("' . $item->getIdentity() . '", "' . $item->getType() . '","' . $viewer->getIdentity() . '", NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');

      if(isset($item->view_count)){
        $item->view_count++;
        $item->save();
      }
    }
  }
}