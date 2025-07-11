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
  <h2 class="payment_transaction_detail_headline">
    <?php echo $this->translate("Transaction Details") ?>
  </h2>
  <table class="payment_transaction_details">
    <tr>
      <td>
        <?php echo $this->translate('Transaction ID') ?>
      </td>
      <td>
        <?php echo $this->locale()->toNumber($this->transaction->transaction_id) ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Member') ?>
      </td>
      <td>
        <?php if( $this->user && $this->user->getIdentity() ): ?>
          <?php echo $this->htmlLink($this->user->getHref(), $this->user->getTitle(), array('target' => '_parent')) ?>
          <?php //echo $this->user->__toString() ?>
          <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
            <?php echo $this->translate('(%1$s)', '<a href="mailto:' .
                $this->escape($this->user->email) . '">' . $this->user->email . '</a>') ?>
          <?php endif; ?>
        <?php else: ?>
          <i><?php echo $this->translate('Deleted Member') ?></i>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Payment Gateway') ?>
      </td>
      <td>
        <?php if( $this->gateway ): ?>
          <?php echo $this->translate($this->gateway->title) ?>
        <?php else: ?>
          <?php if($this->transaction->gateway_id == 3000) { ?>
            <?php echo $this->translate("Wallet") ?>
          <?php } else { ?>
            <i><?php echo $this->translate('Unknown Gateway') ?></i>
          <?php } ?>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Payment Type') ?>
      </td>
      <td>
        <?php echo $this->translate(ucwords($this->transaction->type)) ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Payment State') ?>
      </td>
      <td>
        <?php echo $this->translate(ucfirst($this->transaction->state)) ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Payment Amount') ?>
      </td>
      <td>
        <?php echo $this->translate('%s ', $this->transaction->currency) . Engine_Api::_()->payment()->getCurrencyPrice($this->transaction->amount, $this->transaction->currency); ?>
        <?php if($this->transaction->currency != $this->transaction->current_currency) { ?>
          <?php echo '('.$this->translate('%s', $this->transaction->current_currency) .' ' .Engine_Api::_()->payment()->getCurrencyPrice($this->transaction->amount * $this->transaction->change_rate, $this->transaction->current_currency) .')'; ?><br />
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Gateway Transaction ID') ?>
      </td>
      <td>
        <?php if(!engine_in_array($this->transaction->gateway_id, array('3', '4', '5', '6', '3000')) && !empty($this->transaction->gateway_transaction_id) ): ?>
          <?php echo $this->htmlLink(array(
              'route' => 'admin_default',
              'module' => 'payment',
              'controller' => 'index',
              'action' => 'detail-transaction',
              'transaction_id' => $this->transaction->transaction_id,
            ), $this->transaction->gateway_transaction_id, array(
              //'class' => 'smoothbox',
              'target' => '_blank',
          )) ?>
        <?php elseif(engine_in_array($this->transaction->gateway_id, array('3', '4', '5', '6', '3000')) && !empty($this->transaction->gateway_transaction_id)): ?>
          <?php echo $this->transaction->gateway_transaction_id; ?>
        <?php else: ?>
          <?php echo $this->translate("N/A"); ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php if( !empty($this->transaction->gateway_parent_transaction_id) ): ?>
      <tr>
        <td>
          <?php echo $this->translate('Gateway Parent Transaction ID') ?>
        </td>
        <td>
          <?php echo $this->htmlLink(array(
              'route' => 'admin_default',
              'module' => 'payment',
              'controller' => 'index',
              'action' => 'detail-transaction',
              'transaction_id' => $this->transaction->transaction_id,
              'show-parent' => 1,
            ), $this->transaction->gateway_parent_transaction_id, array(
              //'class' => 'smoothbox',
              'target' => '_blank',
          )) ?>
        </td>
      </tr>
    <?php endif; ?>
    <tr>
      <td>
        <?php echo $this->translate('Gateway Order ID') ?>
      </td>
      <td>
        <?php if( !empty($this->transaction->gateway_order_id) ): ?>
          <?php echo $this->htmlLink(array(
              'route' => 'admin_default',
              'module' => 'payment',
              'controller' => 'index',
              'action' => 'detail-order',
              'transaction_id' => $this->transaction->transaction_id,
            ), $this->transaction->gateway_order_id, array(
              //'class' => 'smoothbox',
              'target' => '_blank',
          )) ?>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Date') ?>
      </td>
      <td>
        <?php echo $this->locale()->toDateTime($this->transaction->timestamp) ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $this->translate('Options') ?>
      </td>
      <td>
        <?php if( $this->order && !empty($this->order->source_id) && $this->order->source_type == 'payment_subscription' ): ?>
          <?php echo $this->htmlLink(array(
            'reset' => false,
            'controller' => 'subscription',
            'action' => 'detail',
            'subscription_id' => $this->order->source_id,
            'transaction_id' => null,
          ), $this->translate('Related Subscription'), array(
            'target' => '_parent'
          )) ?>
        <?php else: ?>
          <?php echo "---"; ?>
        <?php endif; ?>
      </td>
    </tr>
  </table>
</div>
