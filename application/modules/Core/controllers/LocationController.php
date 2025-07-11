<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: LocationController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_LocationController extends Core_Controller_Action_Standard {

	public function getDirectionAction() {
		$resouce_type = $this->_getParam('resource_type');
		$resource_id = $this->_getParam('resource_id',false);
		
		if(!$resource_id || !$resouce_type)
			return $this->_forward('requireauth', 'error', 'core');
			
		$latLng = Engine_Api::_()->getDbtable('locations', 'core')->getLocationData(array('resource_type' => $resouce_type, "resource_id" => $resource_id));

		$this->view->item = Engine_Api::_()->getItem($resouce_type, $resource_id);
		
		if(!empty($latLng)) {
      $this->view->location = $latLng;
    } else {
      $this->view->location = '';
    }
    
    if(isset($latLng) && !empty($latLng)) {
      $this->view->lat = $latLng->lat;
      $this->view->lng = $latLng->lng;
		} else {
      $this->view->lat = 0;
      $this->view->lng = 0;
		}
	}
}
