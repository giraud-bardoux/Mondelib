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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'childMenuItemName' => 'core_admin_main_payment_packages')); ?>

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
<div class="admin_common_top_section">
  <h3>
    <?php echo $this->translate("Manage Subscription Plans") ?>
  </h3>
  <p>
    <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_PACKAGES_INDEX_DESCRIPTION") ?>
  </p>
   <p>
    <?php
      $settings = Engine_Api::_()->getApi('settings', 'core');
      if( $settings->getSetting('user.support.links', 0) == 1 ) {
        echo 'More info: <a href="https://community.socialengine.com/blogs/597/77/plans" target="_blank">See KB article</a>.';
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

<?php /*return; */ endif; ?>
<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Plan'), array(
    'class' => 'admin_link_btn icon_plan_add',
  )) ?>
</div>
<?php //if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <script type="text/javascript">
    var currentOrder = '<?php echo $this->filterValues['order'] ?>';
    var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        document.getElementById('direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        document.getElementById('order').value = order;
        document.getElementById('direction').value = default_direction;
      }
      scriptJquery('#filter_form').trigger('submit');
    }
  </script>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>
<?php //endif; ?>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s plan found", "%s plans found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="table-responsive">
    <table class='admin_table'>
      <thead>
        <tr>
          <?php $class = ( $this->order == 'package_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class="<?php echo $class ?>">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('package_id', 'DESC');">
              <?php echo $this->translate("ID") ?>
            </a>
          </th>
          <?php $class = ( $this->order == 'title' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th class="<?php echo $class ?>">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');">
              <?php echo $this->translate("Title") ?>
            </a>
          </th>
          <?php $class = ( $this->order == 'level_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
            <a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', 'ASC');">
              <?php echo $this->translate("Member Level") ?>
            </a>
          </th>
          <?php $class = ( $this->order == 'price' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class="<?php echo $class ?>">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('price', 'DESC');">
              <?php echo $this->translate("Price") ?>
            </a>
          </th>
          <th style='width: 1%;'>
            <?php echo $this->translate("Billing") ?>
          </th>
          <?php $class = ( $this->order == 'enabled' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
            <a href="javascript:void(0);" onclick="javascript:changeOrder('enabled', 'DESC');">
              <?php echo $this->translate("Enabled?") ?>
            </a>
          </th>
          <?php $class = ( $this->order == 'signup' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
            <a href="javascript:void(0);" onclick="javascript:changeOrder('signup', 'DESC');">
              <?php echo $this->translate("Signup?") ?>
            </a>
          </th>
          <?php $class = ( $this->order == 'default' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
          <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
            <a href="javascript:void(0);" onclick="javascript:changeOrder('default', 'DESC');">
              <?php echo $this->translate("Default?") ?>
            </a>
          </th>
          <th style='width: 1%;' class='admin_table_centered'>
            <?php echo $this->translate("Active Members") ?>
          </th>
          <th style='width: 1%;' class='admin_table_options'>
            <?php echo $this->translate("Options") ?>
          </th>
        </tr>
      </thead>
      <tbody id="menu_list"> 
        <?php foreach( $this->paginator as $item ): ?>
          <tr id="order_<?php echo $item->getIdentity(); ?>">
            <td><?php echo $item->package_id ?></td>
            <td class='admin_table_bold nowrap'>
              <?php echo $item->title ?>
            </td>
            <td class='admin_table_centered'>
              <?php if( $item->level_id ): ?>
                <?php if( ($level = Engine_Api::_()->getItem('authorization_level', $item->level_id)) ): ?>
                  <a href='<?php echo $this->url(array('module' => 'authorization','controller' => 'level', 'action' => 'edit', 'id' => $item->level_id)) ?>'>
                    <?php echo $this->translate($level->getTitle()) ?>
                  </a>
                <?php else: ?>
                  <em><?php echo $this->translate('Missing Level')?></em>
                <?php endif ?>
              <?php else: ?>
                <em><?php echo $this->translate('Not assigned')?></em>
              <?php endif ?>
            </td>
            <td>
              <?php echo Engine_Api::_()->payment()->getCurrencyPrice($item->price, Engine_Api::_()->payment()->defaultCurrency()); //$this->locale()->toNumber($item->price, array('default_locale' => true)) ?>
            </td>
            <td class="nowrap">
              <?php echo $item->getPackageDescription(true) ?>
            </td>
            <td class='admin_table_centered'>
              <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
            </td>
            <td class='admin_table_centered'>
              <?php echo ( $item->signup ? $this->translate('Yes') : $this->translate('No') ) ?>
            </td>
            <td class='admin_table_centered'>
              <?php echo ( $item->default ? $this->translate('Yes') : $this->translate('No') ) ?>
            </td>
            <td class='admin_table_centered'>
              <?php echo $this->locale()->toNumber(@$this->memberCounts[$item->package_id], array('default_locale' => true)) ?>
            </td>
            <td class='admin_table_options'>
              <a href='<?php echo $this->url(array('action' => 'edit', 'package_id' => $item->package_id)) ?>'>
                <?php echo $this->translate("edit") ?>
              </a>
              |
              <a href='<?php echo $this->url(array('controller' => 'subscription', 'action' => 'index', 'package_id' => $item->package_id));?>'>
                <?php echo $this->translate("subscriptions") ?>
              </a>
              |
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'package', 'action' => 'features', 'package_id' => $item->getIdentity()), $this->translate('Manage Features'), array()) ?>
              |
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'package', 'action' => 'change-styles', 'package_id' => $item->getIdentity()), $this->translate("Edit Style")) ?> 
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>  
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');

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
