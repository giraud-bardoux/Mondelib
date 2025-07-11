<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: currency.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_payment', 'childMenuItemName' => 'core_admin_main_payment_currency')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Billing Settings") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_payment').addClass('active');
</script>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery("#selectall").click(function(){
      if(this.checked){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', true);
        });
      } else {
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', false);
        });
      }
    });
    
    scriptJquery("input[name='enable'],input[name='disable']").on('click', function( event ) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to perform this action on selected entries?")) ?>')) {
          scriptJquery('#multidelete_form').append("<input type='hidden' value='"+name+"' name='"+name+"'>");
          scriptJquery('#multidelete_form').trigger("submit");
        }
      }
    });
  });
</script>

<h3><?php echo $this->translate("Manage Currency") ?></h3>

<p><?php echo $this->translate('This page list all the currencies you can enable on your website.'); ?></p> 
<p><?php echo $this->translate('The price of the content will be saved in Default currency in the database and will be shown in different currencies according to the Currency Rate below. You can manually enter the currency rates with below given formula or click on "Update Currency Rates" button to update the currencies.'); ?></p>
<p><?php echo $this->translate('<strong class="bold">Formula:</strong><br>To enter currency rates:
1 Default Currency = Desired Currency Value'); ?></p>
<p><?php echo $this->translate('<strong class="bold">For example:</strong><br> If US Dollar is default currency and 1 US Dollar = 1.33 Australian Dollar<br />Then the Currency rate will be 1.33 for the Australian Dollar.'); ?></p>

<div class="admin_results">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'settings', 'action' => 'create-currency'), $this->translate("Add New Currency"),array('class' => 'smoothbox admin_link_btn')) ?>
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currencyapikey','')) { ?>
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'settings', 'action' => 'update-currency'), $this->translate('Update Currency Rates'), array('class' => 'admin_link_btn icon_sync')) ?>
  <?php } else { ?>
    <a href="javascript:;" class="disabled admin_link_btn icon_sync"><?php echo $this->translate('Update Currency Rates'); ?></a>
    <div class="tip d-block mt-2"><span><?php echo $this->translate("You haven't added the currency converter API key yet. Enter the API key from the <a href='admin/payment/settings'>Global Settings</a> page to enable automatic updates for currency rates."); ?></span></div>
  <?php } ?>
</div>

<div class='admin_search admin_common_search admin_manage_activity_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0) { ?> 
  <div class='clear'>
    <form id="multidelete_form" action="<?php echo $this->url();?>" method="POST">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="_count">
          <?php echo $this->translate(array('%s entry found.', '%s entries found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
        </div>
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <input type='submit' value="Enable" name="enable" class="btn btn-success">
          <input type='submit' value="Disable" name="disable" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator); ?>
        </div>
      </div>
      <div class='admin_responsive_table'>
        <table class='admin_table'>
          <thead>
            <tr>
              <th class='admin_table_short'><input id="selectall" type='checkbox' /></th>
              <th class='admin_table_short'><?php echo $this->translate('ID'); ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Currency Symbol') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Icon') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Currency Name') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Currency Code') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Change Rate') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Gateways') ?></th>
              <th class="admin_table_centered"><?php echo $this->translate('Enabled') ?></th>
              <th><?php echo $this->translate('Action') ?></th>
            </tr>
          </thead>
          <tbody id="menu_list">
            <?php foreach ($this->paginator as $key => $currency) { ?>
              <tr id="order_<?php echo $currency->getIdentity(); ?>">
                <td><input type='checkbox' class='checkbox' name='selectedItems[]' value="<?php echo $currency->getIdentity() ?>"/></td>
                <td data-label="<?php echo $this->translate('ID') ?>"><?php echo $currency->getIdentity(); ?></td>
                <td data-label="<?php echo $this->translate('Symbol') ?>" class="admin_table_centered"><?php echo $currency->symbol; ?></td>
                <td data-label="<?php echo $this->translate('Icon') ?>" class="admin_table_centered">
                  <?php if(isset($currency->icon) && !empty($currency->icon)) { ?>
                    <?php $path = Engine_Api::_()->core()->getFileUrl($currency->icon); ?>
                    <?php if($path) { ?>
                      <img class="icon" src="<?php echo $path; ?>" alt="<?php echo $currency->title; ?>" />
                    <?php } ?>
                  <?php } else { echo "---"; } ?>
                </td>
                <td data-label="<?php echo $this->translate('Currency Name') ?>" class="admin_table_centered">
                  <?php echo $currency->title; ?>
                </td>
                <td data-label="<?php echo $this->translate('Code') ?>" class="admin_table_centered"><?php echo $currency->code; ?></td>
                <td data-label="<?php echo $this->translate('Change Rate') ?>" class="admin_table_centered"><?php echo $currency->change_rate ? $currency->change_rate : '-'; ?></td>
                <td class="admin_table_centered nowrap" data-label="<?php echo $this->translate('Gateways') ?>" class="admin_table_centered">
                <?php if(!empty($currency->gateways)) {
                  $gateways = json_decode($currency->gateways);
                  if(engine_count($gateways) > 0) {
                    $title = array(); 
                    foreach($gateways as $gateway) {
                      $title[] = Engine_Api::_()->getDbTable('gateways', 'payment')->getGatewayTitle($gateway); 
                    }
                    $title = implode(', ', $title);
                  } else {
                    $title = "---";
                  }
                } else { ?>
                  <?php $title = "---"; ?>
                <?php } ?>
                <?php echo trim($title, ','); ?>
                </td>
                <td data-label="<?php echo $this->translate('Enabled') ?>" class="admin_table_centered">
                  <?php if($currency->code != Engine_Api::_()->payment()->defaultCurrency()) { ?>
                    <?php if($currency->enabled): ?>
                      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'settings', 'action' => 'enable', 'id' => $currency->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('data-bs-toggle' => "tooltip", 'data-bs-placement' => "bottom", 'data-bs-original-title' => $this->translate('Disable')))) ?>
                    <?php else: ?>
                      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'settings', 'action' => 'enable', 'id' => $currency->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/uncheck.png', '', array('data-bs-toggle' => "tooltip", 'data-bs-placement' => "bottom", 'data-bs-original-title' => $this->translate('Enable')))) ?>
                    <?php endif; ?>
                  <?php } else { ?>
                    <img src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png'; ?>" alt="" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="<?php echo $this->translate('This is the default currency, so before disabling it, please choose another default currency for your site.'); ?>">
                  <?php } ?>
                </td>
                <td class="admin_table_options nowrap">
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'settings', 'action' => 'edit-currency', 'id' => $currency->getIdentity()), $this->translate("Edit"),array('class' => 'smoothbox')) ?>
                  <?php if($currency->code == Engine_Api::_()->payment()->defaultCurrency()){ ?>
                  |
                    <?php echo $this->translate("Default"); ?>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>  
    </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no currency matching your search criteria.") ?>
    </span>
  </div>
<?php } ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#menu_list').addClass('sortable');
    var SortablesInstance = scriptJquery('#menu_list').sortable({
      stop: function( event, ui ) {
        var ids = [];
        scriptJquery('#menu_list > tr').each(function(e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        // Send request
        var url = '<?php echo $this->url(array('action' => 'order')) ?>';
        scriptJquery.ajax({
            url : url,
            dataType : 'json',
            data : {
                format : 'json',
                order : ids
            }
        });
      }
    });
  });
</script>
<?php if(!empty($_SESSION['apiError'])) { ?>
  <script>
    alert('<?php echo $_SESSION["apiError"] ; ?>')
  </script>
<?php unset($_SESSION['apiError']); ?>
<?php } ?>
