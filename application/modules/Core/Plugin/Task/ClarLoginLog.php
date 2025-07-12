<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ClarLoginLog.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Plugin_Task_ClarLoginLog extends Core_Plugin_Task_Abstract {

  public function execute() {
  
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.enableloginlogs', '1')) {
			$db = Engine_Db_Table::getDefaultAdapter();
			$db->query("TRUNCATE TABLE `engine4_user_logins`;");
		}
  }
}
