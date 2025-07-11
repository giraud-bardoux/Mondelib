<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9924 2013-02-16 02:16:02Z alex $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_orders', 'childMenuItemName' => 'core_admin_main_orders_transactions')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Orders") ?>
</h2>	
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
      ?>
  </div>
<?php endif; ?>
<div class="admin_common_top_section">
  <h3>
    <?php echo $this->translate("Transactions") ?>
  </h3>	
  <p>
    <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_INDEX_INDEX_DESCRIPTION") ?>
  </p>  
  <p>
    <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/74/transactions" target="_blank">See KB article.</a>';
    } 
    ?>
</p>
</div>
<?php if( !empty($this->error) ): ?>
  <ul class="form-errors">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>
<?php return; endif; ?>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>
<?php endif; ?>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->filterValues['order'] ?>';
  var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      $('direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('direction').value = default_direction;
    }
    $('filter_form').submit();
  }
</script>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s transaction found", "%s transactions found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <?php $class = ( $this->order == 'transaction_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('transaction_id', 'DESC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'user_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'ASC');">
            <?php echo $this->translate("Member") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'gateway_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class='<?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('gateway_id', 'ASC');">
            <?php echo $this->translate("Gateway") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'type' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class='<?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('type', 'DESC');">
            <?php echo $this->translate("Type") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'state' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class='<?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('state', 'DESC');">
            <?php echo $this->translate("State") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'amount' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class='<?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('amount', 'DESC');">
            <?php echo $this->translate("Amount") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'timestamp' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class='<?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('timestamp', 'DESC');">
            <?php echo $this->translate("Date") ?>
          </a>
        </th>
        <th class='admin_table_options'>
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item):
        $user = @$this->users[$item->user_id];
        $order = @$this->orders[$item->order_id];
        $gateway = @$this->gateways[$item->gateway_id];
        ?>
        <tr>
          <td><?php echo $item->transaction_id ?></td>
          <td class='admin_table_bold admin_table_name' data-label="<?php echo $this->translate("Member") ?>">
            <?php echo ( $user ? $user->__toString() : '<i>' . $this->translate('Deleted or Unknown Member') . '</i>' ) ?>
          </td>
          <td data-label="<?php echo $this->translate("Gateway") ?>">
            <?php echo ( $gateway ? $gateway->title : ($item->gateway_id == 3000 ? $this->translate("Wallet") : '<i>' . $this->translate('Unknown Gateway') . '</i>') ) ?>
          </td>
          <td data-label="<?php echo $this->translate("Type") ?>">
            <?php echo $this->translate(ucwords($item->type)) ?>
          </td>
          <td data-label="<?php echo $this->translate("State") ?>">
            <?php echo $this->translate(ucfirst($item->state)) ?>
          </td>
          <td data-label="<?php echo $this->translate("Amount") ?>" class="nowrap">
            <?php echo $this->translate('%s ', $item->currency) . Engine_Api::_()->payment()->getCurrencyPrice($item->amount, $item->currency, '', ''); ?>
            <?php if($item->currency != $item->current_currency) { ?>
              <?php echo '('.$this->translate('%s', $item->current_currency) .' ' .Engine_Api::_()->payment()->getCurrencyPrice($item->amount * $item->change_rate, $item->current_currency) .')'; ?><br />
            <?php } ?>
          </td>
          <td data-label="<?php echo $this->translate("Date") ?>">
            <?php echo $this->locale()->toDateTime($item->timestamp) ?>
          </td>
          <td class='admin_table_options'>
            <a class="ajaxsmoothbox" href='<?php echo $this->url(array('action' => 'detail', 'transaction_id' => $item->transaction_id));?>'>
              <?php echo $this->translate("details") ?>
            </a>
            <?php if(engine_in_array($gateway->gateway_id,array(4,5,6))) { ?>
              <?php 
                if($item->state == "okay") {
                  $approvalText = "Approved";
                  $rejectText = "Reject";
                } elseif($order->state == "cancelled") {
                  $rejectText = "Rejected";
                  $approvalText = "Approve";
                } else {
                  $approvalText = "Approve";
                  $rejectText = "Reject";
                }
              ?>
              |
              <?php if($item->state != "okay") { ?>
                <?php echo $this->htmlLink($this->url(array('action'=>'approve','transaction_id'=> $item->transaction_id,'module'=>'payment'), 'admin_default', true), $this->translate($approvalText), array('title' => $this->translate($approvalText), 'class' => 'smoothbox')); ?>
                |
                <?php echo $this->htmlLink($this->url(array('action'=>'cancel','transaction_id'=> $item->transaction_id,'module'=>'payment'), 'admin_default', true), $this->translate($rejectText), array('title' => $this->translate($rejectText), 'class' => 'smoothbox')); ?>
              <?php } else if($item->state == "okay") { ?>
                <?php echo $this->translate($approvalText); ?>
              <?php } ?>
            <?php } ?>
            <?php if($item->file_id > 0){ ?>
              |
              <?php echo $this->htmlLink($this->url(array('action'=>'receipt','transaction_id'=> $item->transaction_id,'module'=>'payment'), 'admin_default', true), $this->translate("View Attachment"), array('title' => $this->translate("View Attachment"), 'class' => 'smoothbox')); ?>
            <?php } ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
