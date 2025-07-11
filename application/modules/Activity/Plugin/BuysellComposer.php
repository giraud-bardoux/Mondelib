<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: BuysellComposer.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Plugin_BuysellComposer extends Core_Plugin_Abstract {

  public function onAttachBuysell($data,$location = '',$postData) {
  
    $table = Engine_Api::_()->getDbTable('buysells','activity');
    try {
      $buysell = $table->createRow();
      $viewer = Engine_Api::_()->user()->getViewer();
      $buysell->user_id = $viewer->getIdentity();
      $buysell->title = $postData['buysell-title'];
      $buysell->buy = $postData['buy-url'];
      $buysell->description = $postData['buysell-description'];
      $buysell->price = $postData['buysell-price'];
      $buysell->currency = Engine_Api::_()->payment()->defaultCurrency();
      $buysell->save();
      //location in post
      if(!empty($postData['buysell-location']) && !empty($postData['activitybuyselllng']) && !empty($postData['activitybuyselllat'])){
         $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
         $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $buysell->getIdentity() . '", "' . $postData['activitybuyselllat'] . '","' . $postData['activitybuyselllng'] . '","activity_buysell","'.$postData['buysell-location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $postData['activitybuyselllat'] . '" , lng = "' . $postData['activitybuyselllng'] . '",venue="'.$postData['buysell-location'].'"');
      }
      $buysell->save();
    } catch( Exception $e ) {
      return;
    }
    return $buysell;
  }
}
