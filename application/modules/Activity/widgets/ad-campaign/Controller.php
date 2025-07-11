<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Widget_AdCampaignController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Get campaign
    if( !($id = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adcampaignid','0')) ||
        !($campaign = Engine_Api::_()->getItem('core_adcampaign', $id)) ) {
      return $this->setNoRender();
    }

    // Check limits, start, and expire
    if( !$campaign->isActive() ) {
      return $this->setNoRender();
    }
    
    // Get viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$campaign->isAllowedToView($viewer) ) {
      return $this->setNoRender();
    }

    // Get ad
    $table = Engine_Api::_()->getDbtable('ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $id)->order('RAND()');
    $ad =  $table->fetchRow($select);
    if( !($ad) ) {
      return $this->setNoRender();
    }
    $this->getElement()->removeDecorator('Container');
    // Okay
    $campaign->views++;
    $campaign->save();
    
    $ad->views++;
    $ad->save();

    $this->view->campaign = $campaign;
    $this->view->ad = $ad;
  }
}
