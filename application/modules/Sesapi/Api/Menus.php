<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Menus.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Api_Menus extends Core_Api_Abstract
{
  public function getMenus($params = array()) {
    return Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation($params['menu']);
  }
  
  public function getIconsMenu($menuName) {

    $coreMenuItemsTable = Engine_Api::_()->getDbTable('menuitems', 'core');
    $coreMenuItemsTableName = $coreMenuItemsTable->info('name');
    return $coreMenuItemsTable->select()
                    ->from($coreMenuItemsTableName, 'file_id')
                    ->where('name =?', $menuName)
                    ->query()
                    ->fetchColumn();
  }
}
