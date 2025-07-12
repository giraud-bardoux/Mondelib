<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div>
      <div>
        <?php if( $this->isAdmin ): ?>
          <div class="tip">
            <span>
            <?php echo $this->translate('Subscriptions are not required for administrators and moderators.') ?>
            </span>
          </div>
        <?php else: ?>
          <?php if( $this->currentPackage && $this->currentSubscription ): ?>
            <div class="my_subscription_plan_head">
              <h3><?php echo $this->translate('My Subscription') ?></h3>
              <?php if(engine_count($this->packages) > 1) { ?>
                <div>
                  <a class="btn" href="<?php echo $this->url(array('module' => "payment", 'controller' => "subscription", 'action' => "choose"), 'default', true); ?>"><?php echo $this->translate("Change Plan"); ?></a>
                </div>
              <?php } ?>
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
                    <?php elseif(!$this->currentPackage->isOneTime() && $this->currentPackage->duration_type != 'forever'): ?>
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
          <?php else: ?>
            <div class="my_subscription_plan_head">
              <h3><?php echo $this->translate('My Subscription') ?></h3>
              <div>
                <a class="btn" href="<?php echo $this->url(array('module' => "payment", 'controller' => "subscription", 'action' => "choose"), 'default', true); ?>"><?php echo $this->translate("Change Plan"); ?></a>
              </div>
            </div>
            <div class="tip">
              <span><?php echo $this->translate('You have not yet selected a subscription plan. Please <a href="'.$this->url(array('module' => "payment", 'controller' => "subscription", 'action' => "choose"), 'default', true).'">click here</a> to choose your plan.'); ?></span>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
