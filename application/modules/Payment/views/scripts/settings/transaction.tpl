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
      <div class="user_setting_global_form">
        <h3><?php echo $this->translate("Transaction History"); ?></h3>
        <p><?php echo $this->translate('Below you can view the transaction history of your orders towards membership payments. Entering criteria into the filter fields will help you find specific order.'); ?></p>
        <div class="manage_search core_search_form">
          <?php echo $this->formFilter->render($this) ?>
        </div>
        <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
          <div class="manage_table">
            <table>
                <thead>
                  <tr>
                    <th><?php echo $this->translate("Order ID") ?></th>
                    <th><?php echo $this->translate("Plan Name") ?></th>
                    <th><?php echo $this->translate("Gateway") ?></a></th>
                    <th><?php echo $this->translate("Type") ?></a></th>
                    <th><?php echo $this->translate("Status") ?></th>
                    <th><?php echo $this->translate("Amount") ?></th>
                    <th><?php echo $this->translate("Order Date") ?></th>
                    <th><?php echo $this->translate("Options") ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $this->paginator as $item):
                    $user = @$this->users[$item->user_id];
                    $order = @$this->orders[$item->order_id];
                    $gateway = @$this->gateways[$item->gateway_id];
                    $subscription = $order->getSource();
                    if($subscription->getType() == 'payment_subscription') {
                      $package = $subscription->getPackage();
                    }
                    ?>
                    <tr>
                      <td data-label="<?php echo $this->translate("Order ID") ?>"><a class="smoothbox" href='<?php echo $this->url(array('action' => 'detail', 'transaction_id' => $item->transaction_id));?>'><?php echo "#".$item->order_id ?></a></td>
                      <td data-label="<?php echo $this->translate("Plan Name") ?>">
                        <?php if($subscription->getType() == 'payment_subscription' && $package) { ?>
                          <?php echo $package->getTitle(); ?>
                        <?php } else { ?>
                          <?php echo "---"; ?>
                        <?php } ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Gateway") ?>">
                        <?php if($item->gateway_id == 3000) { ?>
                          <?php echo $this->translate("Wallet"); ?>
                        <?php } else { ?>
                          <?php echo ( $gateway ? $gateway->title : '<i>' . $this->translate('Unknown Gateway') . '</i>' ) ?>
                        <?php } ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Type") ?>">
                        <?php echo $this->translate(ucwords($item->type)) ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Status") ?>">
                        <?php echo $this->translate(ucfirst($item->state)) ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Amount") ?>">
                        <?php echo $this->translate('%s ', $item->currency) . Engine_Api::_()->payment()->getCurrencyPrice($item->amount, $item->currency); ?>
                        <?php if($item->currency != $item->current_currency) { ?>
                          <?php echo '('.$this->translate('%s', $item->current_currency) .' ' .Engine_Api::_()->payment()->getCurrencyPrice($item->amount * $item->change_rate, $item->current_currency) .')'; ?><br />
                        <?php } ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Order Date") ?>">
                        <?php echo $this->locale()->toDateTime($item->timestamp) ?>
                      </td>
                      <td class="manage_table_options">
                        <a class="smoothbox payment_icon_view" href='<?php echo $this->url(array('action' => 'detail', 'transaction_id' => $item->transaction_id));?>'><?php echo $this->translate("Details") ?></a>
                        <?php if($item->file_id > 0){ ?>
                          |
                          <a class="smoothbox payment_icon_attachment" href='<?php echo $this->url(array('action' => 'receipt', 'transaction_id' => $item->transaction_id));?>'><?php echo $this->translate("View Attachment") ?></a>
                        <?php } ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div>
              <?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->filterValues, 'pageAsQuery' => true)); ?>
            </div>
          </div>
        <?php else: ?>
          <div class="tip">
            <span>
              <?php echo $this->translate("No order has been placed yet.") ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  scriptJquery(``).insertBefore(scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","From").datepicker({
      timepicker: false,
    })
  );
  scriptJquery(``).insertBefore(scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","To").datepicker({
    timepicker: false,
   })
  );
</script>
