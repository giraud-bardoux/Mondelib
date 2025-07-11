<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: finish.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <!-- <form class="form_submit_ajax" method="get" action="<?php //echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'wallet', 'user_id' => $this->user_id), 'default', true)) ?>" enctype="application/x-www-form-urlencoded"> -->
      <?php if( $this->status == 'pending' ): ?>
        <?php if($this->signup) { ?>
          <div class="payment_process_message payment_process_message_wait text-center">
            <i class="payment_process_message_icon fas fa-hourglass-start"></i>
            <p class="payment_process_message_title"><?php echo $this->translate('Payment Pending') ?></p>
            <p class="payment_process_message_des">
            <?php echo $this->translate('Thank you for submitting your payment. Your payment is currently pending - your account will be activated when we are notified that the payment has completed successfully. Please return to our login page when you receive an email notifying you that the payment has completed.') ?>
            </p>
            <p id="buttons-wrapper" class="payment_process_message_btn">
              <a href="<?php echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'index', 'user_id' => $this->user_id), 'default', true)) ?>"  type="submit" class="btn btn-primary"><?php echo $this->translate('Continue') ?></a>
            </p>   
          </div>
        <?php } else { ?>
          <div class="payment_process_message payment_process_message_wait text-center">
            <i class="payment_process_message_icon fas fa-hourglass-start"></i>
            <p class="payment_process_message_title"><?php echo $this->translate('Payment Pending') ?></p>
            <p class="payment_process_message_des">
              <?php echo $this->translate("Thank you for your payment! We will review it promptly. You’ll receive a notification once it’s approved, and your wallet will be recharged.") ?>
            </p>
            <p id="buttons-wrapper" class="payment_process_message_btn">
              <a href="<?php echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'wallet', 'user_id' => $this->user_id), 'default', true)) ?>"  type="submit" class="btn btn-primary"><?php echo $this->translate('Visit Wallet') ?></a>
            </p>   
          </div>
        <?php } ?>
      <?php elseif( $this->status == 'active' ): ?>
        <?php if($this->signup) { ?>
          <div class="payment_process_message payment_process_message_success text-center">
            <i class="payment_process_message_icon fas fa-check-circle"></i>
            <p class="payment_process_message_title"><?php echo $this->translate('Payment Completed') ?></p>
            <p><?php echo $this->translate('Thank you! Your payment has completed successfully.') ?></p>
            <p id="buttons-wrapper" class="payment_process_message_btn">
              <a href="<?php echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'index', 'user_id' => $this->user_id), 'default', true)) ?>" type="submit" class="btn btn-primary"><?php echo $this->translate('Continue') ?></a>
            </p>
          </div>
        <?php } else { ?>
          <div class="payment_process_message payment_process_message_success text-center">
            <i class="payment_process_message_icon fas fa-check-circle"></i>
            <p class="payment_process_message_title"><?php echo $this->translate('Payment Completed') ?></p>
            <p><?php echo $this->translate('Thank you! Your payment has been completed successfully, and your wallet has been recharged.') ?></p>
            <p id="buttons-wrapper" class="payment_process_message_btn">
              <a href="<?php echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'wallet', 'user_id' => $this->user_id), 'default', true)) ?>" type="submit" class="btn btn-primary"><?php echo $this->translate('Visit Wallet') ?></a>
            </p>
          </div>
        <?php } ?>
      <?php else: //if( $this->status == 'failed' ): ?>
        <div class="payment_process_message payment_process_message_fail text-center">
          <i class="payment_process_message_icon fas fa-exclamation-circle"></i>
          <p class="payment_process_message_title"><?php echo $this->translate('Payment Failed') ?></p>
          <p class="payment_process_message_des">
            <?php if( empty($this->error) ): ?>
              <?php echo $this->translate('Our payment processor has notified us that your payment could not be completed successfully. We suggest that you try again with another credit card or funding source.') ?>
              <?php else: ?>
                <?php echo $this->translate($this->error) ?>
              <?php endif; ?>
          </p>
          <p id="buttons-wrapper" class="payment_process_message_btn">
            <a href="<?php echo $this->escape($this->url(array("module"=> "payment", "controller" => "settings",'action' => 'wallet', 'user_id' => $this->user_id), 'default', true)) ?>"  type="submit" class="btn btn-primary"><?php echo $this->translate('Visit Wallet') ?></a>
          </p>
        </div>
      <?php endif; ?>
    <!-- </form> -->
  </div>
</div>
