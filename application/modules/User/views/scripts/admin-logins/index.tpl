<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_banning_general', 'childMenuItemName' => 'user_admin_banning_logins')); ?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<h2 class="page_heading">
  <?php echo $this->translate("Login History") ?>
</h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<p>
  <?php echo $this->translate("USER_VIEWS_SCRIPTS_ADMINLOGINS_INDEX_DESCRIPTION") ?>
</p>
<?php if( $this->formFilter ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>
  <script type="text/javascript">
    var currentOrder = '<?php echo $this->order ?>';
    var currentOrderDirection = '<?php echo $this->order_direction ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        scriptJquery('#order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        scriptJquery('#order').val(order);
        scriptJquery('#order_direction').val(default_direction);
      }
      scriptJquery('#filter_form').trigger("submit");
    }
  </script>
<?php endif ?>
<div>
  <?php echo $this->htmlLink(array(
    'action' => 'clear',
    'reset' => false,
  ), 'Clear History', array(
    'class' => 'smoothbox admin_referrers_btn admin_link_btn',
  )) ?>
</div>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s sign-in found", "%s sign-ins found", $count), $this->locale()->toNumber($count)) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
      //'params' => $this->formValues,
    )); ?>
  </div>
</div>
<table class='admin_table admin_responsive_table'>
  <thead>
    <tr>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('login_id', 'DESC');">
          <?php echo $this->translate("ID") ?>
        </a>
      </th>
      <th>
        <a href="javascript:void(0);" onclick="changeOrder('user_id', 'ASC');">
          <?php echo $this->translate("Member") ?>
        </a>
      </th>
      <th>
        <a href="javascript:void(0);" onclick="changeOrder('email', 'ASC');">
          <?php echo $this->translate("Email Address") ?>
        </a>
      </th>
      <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
        <th><?php echo $this->translate("Phone Number") ?></th>
      <?php } ?>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('ip', 'ASC');">
          <?php echo $this->translate("IP Address") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('state', 'ASC');">
          <?php echo $this->translate("State") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('timestamp', 'DESC');">
          <?php echo $this->translate("Timestamp") ?>
        </a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php if( engine_count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <?php if($item->user_id) { ?>
          <?php $user = Engine_Api::_()->getItem('user', $item->user_id); ?>
        <?php } ?>
        <tr class="admin_logins_<?php echo ( $item->state == 'success' ? 'okay' : 'error' ) ?> admin_logins_type_<?php echo str_replace('-', '_', $item->state) ?>">
          <td data-label="ID">
            <?php echo $this->locale()->toNumber($item->login_id) ?>
          </td>
          <td data-label="<?php echo $this->translate("Member") ?>">
            <?php if( isset($this->users[$item->user_id]) ): ?>
              <?php echo $this->users[$item->user_id]->__toString() ?>
            <?php else: ?>
              <?php echo $this->translate('N/A') ?>
            <?php endif ?>
          </td>
          <td data-label="<?php echo $this->translate("Email Address") ?>">
            <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
              <?php if($user && !empty($user->email)): ?>
                <?php echo $user->email ?>
              <?php else: ?>
                <?php echo is_numeric($item->email) ? '---' : $item->email; ?>
              <?php endif; ?>
            <?php else: ?>
              <?php echo $this->translate('(hidden)') ?>
            <?php endif ?>
          </td>
          <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
            <td data-label="<?php echo $this->translate("Phone Number") ?>">
              <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
                <?php if($user && !empty($user->phone_number)): ?>
                  <?php echo $user->phone_number; ?>
                <?php else: ?>
                  ---
                <?php endif; ?>
              <?php else: ?>
                <?php echo $this->translate('(hidden)') ?>
              <?php endif; ?>
            </td>
          <?php } ?>
          <td data-label="<?php echo $this->translate("IP Address") ?>" class="nowrap">
            <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
              <?php
                $ipObj = new Engine_IP($item->ip);
                echo $ipObj->toString()
              ?>
            <?php else: ?>
              <?php echo $this->translate('(hidden)') ?>
            <?php endif ?>
          </td>
          <td data-label="<?php echo $this->translate("State") ?>" class="nowrap">
            <?php echo $this->translate(ucwords(str_replace('-', ' ', $item->state))) ?>
          </td>
          <td data-label="<?php echo $this->translate("Timestamp") ?>" class="nowrap">
            <?php echo $this->locale()->toDateTime($item->timestamp) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_spam').addClass('active');
</script>
