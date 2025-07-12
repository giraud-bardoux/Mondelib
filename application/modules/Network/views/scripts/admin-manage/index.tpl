<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_networks','childMenuItemName' => 'core_admin_main_managenetworks')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Manage Networks") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<p>
  <?php $link = $this->htmlLink(
    array('module' => 'activity', 'controller' => 'settings', 'action' => 'index','route'=>'admin_default','reset'=>true),
    $this->translate('here')); ?>
  <?php echo $this->translate("NETWORK_VIEWS_SCRIPTS_ADMINMANAGE_INDEX_DESCRIPTION", $link) ?>
</p>
<?php
	$settings = Engine_Api::_()->getApi('settings', 'core');
	if( $settings->getSetting('user.support.links', 0) == 1 ) {
		echo 'More info: <a href="https://community.socialengine.com/blogs/597/15/networks" target="_blank">See KB article</a>.';
	} 
?>	
<script type="text/javascript">
  var changeOrder = function(newOrder) {
    var order = scriptJquery('#order').val();
    var direction = scriptJquery('#direction').val();
    
    if( order != newOrder ) {
      scriptJquery('#order').val(newOrder);
      scriptJquery('#direction').val('ASC');
    } else {
      scriptJquery('#order').val(newOrder);
      scriptJquery('#direction').val(( direction == 'ASC' ? 'DESC' : 'ASC' ) );
    }
    scriptJquery('#order').parents('form').trigger("submit");
  }
  var checkAll = function(pel) {
    var state = pel.checked;
    scriptJquery('input[id=actions]').each(function(el){
      scriptJquery(this).prop("checked",state);
    });
  }
</script>

<?php echo $this->formFilter->render($this) ?>

<div class="add_network_section">
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Network'), array(
    'class' => 'admin_link_btn',
    
  )) ?>
</div>

<?php if( engine_count($this->paginator) ): ?>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->formValues,
      'pageAsQuery' => true,
    )); ?>
  <form id='delete_selected' method='post' action='<?php echo $this->url(array('action' => 'deleteselected')) ?>'>
    <table class='admin_table admin_responsive_table'>
      <thead>
        <tr>
          <th style="width: 1%;">
            <input type='checkbox' class='checkbox' id="checkall" onchange="checkAll(this);" />
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('network_id');">
              <?php echo $this->translate("ID") ?>
            </a>
          </th>
          <th>
            <a href="javascript:void(0);" onclick="javascript:changeOrder('title');">
              <?php echo $this->translate("Network Name") ?>
            </a>
          </th>
          <th >
            <?php echo $this->translate("Related Profile Question") ?>
          </th>
          <th  style="width: 150px;">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('member_count');">
              <?php echo $this->translate("Members") ?>
            </a>
          </th>
          <th style="width: 150px;">
            <?php echo $this->translate("Options") ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach( $this->paginator as $network ): ?>
        <tr>
          <td >
            <?php echo $this->formCheckbox('actions[]', $network->network_id) ?>
          </td>
          <td data-label="ID">
            <?php echo $this->locale()->toNumber($network->network_id) ?>
          </td>
          <td data-label="<?php echo $this->translate("Network Name") ?>" class="admin_table_bold">
            <?php echo $network->getTitle() ?>
          </td>
          <td data-label="<?php echo $this->translate("Related Profile Question") ?>">
            <?php echo $this->networkField($network, $this->fields) ?>
          </td>
          <td data-label="<?php echo $this->translate("Members") ?>">
            <?php $count = $network->getMemberCount();?>
            <?php if($count):?><a class='smoothbox' href='<?php echo $this->url(array('action' => 'members', 'network_id' => $network->network_id));?>'><?php endif;?>
              <?php
                echo $this->translate(array('%s member', '%s members', $count), $count)
              ?>
            <?php if($count):?></a><?php endif;?>
          </td>
          <td class="admin_table_options">
            <?php echo $this->htmlLink(array('action' => 'edit', 'id' => $network->network_id, 'reset' => false), $this->translate('edit')) ?> 
            |
            <?php echo $this->htmlLink(array('action' => 'delete', 'id' => $network->network_id, 'reset' => false, 'format' => 'smoothbox'), $this->translate('delete'), array('class' => 'smoothbox')) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class='buttons'>
      <button type='submit'>
        <?php echo $this->translate("Delete Selected") ?>
      </button>
    </div>
  </form>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are currently no networks.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_networks').addClass('active');
</script>
