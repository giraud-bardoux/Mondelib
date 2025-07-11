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
<?php $cryptoPaymentEnable = false;
$iscryptoPaymentEnable = false; ?>
<script type="text/javascript">
  var fromCryptoPayment = false;
</script>
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
        <form method="post" id="subscription_payment" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>" enctype="application/x-www-form-urlencoded">
          <h3><?php echo $this->translate('Pay for Access') ?></h3>
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
                  $cryptoPaymentEnable = false;
                  if($gatewayInfo['plugin'] instanceof Engine_Payment_Gateway_Coinpayment){
                    $cryptoPaymentEnable = true;
                    $iscryptoPaymentEnable = true;
                    $cryptoConfig = $config;
                  ?>
                  <input type="hidden" name="currency" id="crypto_currency" />
                  <?php 
                  }
                  ?>
                  <div class="text-center">
                    <button type="<?php echo $cryptoPaymentEnable ? "button" : "submit" ?>" id="submit_subscription<?php echo $cryptoPaymentEnable ? "_crypto" : "" ?>" name="execute" <?php echo $cryptoPaymentEnable ? "data-coinpayment='true'" : "" ?> onclick="scriptJquery('#gateway_id').attr('value', '<?php echo $gateway->gateway_id ?>')">
                      <?php if(isset($config['icon']) && !empty($config['icon'])) { ?>
                        <?php $path = Engine_Api::_()->core()->getFileUrl($config['icon']); ?>
                        <img src="<?php echo $path; ?>" alt="img">
                      <?php } ?>
                      <span><?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?></span>
                    </button>
                  </div>
                <?php endforeach; ?>
                <button id="gatewayButton" type="submit" name="gatewayButton" style="display:none;"></button>
                <script type="text/javascript">
                  scriptJquery(document).on("click",'#submit_subscription_crypto',function(e){
                    if(fromCryptoPayment){
                      return;
                    }
                      if(scriptJquery("#crypto_currency_select").children().length == 1){
                        cryptoPayment();
                        return;
                      }
                      e.preventDefault();
                      scriptJquery("#openCryptoPopup").trigger("click");
                      return;
                  })
                 
                </script>

              <?php } ?>
            </div>
          </div>
          <input type="hidden" name="gateway_id" id="gateway_id" value="" />
          <input type="hidden" name="user_id" id="user_id" value="<?php echo $this->user_id; ?>" />
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if($iscryptoPaymentEnable){ ?>
  <button style="display:none;" id="openCryptoPopup"  type="button" data-bs-toggle="modal"  data-bs-target="#cryptoCurrency"></button>
  <div class="modal fade cryptoCurrency" id="cryptoCurrency" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content position-relative">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?php echo $this->translate("Select Crypto Currency"); ?></h1>
          <a href="javascript:void(0)" type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></a>
        </div>
        <div class="modal-body cryptoCurrency_content">
            <div class="form-elements">
              <p class="_txt pb-0 mb-2">
                <?php echo $this->translate("Please select a crypto currency in which you want to make the payment."); ?> 
              </p>
              <div class="pb-1 gap-2">
                <select name="currency" id="crypto_currency_select" class="w-100">
                  <?php 
                  $coins = Engine_Api::_()->coinpayments()->crypto();
                  foreach($cryptoConfig['coin'] as $currency){ ?>
                    <option value="<?php echo $currency ?>"><?php echo $coins[$currency] ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="wallet_form_payment_inner">
                <div class="form-wrapper d-flex justify-content-center flex-column">
                    <button class="btn btn-primary justify-content-center gap-2 mt-2" type="button" name="execute" onclick="cryptoPayment();"><?php echo $this->translate("Continue") ?></button>                  
                </div>
              </div>  
            </div>
            <script type="text/javascript">
              function cryptoPayment(){
                fromCryptoPayment = true;
                let currency = scriptJquery("#crypto_currency_select").val();
                scriptJquery("#crypto_currency").val(currency);
                scriptJquery('#gatewayButton').trigger('click');
              }
            </script>
        </div>
      </div>
    </div>
  </div>
<?php } ?>