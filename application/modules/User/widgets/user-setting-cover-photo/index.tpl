<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<div class="user_setting_cover">
  <div class="user_setting_cover_img" style="background-image:url('./application/modules/User/externals/images/user_bg_pattren.png');"></div>
  <div class="user_setting_cover_content">
    <div class="user_setting_cover_info_photo">
      <?php echo $this->htmlLink($this->subject()->getHref(), $this->itemBackgroundPhoto($this->subject(), 'thumb.profile')) ?>
    </div>
    <div class="user_setting_cover_info">
      <div class="user_setting_cover_member_info">
        <?php if($this->subject()) { ?>
          <h3>
            <a href="<?php echo $this->subject()->getHref(); ?>" class="font_color"><?php echo $this->subject()->getTitle() ?></a>
          </h3>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1)) { ?>
          <span class="username"><?php echo '@'.$this->subject()->username;?></span>
        <?php } ?>
      </div>
      <?php if(!$this->showPaymentInfo && $this->currentSubscription && $this->currentPackage) { ?>
        <div class="user_setting_cover_plan_info">
          <div class="_title"><?php echo $this->translate($this->currentPackage->title); ?></div>
          <div class="_details font_small font_color_light">
            <span><?php echo $this->currentPackage->getPackageDescription(); ?></span>
            <?php if($this->currentPackage->hasDuration()): ?>
              <span><?php echo $this->translate('Plan Expiry Date: '); ?><?php echo date('Y-m-d',strtotime($this->currentSubscription->expiration_date)); ?></span>
            <?php elseif(!$this->currentPackage->isOneTime() && $this->currentPackage->duration_type != 'forever'): ?>
              <span><?php echo $this->translate('Next Payment Date: '); ?><?php echo $this->currentSubscription->expiration_date ? date('Y-m-d',strtotime($this->currentSubscription->expiration_date)) : $this->translate("Forever"); ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php } ?>
      <div class="user_setting_cover_member_btn">
        <a href="<?php echo  $this->subject()->getHref(); ?>" class="btn btn-primary"> 
          <i class="fas fa-user"></i> 
          <span><?php echo $this->translate("View Profile"); ?></span>
        </a>
      </div> 
    </div>
  </div>
</div>
