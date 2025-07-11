<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: finish.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <form method="get" action="<?php echo $this->escape($this->url) ?>" enctype="application/x-www-form-urlencoded">
      <?php if( $this->status == 'pending' ): ?>
        <div class="payment_process_message payment_process_message_wait text-center">
          <i class="payment_process_message_icon fas fa-hourglass-start"></i>
          <p class="payment_process_message_title"><?php echo $this->translate('Payment Pending') ?></p>
          <p class="payment_process_message_des">
            <?php echo $this->translate('Thank you for submitting your ' .
              'payment. Your payment is currently pending - your account ' .
              'will be activated when we are notified that the payment has ' .
              'completed successfully. Please return to our login page ' .
              'when you receive an email notifying you that the payment ' .
              'has completed.') ?>
          </p>
          <p id="buttons-wrapper" class="payment_process_message_btn">
            <button type="submit"><?php echo $this->translate('Back to Home') ?></button>
          </p>   
        </div>
      <?php elseif( $this->status == 'active' ): ?>
        <div class="payment_process_message payment_process_message_success text-center">
          <i class="payment_process_message_icon fas fa-check-circle"></i>
          <p class="payment_process_message_title"><?php echo $this->translate('Payment Completed') ?></p>
          <p><?php echo $this->translate('Thank you! Your payment has completed successfully.') ?></p>
          <p id="buttons-wrapper" class="payment_process_message_btn">
            <button type="submit"><?php echo $this->translate('Continue') ?></button>
          </p>
        </div>
      <?php else: //if( $this->status == 'failed' ): ?>
        <div class="payment_process_message payment_process_message_fail text-center">
          <i class="payment_process_message_icon fas fa-exclamation-circle"></i>
          <p class="payment_process_message_title"><?php echo $this->translate('Payment Failed') ?></p>
          <p class="payment_process_message_des">
            <?php if( empty($this->error) ): ?>
              <?php echo $this->translate('Our payment processor has notified ' .
                'us that your payment could not be completed successfully. ' .
                'We suggest that you try again with another credit card ' .
                'or funding source.') ?>
            <?php else: ?>
              <?php echo $this->translate($this->error) ?>
            <?php endif; ?>
          </p>
          <p id="buttons-wrapper" class="payment_process_message_btn">
            <button type="submit"><?php echo $this->translate('Back to Home') ?></button>
          </p>     
        </div>  
      <?php endif; ?>
    </form>
  </div>
</div>