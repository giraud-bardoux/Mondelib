<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Controller.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Widget_TipController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    $coreApi = Engine_Api::_()->core();
		$setting = Engine_Api::_()->getApi('settings', 'core');
    $androidEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesandoidapp');
    $iosEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesiosapp');
    $moduleEnable = $androidEnable || $iosEnable;
		if (!$setting->getSetting('sesapi.tip.enable', 1) || !$moduleEnable)
      return $this->setNoRender();
    
    $this->view->title = $setting->getSetting('sesapi.tip.title', '');
    $this->view->description = $setting->getSetting('sesapi.tip.description', '');
    if($iosEnable)
    $this->view->iosid = $setting->getSetting('sesapi.tip.iosid', '');
    if($androidEnable)
    $this->view->androidid = $setting->getSetting('sesapi.tip.androidid', '');
    $this->view->buttoninstall = $setting->getSetting('sesapi.tip.buttoninstall', 'INSTALL');
    $this->view->daysHidden = $setting->getSetting('sesapi.tip.daysHidden', 15);
    $this->view->daysReminder = $setting->getSetting('sesapi.tip.daysReminder', 90);
    $this->view->image = $setting->getSetting('sesapi.tip.image', '');
    
  }

}
