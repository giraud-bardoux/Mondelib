<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: choose.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="generic_layout_container layout_main">
	<div class="generic_layout_container layout_middle">
  	<div class="generic_layout_container layout_core_content">
      <?php //echo $this->form->render($this) ?>
      <?php if( $this->currentPackage && $this->currentSubscription ): ?>
        <div class="my_subscription_plan_head">
          <h3><?php echo $this->translate('Current Plan') ?></h3>
          <div>
            <a class="btn" href="<?php echo $this->url(array("module" => "payment", "controller" => "settings", "action" => "index"), 'default', true); ?>"><?php echo $this->translate("Go to Membership Subscription"); ?></a>
          </div>
        </div>    
        <div class="plan_active_details">
          <div class="plan_active_details_inner">
            <div class="plan_active_details_inner_title">
              <h5><?php echo $this->currentPackage->title; ?></h5>
            </div>
            <ul>
              <li>
                <span>
                  <?php echo $this->translate("Started On"); ?>
                </span>
                <h6>
                  <?php echo $this->currentSubscription->creation_date; ?>
                </h6>
              </li>
              <li>
                <span>
                  <?php echo $this->translate("Price"); ?>
                </span>
                <h6>
                  <?php echo $this->currentPackage->getPackageDescription(); ?>
                </h6>
              </li>
              <li>
                <span>
                  <?php if($this->currentPackage->hasDuration()): ?>
                  <?php echo $this->translate('Plan Expiry Date: '); ?>  
                </span>
                <h6>
                  <?php echo date('Y-m-d',strtotime($this->currentSubscription->expiration_date)); ?>
                </h6>
              </li>
              <li>
                <?php elseif(!$this->currentPackage->isOneTime()  && $this->currentPackage->duration_type != 'forever'): ?>
                  <span>
                    <?php echo $this->translate('Next Payment Date: '); ?>
                  </span>
                  <h6>
                    <?php echo $this->currentSubscription->expiration_date ? date('Y-m-d',strtotime($this->currentSubscription->expiration_date)) : $this->translate("Forever"); ?>
                  </h6>
                <?php endif; ?>  
              </li>
            </ul>
          </div>
        </div>
      <?php elseif($this->user->getIdentity() && !$_SESSION['User_Plugin_Signup_Account']): ?>
        <div class="my_subscription_plan_head" style="border-bottom-width:1px;padding-bottom:10px;margin-bottom:50px;">
          <h3><?php echo $this->translate('Current Plan') ?></h3>
          <div>
            <a class="btn" href="<?php echo $this->url(array("module" => "payment", "controller" => "settings", "action" => "index"), 'default', true); ?>"><?php echo $this->translate("Go to Membership Subscription"); ?></a>
          </div>
        </div>
      <?php endif; ?>
      <?php if($this->packages > 0) { ?>
        <?php include APPLICATION_PATH .  '/application/modules/Payment/views/scripts/_signupSubscription.tpl';?>
      <?php } ?>
    </div>
  </div>
</div>
