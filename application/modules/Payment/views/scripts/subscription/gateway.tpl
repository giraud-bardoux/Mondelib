<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: gateway.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">  
    <?php if( $this->status == 'pending' ): // Check for pending status ?>
      Your subscription is pending payment. You will receive an email when the
      payment completes.
    <?php else: ?>
      <?php 
          $settings = Engine_Api::_()->getApi('settings', 'core'); 
          $currentCurrency = $settings->getSetting('payment.currency', 'USD');
      ?>
      <div class="payment_process_gateway">
        <form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>" enctype="application/x-www-form-urlencoded">
          <h3><?php echo $this->translate('Subscription Plan Confirmation') ?></h3>
          <div class="payment_process_gateway_content">  
            <?php if( $this->package->recurrence ): ?>
            <p class="form-description">
              <?php echo $this->translate('You have selected an account type that requires ' .
                'recurring subscription payments. You will be taken to a secure ' .
                'checkout area where you can setup your subscription. Remember to ' .
                'continue back to our site after your purchase to sign in to your ' .
                'account.') ?>
            </p>
            <?php endif; ?>
            <p>
              <?php $stripCheck = true; ?>
              <?php if( $this->package->recurrence ): ?>
                <?php $stripCheck = $this->package->isPackageRecurrence(); ?>
                <?php echo $this->translate('Please setup your subscription to continue:') ?>
              <?php else: ?>
                <?php echo $this->translate('Please pay a one-time fee to continue:') ?>
              <?php endif; ?>
              <?php echo $this->package->getPackageDescription() ?>
            </p>
            <div class="payment_process_gateway_buttons">
              <?php $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency(Engine_Api::_()->payment()->getCurrentCurrency()); 
              $allowedGateways = json_decode($currencyData->gateways);
              ?>
              <?php if(engine_count($allowedGateways) > 0) { ?>
                <?php foreach( $this->gateways as $gatewayInfo ):
                  $gateway = $gatewayInfo['gateway'];
                  $plugin = $gatewayInfo['plugin'];
                  $config = (array) $gateway['config'];
                  $gatewayObject = $gateway->getGateway();
                  $supportedCurrencies = $gatewayObject->getSupportedCurrencies();
                  if(!engine_in_array($currentCurrency,$supportedCurrencies))
                    continue;
                  if(!engine_in_array($gateway->gateway_id , $allowedGateways))
                    continue;
                    
                  if($gateway->plugin == 'Payment_Plugin_Gateway_Stripe' && !$stripCheck) {
                    continue;
                  }
                  ?>
                  <div class="text-center">
                    <button type="submit" name="execute" onclick="scriptJquery('#gateway_id').attr('value', '<?php echo $gateway->gateway_id ?>')">
                      <?php if(isset($config['icon']) && !empty($config['icon'])) { ?>
                        <?php $path = Engine_Api::_()->core()->getFileUrl($config['icon']); ?>
                        <img src="<?php echo $path; ?>" alt="img">
                      <?php } ?>
                      <span><?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?></span>
                    </button>
                  </div>
                <?php endforeach; ?>
              <?php } ?>
            </div>
          </div>
          <input type="hidden" name="gateway_id" id="gateway_id" value="" />
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
