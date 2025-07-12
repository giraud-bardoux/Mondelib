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
<?php $isSentRequest = Engine_Api::_()->getDbTable('verificationrequests', 'user')->isSentRequest(array('user_id' => $this->user->getIdentity())); ?>

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
        <?php if( $this->isAdmin ): ?>
          <div class="tip">
            <span><?php echo $this->translate('Verifications Requests are not required for administrators and moderators.') ?></span>
          </div>
        <?php endif; ?>
        <?php if( !$this->isAdmin ): ?>
          <form method="get" action="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'verification', 'action' => 'process'),'default',true)) ?>" class="global_form verification_form_settings" enctype="application/x-www-form-urlencoded">
            <div>
              <div>
                <?php if($this->verified == 2) { ?>
                  <h3><?php echo $this->translate('Verification Settings') ?></h3>
                <?php } else { ?>
                  <h3><?php echo $this->translate('Verification Subscription') ?></h3>
                <?php } ?>
                <?php if(!empty($this->verified) && $this->user->is_verified) { ?>
                  <p class="success_msg">
                    <span><?php echo $this->translate('Congratulations, you are a verified member of this site.') ?></span>
                  </p>
                <?php } ?>

                <?php if(!empty($this->verified) && empty($this->user->is_verified)) { ?>
                  <?php if(empty($isSentRequest) && $this->verified == 2) { ?>
                      <p><?php echo $this->translate("At present, your membership on this site is not verified. To initiate the verification process for your profile, please click the 'Request Verification' button below."); ?></p>
                  <?php } else if($this->verified == 4) { ?>
                    <?php if(empty($this->subscription)) { ?>
                      <p class="pb-0"><?php echo $this->translate("At present, your membership on this site is not verified. To initiate the verification process for your profile, please make the payment by click on button below."); ?></p>
                    <?php } else if(!empty($this->subscription) && empty($this->user->is_verified)) { ?>
                      <p class="error_msg"><?php echo $this->translate("Your user profile verification is currently suspended."); ?></p>
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
                
                <?php if(!empty($this->verified) && $this->verified == 4) { ?>
                  <?php if(!empty($this->price_verified) && empty($this->subscription) && empty($this->user->is_verified)) { ?>
                    <div class="form-elements">
                      <p class="info_msg">
                        <?php echo $this->translate("Verification Fees: %s", $this->package->getPackageDescription()); ?> 
                      </p>

                      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) { ?>
                        <?php if($this->price_verified > $this->user->wallet_amount) { ?>
                          <div class="tip">
                            <span><?php echo $this->translate("You don't have enough balance for verification subscription, please first recharge your "); ?><a href="<?php echo $this->url(array("module" => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true); ?>" onclick="showReloadPopup();" target="_blank"><?php echo $this->translate(" wallet"); ?>.</a></span>
                          </div>
                          <!-- <div id="buttons-wrapper" class="form-wrapper mb-0 pb-0">
                            <a href="javascript:;" class="btn btn-primary"><?php //echo $this->translate("Pay By Wallet"); ?></a>
                          </div> -->
                        <?php } else { ?>
                          <div id="buttons-wrapper" class="form-wrapper mb-0 pb-0">
                            <a href="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'verification', 'action' => 'complete', 'user_id' => $this->subject()->user_id),'default',true)) ?>" class="btn btn-primary ajaxPrevent" data-bs-toggle="modal" data-bs-target="#wallet_modal"><?php echo $this->translate("Pay By Wallet"); ?></a>
                          </div>
                        <?php } ?>
                      <?php } ?>
                    </div>
                  <?php } ?>

                  <?php if(!empty($this->subscription) && !empty($this->user->is_verified)) { ?>
                    <?php 
                      $verificationpackage = Engine_Api::_()->getItem($this->subscription->resource_type, $this->subscription->resource_id);
                      $desc = $verificationpackage->getPackageDescription();
                    ?>
                    <p class="info_msg"><?php echo $this->translate('You are currently paying: %1$s', '<strong>' . $desc . '</strong>') ?></p>
                    <?php if(!$verificationpackage->isOneTime()) { ?>
                      <p class="p-0">
                        <?php echo $this->translate('If you would like to cancel your verification subscription, please click on Cancel button below.') ?>
                      </p>
                      <div class="form-elements">
                        <a title="<?php echo $this->translate("Cancel Verification") ?>" href="<?php echo $this->url(array('module'=> 'user','controller' => 'verification', 'action'=>'cancel','subscription_id' => $this->subscription->subscription_id), 'default', true); ?>" class="smoothbox cancel_btn btn btn-primary"><?php echo $this->translate("Cancel"); ?></a>
                      </div>
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
                <?php if(!empty($this->verified) && $this->verified == 2 && empty($this->user->is_verified)) { ?>
                  <?php if(!$isSentRequest && empty($this->user->is_verified)) { ?>
                    <div>
                      <a class="smoothbox btn btn-primary" href="<?php echo $this->escape($this->url(array('module'=> 'user', 'controller' => 'verification', 'action' => 'send-verification-request', 'user_id' => $this->user->getIdentity()),'default',true)) ?>"><?php echo $this->translate("Request Verification"); ?></a>
                    </div>
                  <?php } else if(!empty($isSentRequest) && empty($this->user->is_verified)) { ?>
                    <p><?php echo $this->translate("Your verification request is being processed. To cancel the request, click 'Cancel Request' below.") ?></p>
                    <p><a class="smoothbox btn btn-primary" href="<?php echo $this->escape($this->url(array('module'=> 'user', 'controller' => 'verification', 'action' => 'cancel-verification-request', 'user_id' => $this->user->getIdentity(), 'verificationrequest_id' => $isSentRequest),'default',true)) ?>"><?php echo $this->translate("Cancel Request"); ?></a></p>
                  <?php } ?>
                <?php } ?>

              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- wallet Modal Poup -->
<div class="modal fade wallet_modal" id="wallet_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content position-relative" id="pay_wallet_form">
      <form action="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'verification', 'action' => 'complete', 'user_id' => $this->subject()->user_id),'default',true)) ?>" method="post" id="wallet_payment" enctype="multipart/form-data">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?php echo $this->translate("Verification Subscription"); ?></h1>
        </div>
        <div class="modal-body">
          <p class="mb-3"><?php echo $this->translate("Are you sure that you want to pay using wallet for this verification subscription?"); ?></p>
          <div style="display:none;" id="error_message" class="failed_msg mt-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal"><?php echo $this->translate("Cancel"); ?></button>
          <button type="submit" id="submit" class="btn btn-primary"><?php echo $this->translate('Pay'); ?></button>
        </div>
      </form>
      <div class="core_loading_cont_overlay" id="core_loading_cont_overlay" style="display:none;"></div>
    </div>
  </div>
</div>

<!-- reload Modal Poup -->
<div id="reload_modal_data">
  <div class="modal fade reload_modal" id="reload_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative" id="reload_form">
          <div class="modal-header">
            <h1 class="modal-title fs-5"><?php echo $this->translate('Reload After Adding Funds') ?></h1>
          </div>
          <div class="modal-body">
            <p class="mb-3" id="modal_description"><?php echo $this->translate('Your balance will not update automatically. Please reload this page after recharging your wallet.') ?></p>
            <div style="display:none;" id="error_message" class="failed_msg mt-2"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-link" data-bs-dismiss="modal"><?php echo $this->translate("Cancel"); ?></button>
            <button type="button" id="reload_button" class="btn btn-primary"><?php echo $this->translate('Reload'); ?></button>
          </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  AttachEventListerSE('submit', '#wallet_payment', function(e) {
    e.preventDefault();
    scriptJquery("#core_loading_cont_overlay").show();
    scriptJquery.ajax({
      dataType: 'json',
      url: en4.core.baseUrl + 'user/verification/complete',
      method: 'post',
      data: {
        format: 'json',
        user_id: '<?php echo $this->subject()->user_id; ?>',
      },
      success: function(response) {
        scriptJquery("#core_loading_cont_overlay").hide();
        if(response.status) {
          scriptJquery('#wallet_payment').hide();
          scriptJquery('#pay_wallet_form').append("<div id='success_msg' class='success_msg success_msg m-2'><span>"+response.message+"</span></div>");
          setTimeout(() => {
            scriptJquery('#wallet_modal').modal;
            scriptJquery('#pay_wallet_form').hide();
            window.proxyLocation.reload("full");
            //loadAjaxContentApp(window.proxyLocation.href);
          }, 2000);
        } else {
          scriptJquery('#error_message').show().html(response.message);
        } 
      }
    });
  });
  en4.core.runonce.add(function() {
    scriptJquery("#wallet_modal").on('hide.bs.modal', function(){
      scriptJquery('#error_message').hide();
    });
  });

  //Relaod popup
  function showReloadPopup() {
    // Make sure you're using the correct Bootstrap 5 modal method
    var myModal = new bootstrap.Modal(document.getElementById('reload_modal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();  // Show the modal
  }

  // Reload the page when the "Reload" button is clicked
  AttachEventListerSE('click', '#reload_button', function () {
    window.location.reload();  // Reload the page
  });
</script>
