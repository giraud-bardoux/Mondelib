<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_payment', 'childMenuItemName' => 'core_admin_main_payment_gateways')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Billing") ?>
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
    <?php echo $this->translate("Manage Payment Gateways") ?>
  </h3>
  <p>
    <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_GATEWAYS_INDEX_DESCRIPTION") ?>
  </p>
  <p>
    <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/76/gateways" target="_blank">See KB article</a>.';
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
<?php endif; ?>


<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s gateway found", "%s gateways found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
</div>
<table class='admin_table admin_responsive_table'>
  <thead>
    <tr>
      <th><?php echo $this->translate("Title") ?></th>
      <th><?php echo $this->translate("Icon") ?></th>
      <th class='admin_table_centered'><?php echo $this->translate("Enabled") ?></th>
      <th style='width: 1%;'><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if( engine_count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): 
          $config = (array) $item['config'];
      ?>
        <tr>
          <td data-label="<?php echo $this->translate("Title") ?>" class='admin_table_bold'>
            <?php echo $item->title ?>
          </td>
          <td data-label="<?php echo $this->translate("Icon") ?>" class='admin_table_bold'>
            <?php if(isset($config['icon']) && !empty($config['icon'])) { ?>
              <?php $path = Engine_Api::_()->core()->getFileUrl($config['icon']); ?>
              <img src="<?php echo $path; ?>" class="table_img" alt="img" />
            <?php } else { echo "---"; }  ?>
          </td>
          <td data-label="<?php echo $this->translate("Enabled") ?>" class='admin_table_centered'>
            <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_options'>
            <a href='<?php echo $this->url(array('action' => 'edit', 'gateway_id' => $item->gateway_id));?>'>
              <?php echo $this->translate("edit") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_payment').addClass('active');
</script>
