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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'childMenuItemName' => 'core_admin_main_payment_subscriptions')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
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

<h2><?php echo $this->translate('Subscription Details') ?></h2>

<table class="payment_transaction_details">
  <tr>
    <td>
      <?php echo $this->translate('Subscription ID') ?>
    </td>
    <td>
      <?php echo $this->subscription->subscription_id ?>
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
        <?php echo $this->translate('(%s)', $this->translate('ID: %s', $this->subscription->user_id))  ?>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Current Member Level') ?>
    </td>
    <td>
      <?php if( !empty($this->actualLevel) ): ?>
        <a href='<?php echo $this->url(array('module' => 'authorization', 'controller' => 'level', 'action' => 'edit', 'id' => $this->actualLevel->level_id)) ?>'>
          <?php echo $this->translate($this->actualLevel->getTitle()) ?>
        </a>
      <?php else: ?>
        <?php echo $this->translate('N/A') ?>
      <?php endif; ?>
    </td>
  </tr>
  <tr>   
    <td>
      <?php echo $this->translate('Plan') ?>
    </td>
    <td>
      <?php if($this->subscription->resource_type != 'payment_verificationpackage') { ?>
        <a href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'package', 'action' => 'edit', 'package_id' => $this->package->package_id)) ?>'>
          <?php echo $this->translate($this->package->title) ?>
        </a>
      <?php } else if($this->subscription->resource_type == 'payment_verificationpackage') { ?>
        <?php $verficationPackage = Engine_Api::_()->getItem($this->subscription->resource_type, $this->subscription->resource_id); ?>
        <?php if($verficationPackage) { ?>
          <?php echo $this->translate($this->level->title); ?>
        <?php } else { ?>
          <?php echo "---"; ?>
        <?php } ?>
      <?php } ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Plan Member Level') ?>
    </td>
    <td>
      <a href='<?php echo $this->url(array('module' => 'authorization', 'controller' => 'level', 'action' => 'edit', 'id' => $this->level->level_id)) ?>'>
        <?php echo $this->translate($this->level ? $this->level->getTitle() : 'Default Level') ?>
      </a>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Subscription State') ?>
    </td>
    <td>
      <?php echo $this->translate(ucfirst($this->subscription->status)) ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Created') ?>
    </td>
    <td>
      <?php echo $this->locale()->toDateTime($this->subscription->creation_date) ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Expires') ?>
    </td>
    <td>
      <?php if( empty($this->subscription->expiration_date) ||
          $this->subscription->expiration_date == '0000-00-00 00:00:00' ): ?>
        <?php echo $this->translate('N/A') ?>
      <?php else: ?>
        <?php echo $this->locale()->toDateTime($this->subscription->expiration_date) ?>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo $this->translate('Options') ?>
    </td>
    <td>
      <a href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'index'), null, true) ?>?user_id=<?php echo $this->subscription->user_id ?>'>
        <?php echo $this->translate('Member Subscription History') ?>
      </a>
      |
      <a href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'index', 'action' => 'index'), null, true) ?>?user_id=<?php echo $this->subscription->user_id ?>'>
        <?php echo $this->translate('Member Transaction History') ?>
      </a>
      <br />
      <a href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'cancel'), null, true) ?>?subscription_id=<?php echo $this->subscription->subscription_id ?>' class="smoothbox">
        <?php echo $this->translate('Cancel Subscription') ?>
      </a>
      |
      <a class="smoothbox" href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'edit'), null, true) ?>?subscription_id=<?php echo $this->subscription->subscription_id ?>'>
        <?php echo $this->translate('Edit Subscription') ?>
      </a>
    </td>
  </tr>
</table>
<br>

<h3 class="payment_transaction_detail_headline">
  <?php echo $this->translate('Related Transactions') ?>
</h3>

<table class="admin_table payment_transaction_list">
  <thead>
    <tr>
      <th>
        <?php echo $this->translate('Transaction ID') ?>
      </th>
      <th>
        <?php echo $this->translate('Gateway') ?>
      </th>
      <th>
        <?php echo $this->translate('Type') ?>
      </th>
      <th>
        <?php echo $this->translate('State') ?>
      </th>
      <th>
        <?php echo $this->translate('Amount') ?>
      </th>
      <th>
        <?php echo $this->translate('Date') ?>
      </th>
      <th>
        <?php echo $this->translate('Options') ?>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php foreach( $this->transactions as $transaction ):
      $gateway = @$this->gateways[$transaction->gateway_id];
      $order = @$this->orders[$transaction->order_id];
      ?>
    <tr>
      <td>
        <?php echo $transaction->transaction_id ?>
      </td>
      <td>
        <?php if( $gateway ): ?>
          <?php echo $this->translate($gateway->title) ?>
        <?php elseif( $transaction->gateway_id == 3000 ): ?>
          <?php echo $this->translate("Wallet") ?>
        <?php else: ?>
          <i><?php echo $this->translate('Unknown Gateway') ?></i>
        <?php endif; ?>
      </td>
      <td>
        <?php echo $this->translate(ucfirst($transaction->type)) ?>
      </td>
      <td>
        <?php echo $this->translate(ucfirst($transaction->state)) ?>
      </td>
      <td>
        <?php echo $this->translate('%s ', $transaction->currency) . Engine_Api::_()->payment()->getCurrencyPrice($transaction->amount, $transaction->currency); ?>
        <?php if($transaction->currency != $transaction->current_currency) { ?>
          <?php echo '('.$this->translate('%s', $transaction->current_currency) .' ' .Engine_Api::_()->payment()->getCurrencyPrice($transaction->amount * $transaction->change_rate, $transaction->current_currency) .')'; ?>
        <?php } ?>
      </td>
      <td>
        <?php echo $this->locale()->toDateTime($transaction->timestamp) ?>
      </td>
      <td class='admin_table_options'>
        <a class="ajaxsmoothbox" href='<?php echo $this->url(array('controller' => 'index', 'action' => 'detail', 'transaction_id' => $transaction->transaction_id));?>'>
          <?php echo $this->translate("details") ?>
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');
</script>
