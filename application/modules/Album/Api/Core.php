<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Api_Core extends Core_Api_Abstract
{

	/**
	 * Get Flush Photo Count
	 *
	 * @return photocount
	 */
	public function getFlushPhotoData() {
		$GetTableNamePhoto = Engine_Api::_()->getItemTable('photo');
		$tableNamePhoto = $GetTableNamePhoto->info('name');
		$select = $GetTableNamePhoto->select()->from($tableNamePhoto, new Zend_Db_Expr('COUNT(photo_id) as total'))->where('album_id =?', 0)->where('DATE(NOW()) != DATE(creation_date)');
		$data = $GetTableNamePhoto->fetchRow($select);
		return (int) $data->total;
	}
	
	/**
	 * Get Flush Photo Data
	 *
	 * @return photos
	 */
	public function getFlushPhotosData() {
	
		$GetTableNamePhoto = Engine_Api::_()->getItemTable('photo');
		$tableNamePhoto = $GetTableNamePhoto->info('name');
		$select = $GetTableNamePhoto->select()->from($tableNamePhoto)->where('album_id =?', 0)->where('DATE(NOW()) != DATE(creation_date)');
		return $GetTableNamePhoto->fetchAll($select);
	}
}
