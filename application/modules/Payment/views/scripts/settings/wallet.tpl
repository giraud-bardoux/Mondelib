<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: wallet.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $currentCurrency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
$cryptoPaymentEnable = false;

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
      <div class="user_setting_global_form">
        <div class="global_form wallet_form_settings">
          <div>
            <h3><?php echo $this->translate('Wallet') ?></h3>
            <p class="p-0"><?php echo $this->translate("You can use this wallet balance for Membership subscription, Verification subscription, and any other payment activities on the website."); ?></p>
            <div class="wallet_form_settings_inner d-flex align-items-center justify-content-start gap-3">
              <div class="wallet_form_settings_left">
                <span class="d-block mb-2">
                  <?php echo $this->translate("Current balance"); ?>
                </span>
                <p class="p-0">
                  <?php echo $this->viewer()->wallet_amount ? Engine_Api::_()->payment()->getCurrencyPrice($this->viewer()->wallet_amount,'','','') : Engine_Api::_()->payment()->getCurrencyPrice('0.00','','',''); ?>
                </p>
              </div>
              <div class="wallet_form_settings_right d-flex align-items-center justify-content-end">
                 <button class="btn btn-primary" type="button" data-bs-toggle="modal"  data-bs-target="#wallet_form_payment_option">
                  <i class="fa-solid fa-money-bill-wave"></i> <?php echo $this->translate('Add Funds') ?>
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- Add Blance Modal Poup -->
        <div id="wallet_form_paymentoption">
          <form method="post" action="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'wallet', 'action' => 'process'),'default',true)) ?>" class="" enctype="application/x-www-form-urlencoded">
            <div class="modal fade wallet_form_payment_option" id="wallet_form_payment_option" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content position-relative">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5"><?php echo $this->translate("Add Fund"); ?></h1>
                    <a href="javascript:void(0)" type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></a>
                  </div>
                  <div class="modal-body wallet_form_payment_option_content">
                    <?php if(!empty($this->wallet)) { ?>
                      <?php $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency(Engine_Api::_()->payment()->getCurrentCurrency()); ?>
                      <?php $allowedGateways = json_decode($currencyData->gateways); ?>
                      <div class="form-elements">
                        <p class="_txt pb-0 mb-2">
                          <?php echo $this->translate("Enter the fund below to recharge your wallet."); ?> 
                        </p>
                        <div class="add_balance_section d-flex justify-content-end pb-1 gap-2">
                          <span><?php echo $currencyData->symbol ? $currencyData->symbol : Engine_Api::_()->payment()->getCurrentCurrency(); ?></span>
                          <input type="number" placeholder="0.00" id="price_wallet" name="price_wallet" min="1" min="100000" required />
                        </div>
                        <div class="wallet_form_payment_inner">
                          <div id="buttons-wrapper" class="form-wrapper d-flex justify-content-center flex-column">
                            
                            <?php foreach( $this->gateways as $gatewayInfo ):
                              $gateway = $gatewayInfo['gateway'];
                              $plugin = $gatewayInfo['plugin'];
                              $config = (array) $gateway['config'];
                              $first = ( !isset($first) ? true : false );
                              $gatewayObject = $gateway->getGateway();
                              $supportedCurrencies = $gatewayObject->getSupportedCurrencies();
                              if(!engine_in_array($currentCurrency,$supportedCurrencies))
                              continue;
                              if(engine_count($allowedGateways) == 0 && !engine_in_array($gateway->gateway_id , $allowedGateways))         continue;
                              if(engine_count($allowedGateways) > 0 && !engine_in_array($gateway->gateway_id , $allowedGateways))         continue;
                              if($gatewayInfo['plugin'] instanceof Engine_Payment_Gateway_Coinpayment){
                                $cryptoPaymentEnable = true;
                                $cryptoConfig = $config;
                              ?>
                              <input type="hidden" name="currency" id="crypto_currency" />
                              <?php 
                              }
                              ?>
                              <button class="btn btn-alt justify-content-center gap-2 mt-2" <?php echo $cryptoPaymentEnable ? "data-coinpayment='true'" : "" ?> type="button" name="execute" onclick="checkGatewayId(<?php echo $gateway->gateway_id ?>,this);">
                              <?php if(isset($config['icon']) && !empty($config['icon'])) { ?>
                                <?php $path = Engine_Api::_()->core()->getFileUrl($config['icon']); ?>
                                <img src="<?php echo $path; ?>" alt="img">
                              <?php } ?>
                              <?php echo $this->translate('Recharge with %1$s', $this->translate($gateway->title)) ?></button>
                              <button id="gatewayButton" type="submit" name="gatewayButton" style="display:none;"></button>
                            <?php endforeach; ?>
                            <input type="hidden" name="gateway_id" id="gateway_id" />
                            <input type="hidden" name="user_id" id="user_id" value="<?php echo $this->subject()->user_id; ?>" />
                          </div>
                        </div>  
                      </div>
                      <script type="text/javascript">
                        function checkGatewayId(gatewayId,obj) {
                          if(!scriptJquery("#price_wallet").val()){
                            return;
                          } 
                          scriptJquery('#gateway_id').val(gatewayId);
                          if(scriptJquery("#crypto_currency_select").children().length == 1){
                            cryptoPayment();
                            return;
                          }

                          scriptJquery(".coinpayment").show();
                          
                          if(scriptJquery(obj).attr("data-coinpayment")){
                            // show option
                            scriptJquery("#openCryptoPopup").trigger("click");
                            return;
                          }
                          scriptJquery('#gatewayButton').trigger('click');
                        }
                      </script>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if($cryptoPaymentEnable){ ?>
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

<script type="text/javascript">
  AttachEventListerSE('keypress', '#price_wallet', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevents form submission when Enter is pressed
    }
  });

  AttachEventListerSE('click touchend', '.modal_close_btn', function(){
    scriptJquery('#price_wallet').val('')
  });

  en4.core.runonce.add(function() {
    scriptJquery(scriptJquery('#wallet_form_paymentoption').html()).appendTo('#append-script-data');
    scriptJquery('#wallet_form_paymentoption').remove()
  });
</script>
