<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _signupSubscription.tpl 9804 2012-10-27 08:31:56Z pamela $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php if($this->user && Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) { ?>
  <?php if($this->user->wallet_amount == 0) { ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("You don't have enough balance to subscribe paid plans, please first recharge your  "); ?><a href="<?php echo $this->url(array("module" => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true); ?>" onclick="showReloadPopup();" target="_blank"><?php echo $this->translate(" wallet"); ?>.</a>
      </span>
    </div>
  <?php } else { ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("Current balance: "); ?><?php echo $this->user->wallet_amount ? Engine_Api::_()->payment()->getCurrencyPrice($this->user->wallet_amount,'','','') : 0.00; ?>
      </span>
    </div>
  <?php } ?>

  <?php if($this->user) { ?>
    <?php $currentSubscriptionFirstPlan = Engine_Api::_()->getDbTable('subscriptions', 'payment')->currentSubscriptionFirstPlan($this->user); 
    if($currentSubscriptionFirstPlan) {
      $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
      $currentFirstPackage = $packagesTable->fetchRow(array('package_id = ?' => $currentSubscriptionFirstPlan->package_id));
    }
    ?>
  <?php } ?>
<?php } ?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<form method="post" id="signup" name="signup" action="<?php echo $this->escape($this->form->getAction()) ?>" enctype="application/x-www-form-urlencoded">
  <div class="payment_subscription_plans_table <?php echo $settings->getSetting('payment.overlap', 1) ? 'payment_subscription_plans_overlap' : ''; ?>" 
    style="background-color:<?php echo $settings->getSetting('payment.body.container.clr',""); ?>;">
    <?php if($settings->getSetting('payment.table.title','Subscription Plans') || $settings->getSetting('payment.table.description','Please choose a subscription plan from the options below.')) { ?>
      <div class="payment_subscription_plans_table_header" style="background-color:<?php echo $settings->getSetting('payment.header.bgclr',""); ?>;">
        <p class="payment_subscription_plans_table_heading" style="color:<?php echo $settings->getSetting('payment.header.txtclr',""); ?>;"><?php echo $this->translate($settings->getSetting('payment.table.title','Subscription Plans')); ?></p>
        <p class="payment_subscription_plans_table_des" style="color:<?php echo $settings->getSetting('payment.header.txtclr',""); ?>;"><?php echo $this->translate(nl2br($settings->getSetting('payment.table.description','Please choose a subscription plan from the options below.'))); ?></p>
      </div>
    <?php } else { ?>
      <div class="payment_subscription_plans_table_header" style="background-color:<?php echo $settings->getSetting('payment.header.bgclr',""); ?>;">
        <p class="payment_subscription_plans_table_heading" style="color:<?php echo $settings->getSetting('payment.header.txtclr',""); ?>;"><?php echo $this->translate("Subscription Plan"); ?></p>
        <p class="payment_subscription_plans_table_des" style="color:<?php echo $settings->getSetting('payment.header.txtclr',""); ?> ;"><?php echo $this->translate("Please select a subscription plan from the list below."); ?></p>
      </div>
    <?php } ?>
    
    <div class="payment_subscription_plans_listing">
      <?php foreach($this->form->getPackages() as $package): ?>
        <?php if($this->currentPackage && $package->package_id == $this->currentPackage->package_id ) { continue; } ?>

        <?php if($currentSubscriptionFirstPlan && $currentSubscriptionFirstPlan->status == 'initial' && $currentFirstPackage && $package->package_id != $currentFirstPackage->package_id ) { continue; } ?>

        <?php $column = json_decode($package->packagestyles); ?>
        <div class="payment_subscription_plans_listing_item<?php if(!empty($column->show_highlight)): ?> heighlighted <?php endif;?>" style="width:<?php echo isset($column->column_width) && is_numeric($column->column_width) ? $column->column_width.'px' : $this->width ?>; <?php if(isset($column->column_margin) && $column->column_margin):?>margin-left:<?php echo $column->column_margin - 4;?>px;margin-right:<?php echo $column->column_margin;?>px;<?php endif;?>">
          <article style="background-color:#<?php echo isset($column->column_row_color) && $column->column_row_color ? $column->column_row_color : '';?>;">
            <div class="payment_subscription_plans_listing_top" style="background-color:#<?php echo isset($column->column_color) && $column->column_color ? $column->column_color : '' ?>;">
              <?php if(!empty($package->photo_id)): ?>
                <?php $path = Engine_Api::_()->core()->getFileUrl($package->photo_id); ?>
                <?php if(!empty($path)) { ?>
                  <div class="payment_subscription_plans_listing_img" style="background-image:url(<?php echo $path; ?>);"></div>
                <?php } ?>
              <?php endif; ?>
              <div class="payment_subscription_plans_listing_title">
                <?php if(!empty($package->title)):?>
                  <span style="color:#<?php echo isset($column->column_text_color) && $column->column_text_color ? $column->column_text_color : ''; ?>"><?php echo $this->translate($package->title); ?></span>
                <?php endif;?>
              </div>
              <div class="payment_subscription_plans_listing_content">
                <p class="price">
                  <?php // Plan is free
                    $typeStr = '';
                    $priceStr =  Engine_Api::_()->payment()->getCurrencyPrice($package->price,'','','');
                    
                    if( $package->price == 0 ) {
                      $typeStr = $this->translate('Free');
                    }
                    // Plan is recurring
                    else if( $package->recurrence > 0 && $package->recurrence_type != 'forever' ) {
                      // Make full string
                      if( $package->recurrence == 1 ) { // (Week|Month|Year)ly
                        if( $package->recurrence_type == 'day' ) {
                          $typeStr = $this->translate('daily');
                        } else {
                          $typeStr = $this->translate($package->recurrence_type . 'ly');
                        }
                      } else { // per x (Week|Month|Year)s
                        $typeStr = $this->translate(array($package->recurrence_type, $package->recurrence_type . 's', $package->recurrence));
                        $typeStr = sprintf($this->translate(' %1$s %2$s'), $package->recurrence, $typeStr); // @todo currency
                      }
                    } 
                    // Plan is one-time
                    else {
                      $typeStr = $this->translate('One-time fee');
                    }
                  ?>
                  <span style="color:#<?php echo $column->column_text_color;?> "><?php echo $priceStr; //sprintf($this->translate('%1$s'), $priceStr); ?></span>
                  <?php if($typeStr): ?><sub style="color:#<?php echo $column->column_text_color;?> ">/&nbsp;<?php echo $typeStr; ?></sub><?php endif;?>
                </p>
                <p class="duration" style="color:#<?php echo $column->column_text_color;?> ">
                  <?php $typeStr = $this->translate(array($package->duration_type, $package->duration_type . 's', $package->duration)); ?>
                  <?php if($package->duration > 0) { ?>
                    <span  style="color:#<?php echo $column->column_text_color;?>"><?php echo sprintf($this->translate('for %1$s %2$s'),$package->duration, $typeStr); ?></span>
                  <?php } else { ?>
                    <?php if($package->duration_type == 'forever') { ?>
                      <span  style="color:#<?php echo $column->column_text_color;?> "><?php echo $this->translate('forever'); ?></span>
                    <?php } else { ?>
                      <span  style="color:#<?php echo $column->column_text_color;?> "><?php echo sprintf($this->translate('%1$s'),$typeStr); ?></span>
                    <?php } ?>
                  <?php } ?>
                </p>
              </div>
            </div>
            <?php if($package->description) { ?>
              <div class="payment_subscription_plans_listing_hint" style="color:#<?php echo isset($column->column_row_text_color) && ($column->column_row_text_color) ? $column->column_row_text_color : ''; ?> ;height:<?php echo isset($column->column_descr_height) && is_numeric($column->column_descr_height) ? $column->column_descr_height : ''; ?>px;border-color:#<?php echo isset($column->row_border_color) && $column->row_border_color ? $column->row_border_color : ''; ?>;">
                <?php echo $this->translate($package->description) ?>
              </div>
            <?php } ?>
            <ul class="payment_subscription_plans_listing_features <?php if(isset($column->icon_position) &&  $column->icon_position): ?> iscenter <?php endif;?>">
              <?php $rowCount = 15; ?> 
              <?php for ($i = 1; $i <= $rowCount; $i++) { ?>
                <?php 
                  $fileIdColumn = 'row'.$i.'_file_id';
                  $descriptionColumn = 'row'.$i.'_description';
                  $textColumn = 'row'.$i.'_text';
                  $features = json_decode($package->features);
                ?>
                <?php if(!empty($features->$textColumn)):?>
                  <li class="payment_custom_scroll" style="height:<?php echo isset($column->row_height) && is_numeric($column->row_height) ? $column->row_height : ''; ?>px;border-color:#<?php echo isset($column->row_border_color) && $column->row_border_color ? $column->row_border_color : ''; ?>;">
                    <?php if(isset($features->$fileIdColumn) && !empty($features->$fileIdColumn)):?>
                      <i class="<?php echo $features->$fileIdColumn; ?> payment_font_icon"></i>
                    <?php endif;?>
                    <?php if(isset($features->$textColumn) && $features->$textColumn):?>
                      <span style="color:#<?php echo isset($column->column_row_text_color) && $column->column_row_text_color ? $column->column_row_text_color : ''; ?> "><?php echo $this->translate($features->$textColumn); ?></span>	
                    <?php endif;?>
                    <?php if(isset($features->$descriptionColumn) && $features->$descriptionColumn):?>
                      <i data-bs-toggle="tooltip" data-bs-placement="top" class="fa fa-question-circle" title="<?php echo $this->translate($features->$descriptionColumn); ?>" style="color:#<?php echo isset($column->column_row_text_color) && $column->column_row_text_color ? $column->column_row_text_color : ''; ?>">
                      </i>
                    <?php endif;?>
                  </li>
                <?php endif; ?>
              <?php } ?>
            </ul>
            <div class="payment_subscription_plans_listing_footer">
              <?php if($this->user && $this->user->wallet_amount > 0) { ?>
                <?php if($this->user->wallet_amount >= $package->price) { ?>
                  <input type="radio" name="package_id" id="package_id_<?php echo $package->package_id ?>" value="<?php echo $package->package_id ?>" />
                  <a href="javascript:;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#wallet_modal" onclick="onFormSubmit(<?php echo $package->package_id ?>, <?php echo $package->price; ?>)" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                <?php } else { ?>
                  <p class="error_msg">
                    <?php echo $this->translate("You don't have enough balance to subscribe this plan, please first recharge your  "); ?><a href="<?php echo $this->url(array("module" => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true); ?>" onclick="showReloadPopup();" target="_blank"><?php echo $this->translate(" wallet"); ?>.</a>
                  </p>
                  <a href="javascript:;" class="btn btn-primary disabled" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                <?php } ?>
              <?php } else { ?>
                <input type="radio" name="package_id" id="package_id_<?php echo $package->package_id ?>" value="<?php echo $package->package_id ?>" />

                <?php if($this->user) { ?>
                  <?php if($package->price == 0) { ?>
                    <a href="javascript:;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#wallet_modal" onclick="onFormSubmit(<?php echo $package->package_id ?>, <?php echo $package->price; ?>)" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                  <?php } else { ?>
                    <a href="javascript:;" class="btn btn-primary disabled" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                  <?php } ?>
                <?php } else { ?>
                  <?php if($package->price == 0) { ?>
                    <a href="javascript:;" class="btn btn-primary" onclick="onFormSubmit(<?php echo $package->package_id ?>, <?php echo $package->price; ?>)" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                  <?php } else { ?>
                    <a href="javascript:;" class="btn btn-primary" onclick="onFormSubmit(<?php echo $package->package_id ?>, <?php echo $package->price; ?>)" style="background-color:#<?php echo isset($column->footer_bg_color) && $column->footer_bg_color ? $column->footer_bg_color : ''; ?>;color:#<?php echo isset($column->footer_text_color) && $column->footer_text_color ? $column->footer_text_color : ''; ?>"><?php echo $this->currentSubscription ? $this->translate("Choose Plan") : $this->translate("Join Now"); ?></a>
                  <?php } ?>
                <?php } ?>
              <?php } ?>
            </div>
            <?php if(isset($column->show_label) && $column->show_label): ?>
              <div class="<?php if(isset($column->label_position) && $column->label_position) : ?>payment_subscription_plans_listing_label right<?php else:?>payment_subscription_plans_listing_label left<?php endif;?>">
                <?php if(isset($column->label_text) && $column->label_text): ?><div style="color:#<?php echo $column->label_text_color;?> ;background-color:#<?php echo isset($column->label_color) && $column->label_color ? $column->label_color : ''; ?>;"><?php echo $this->translate($column->label_text); ?></div><?php endif;?>
              </div>
            <?php endif;?>
          </article>
        </div>
      <?php endforeach;?>
    </div>
  </div>
</form>

<!-- wallet Modal Poup -->
<div id="wallet_modal_data">
  <div class="modal fade wallet_modal" id="wallet_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative" id="pay_wallet_form">
        <?php if($this->user) { ?>
          <form action="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'subscription', 'action' => 'choose', 'user_id' => $this->user->getIdentity()),'default',true)) ?>" method="post" id="wallet_payment" enctype="multipart/form-data">
        <?php } else { ?>
          <form action="<?php echo $this->escape($this->url(array('module'=> 'user','controller' => 'subscription', 'action' => 'choose'),'default',true)) ?>" method="post" id="wallet_payment" enctype="multipart/form-data">
        <?php } ?>
          <div class="modal-header">
            <h1 class="modal-title fs-5"><?php echo $this->translate('Subscription Plan Confirmation') ?></h1>
          </div>
          <div class="modal-body">
            <p class="mb-3" id="modal_description"><?php echo $this->translate('Are you sure you want to subscribe to the selected plan? Please click "Proceed to Pay" button to continue and complete your payment.') ?></p>
            <div style="display:none;" id="error_message" class="failed_msg mt-2"></div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="selected_package_id" id="selected_package_id" />
            <button type="button" id="wallet_cancel" class="btn btn-link" data-bs-dismiss="modal"><?php echo $this->translate("Cancel"); ?></button>
            <button type="submit" id="submit" class="btn btn-primary"><?php echo $this->translate('Proceed to Pay'); ?></button>
          </div>
        </form>
        <div class="core_loading_cont_overlay" id="core_loading_cont_overlay" style="display:none;"></div>
      </div>
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
            <button type="button" id="wallet_cancel" class="btn btn-link" data-bs-dismiss="modal"><?php echo $this->translate("Cancel"); ?></button>
            <button type="button" id="reload_button" class="btn btn-primary"><?php echo $this->translate('Reload'); ?></button>
          </div>
      </div>
    </div>
  </div>
</div>

<?php if($settings->getSetting('payment.footer.enable',1)) { ?>
  <div class="payment_subscription_plans_listing_note">
    <div class="payment_rich_content">
      <?php echo $this->translate(nl2br($settings->getSetting('payment.footer.note',''))); ?>
    </div>
  </div>
<?php } ?>

<script type='text/javascript'>
  AttachEventListerSE('submit', '#wallet_payment', function(e) {
    e.preventDefault();
    scriptJquery("#core_loading_cont_overlay").show();
    scriptJquery.ajax({
      dataType: 'json',
      url: en4.core.baseUrl + 'payment/subscription/choose',
      method: 'post',
      data: {
        format: 'json',
        user_id: '<?php echo $this->user ? $this->user->getIdentity() : 0; ?>',
        package_id: scriptJquery('#selected_package_id').val(),
      },
      success: function(response) {
        scriptJquery("#core_loading_cont_overlay").hide();
        if(response.status) {
          scriptJquery('#wallet_payment').hide();
          scriptJquery('#pay_wallet_form').append("<div id='success_msg' class='success_msg success_msg m-2'><span>"+response.message+"</span></div>");
          setTimeout(() => {
            scriptJquery('#wallet_cancel').trigger('click');
            scriptJquery('#pay_wallet_form').hide();
            window.proxyLocation.reload("full");
            // if(response.url) {
            //   loadAjaxContentApp(response.url);
            // } else {
            //   loadAjaxContentApp(window.proxyLocation.href);
            // }
          }, 2000);
        } else {
          scriptJquery('#error_message').show().html(response.message);
          setTimeout(() => {
            if(response.url) {
              loadAjaxContentApp(response.url);
            }
          }, 2000);
        } 
      }
    });
  });

  en4.core.runonce.add(function() {

    scriptJquery(scriptJquery('#wallet_modal_data').html()).appendTo('#append-script-data');
    scriptJquery('#wallet_modal_data').remove();

    //Reload modal window
    scriptJquery(scriptJquery('#reload_modal_data').html()).appendTo('#append-script-data');
    scriptJquery('#reload_modal_data').remove();

    scriptJquery("#wallet_modal").on('hide.bs.modal', function(){
      scriptJquery('#error_message').hide();
      scriptJquery('#selected_package_id').val('');
    });
  });

  scriptJquery("#global_wrapper").addClass('signup_subscriptions_plans');
  
  function onFormSubmit(id, price) {
    if(price == 0) {
      scriptJquery('#modal_description').html("<?php echo $this->string()->escapeJavascript($this->translate('Are you sure you want to subscribe to the selected plan? Please click "Yes" to proceed further.')) ?>");
      scriptJquery('#submit').html("<?php echo $this->string()->escapeJavascript($this->translate('Yes')) ?>");
    } else {
      scriptJquery('#modal_description').html("<?php echo $this->string()->escapeJavascript($this->translate('Are you sure you want to subscribe to the selected plan? Please click "Proceed to Pay" button to continue and complete your payment.')) ?>");
      scriptJquery('#submit').html("<?php echo $this->string()->escapeJavascript($this->translate('Proceed to Pay')) ?>");
    }
    <?php if($this->user) { ?>
      scriptJquery('#selected_package_id').val(id);
    <?php } else { ?>
      document.getElementById("package_id_"+id).checked = true;
      scriptJquery("#signup").trigger('submit');
    <?php } ?>
  }

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
