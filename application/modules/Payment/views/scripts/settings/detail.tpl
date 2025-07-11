<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: detail.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="payment_transaction_details_popup">
  <div class="payment_transaction_detail_headline d-flex justify-content-between align-items-center">
    <h3><?php echo $this->translate("Transaction Details") ?></h3>
    <a href="javascript:void(0);" onclick="parent.Smoothbox.close();" class="btn-icon btn-alt btn-icon rounded-circle center_item"><i class="icon_cross"></i></a>
  </div>
  <table>
    <tr>  
      <td><?php echo $this->translate('Transaction ID') ?></td>
      <td><?php echo $this->locale()->toNumber($this->transaction->transaction_id) ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Payment Gateway') ?></td>
      <td>
        <?php if( $this->gateway ): ?>
          <?php echo $this->translate($this->gateway->title) ?>
        <?php elseif( $this->transaction->gateway_id == 3000 ): ?>
          <?php echo $this->translate("Wallet"); ?>
        <?php else: ?>
          <i><?php echo $this->translate('Unknown Gateway') ?></i>
        <?php endif; ?>
      </td>
    </tr>
    <tr>   
      <td><?php echo $this->translate('Payment Type') ?></td>
      <td><?php echo $this->translate(ucfirst($this->transaction->type)) ?></td>
    </tr>
    <tr>     
      <td><?php echo $this->translate('Payment Status') ?></td>
      <td><?php echo $this->translate(ucfirst($this->transaction->state)) ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Payment Amount') ?></td>
      <td>
        <?php echo $this->translate('%s ', $this->transaction->currency) . Engine_Api::_()->payment()->getCurrencyPrice($this->transaction->amount, $this->transaction->currency); ?>
        <?php if($this->transaction->currency != $this->transaction->current_currency) { ?>
          <?php echo '('.$this->translate('%s', $this->transaction->current_currency) .' ' .Engine_Api::_()->payment()->getCurrencyPrice($this->transaction->amount * $this->transaction->change_rate, $this->transaction->current_currency) .')'; ?>
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Gateway Transaction ID') ?></td>
      <td>
        <?php if(!in_array($this->transaction->gateway_id, array('3', '4', '5', '6')) && !empty($this->transaction->gateway_transaction_id) ): ?>
          <?php echo $this->transaction->gateway_transaction_id; ?>
        <?php else: ?>
          <?php echo $this->translate("N/A"); ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php if( !empty($this->transaction->gateway_parent_transaction_id) ): ?>
      <tr>
        <td><?php echo $this->translate('Gateway Parent Transaction ID') ?></td>
        <td><?php echo $this->transaction->gateway_parent_transaction_id; ?></td>
      </tr>
    <?php endif; ?>
    <?php if( !empty($this->transaction->gateway_order_id) ): ?>
      <tr> 
        <td>
          <?php echo $this->translate('Gateway Order ID') ?>
        </td>
        <td>
          <?php if( !empty($this->transaction->gateway_order_id) ): ?>
            <?php echo $this->transaction->gateway_order_id ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endif; ?>
    <tr>
      <td>
        <?php echo $this->translate('Date') ?>
      </td>
      <td>
        <?php echo $this->locale()->toDateTime($this->transaction->timestamp) ?>
      </td>
    </tr>
  </table>
</div>
