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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_verification', 'childMenuItemName' => 'core_admin_main_settings_verification')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
</h2>	
<?php if( count($this->navigation) ): ?>
<div class='tabs'>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
</div>
<?php endif; ?>
<div class="admin_common_top_section">
  <h3>
    <?php echo $this->translate("Manage Verification Plans") ?>
  </h3>
  <p>
    <?php echo $this->translate("Browse and manage verification plans.") ?>
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
<!--<div>
  <?php //echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Plan'), array('class' => 'admin_link_btn icon_plan_add')); ?>
</div>-->
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
<!--  <div class='admin_search'>
    <?php //echo $this->formFilter->render($this) ?>
  </div>-->
<?php //endif; ?>
<!--<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s plan found.", "%s plans found.", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>-->
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="table-responsive">
    <table class='admin_table'>
      <thead>
        <tr>
          <th style='width: 1%;'>
            <?php echo $this->translate("ID") ?>
          </th>
          <th style='width: 1%;'>
            <?php echo $this->translate("Member Level") ?>
          </th>
          <th style='width: 1%;'>
            <?php echo $this->translate("Billing") ?>
          </th>
          <th style='width: 1%;' class='admin_table_options'>
            <?php echo $this->translate("Option") ?>
          </th>
        </tr>
      </thead>
      <tbody> 
        <?php foreach( $this->paginator as $item ): ?>
          <tr>
            <td><?php echo $item->verificationpackage_id ?></td>
            <td>
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
            <td class="nowrap">
              <?php if($item->verified == 4) { ?>
              <?php echo $item->getPackageDescription(true) ?>
              <?php } else { ?>
                ---
              <?php } ?>
            </td>
            <td class='admin_table_options'>
              <a href='<?php echo $this->url(array('action' => 'edit', 'verificationpackage_id' => $item->verificationpackage_id, 'level_id' => $item->level_id)) ?>'>
                <?php echo $this->translate("edit") ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
