<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Verificationrequests.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Verificationrequests extends Engine_Db_Table {

  public function isSentRequest($params = array()) {

    return $this->select()
            ->from($this->info('name'), 'verificationrequest_id')
            ->where('user_id = ?', $params['user_id'])
            ->query()
            ->fetchColumn();
  }
}
