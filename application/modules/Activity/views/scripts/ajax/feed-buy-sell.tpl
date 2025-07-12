<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: feed-buy-sell.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<div class="activity_sellitem_popup clearfix ">
	<div class="activity_sellitem_popup_header">
  	<?php echo $this->translate("Item Details");?>
  </div>
  <div class="activity_sellitem_popup_content clearfix">
  	<div class="activity_sellitem_popup_right custom_scrollbar">
    	<div class="activity_sellitem_popup_right_inner clearfix">
        <div class="activity_sellitem_popup_right_cont clearfix">
          <div class="activity_sellitem_popup_owner clearfix">
            <div class="activity_sellitem_popup_owner_photo">
              <?php 
              $action = $this->action;
              $owner = Engine_Api::_()->getItem('user',$this->action->subject_id);
              echo $this->htmlLink($owner->getHref(), $this->itemPhoto($owner, 'thumb.icon', $owner->getTitle()), array()) ?>
            </div>
            <div class="activity_sellitem_popup_owner_info">
              <div class="activity_sellitem_popup_owner_name">
                <a href="<?php echo $owner->getHref(); ?>" class="font_color"><?php echo $owner->getTitle(); ?></a>
              </div>
              <div class="activity_sellitem_popup_time font_color_light">
                	<?php echo $this->timestamp($this->main_action->getTimeValue()) ?>
              </div>
            </div>
          </div>
          <div class="activity_sellitem_popup_item_info clearfix">
            <div class="activity_sellitem_popup_item_title"><?php echo $this->item->getTitle(); ?></div>
            <div class="activity_sellitem_popup_item_price">
              <?php echo Engine_Api::_()->payment()->getCurrencyPrice($this->item->price); ?>
            </div>
            <?php $locationBuySell = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type'=>'activity_buysell','resource_id'=>$this->item->getIdentity())) ?>
            <?php if($locationBuySell){ ?>            
              <div class="activity_sellitem_popup_item_location font_color_light">
                <i class="fas fa-map-marker-alt"></i>
                <span>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
                  <a href="<?php echo 'http://maps.google.com/?q='.$locationBuySell->venue; ?>" target='_blank' class="font_color_light"><?php echo $locationBuySell->venue; ?></a>
                <?php } ?>
                </span>
              </div>
            <?php } ?>
            <?php if($this->item->description){ ?>
            <div class="activity_sellitem_popup_item_des">
             <?php echo $this->viewMoreActivity($this->item->description); ?>
            </div>
            <?php } ?>
            
            <div class="activity_sellitem_popup_item_button">
            <?php if($this->item->buy && !$this->item->is_sold){ ?>
              <div>
                <a class="btn btn-primary" href="<?php echo $this->item->buy; ?>" target="_blank" ><i class="fa fa-shopping-cart"></i><?php echo $this->translate("Buy Now"); ?></a>
              </div>
              <?php } ?>
              <?php if($this->viewer()->getIdentity() != 0){ ?>
              <div>
            	<?php if(!$this->item->is_sold){ ?>
              <?php if($action->subject_id != $this->viewer()->getIdentity()){ ?>
                <button class="btn btn-alt" onClick="openSmoothBoxInUrl('/activity/ajax/message/action_id/<?php echo $action->getIdentity(); ?>');return false;"><i class="fa fa-comment"></i><?php echo $this->translate("Message Seller"); ?></button>
              <?php }else{ ?>
                <button class="btn btn-success mark_as_sold_buysell mark_as_sold_buysell_<?php echo $action->getIdentity(); ?>" data-sold="<?php echo $this->translate('Sold'); ?>" data-href="<?php echo $action->getIdentity(); ?>"><i class="fa fa-check"></i><?php echo $this->translate("Mark as Sold") ?></button>
              <?php } ?>
             <?php }else{ ?>
                <button><i class="fa fa-check"></i><?php echo $this->translate("Sold"); ?></button>
             <?php } ?>
             </div>
            <?php } ?>
            
            </div>
          </div>
        </div>
        <div class="activity_sellitem_popup_right_comments"> 
         <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('comment')){ ?>
            <div class="activity_feed  clearfix">
            	<ul class="feed"><?php echo $this->activity($action, array('noList' => true,'isOnThisDayPage'=>false), 'update',false);?></ul>
          	</div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="activity_sellitem_popup_photos">
    	<div class="activity_sellitem_popup_photos_strip">
				<div class="custom_scrollbar">
        <?php foreach( $action->getAttachments() as $attachment){ ?>
        	<a href="javascript:;" class="buysell_img_a"><img <?php  if($attachment->item->getIdentity() == $this->photo_id){ ?> class="selected" <?php } ?>src="<?php echo $attachment->item->getPhotoUrl('thumb.normalmain');  ?>" alt=""></a>
          <?php } ?>
      	</div>
      </div>
      <div class="activity_sellitem_popup_photo_container">
      	<div>
        	<div>
            <?php $photo = Engine_Api::_()->getItem('album_photo',$this->photo_id); ?>
            <?php if($photo) { ?>
              <img class="selected_image_buysell" src="<?php echo $photo->getPhotoUrl(); ?>" />
          	<?php } else { ?>
              <img class="selected_image_buysell" src="application/modules/Activity/externals/images/sell-popup.png" />
          	<?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
