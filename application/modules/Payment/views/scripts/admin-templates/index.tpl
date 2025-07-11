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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'childMenuItemName' => 'core_admin_main_payment_templates')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
</h2>	
<?php if( count($this->navigation) ): ?>
<div class='tabs'>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
</div>
<?php endif; ?>

<h2><?php echo $this->translate("Manage Template") ?></h2>
<p class="payment_search_result"><?php echo $this->translate('From here, manage all the templates you have created for the membership subscription plans on your website. You can create as many templates as you want for the Plans.'); ?></p>

<div class="payment_search_result">
  <?php echo $this->htmlLink(array('action' => 'add-template', 'reset' => false), $this->translate('Add New Template'), array('class' => 'admin_link_btn payment_icon_add smoothbox')) ?>
</div>
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <div class="payment_search_result">
    <?php echo $this->translate(array('%s Template found.', '%s Templates found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>">
    <div class="admin_table_form">
      <table class='admin_table'>
        <thead>
          <tr>
            <th style="width:1%;" class='admin_table_short'><?php echo $this->translate("ID") ?></th>
            <th style="width:40%;"><?php echo $this->translate("Title") ?></th>
            <th style="width:40%;" class="admin_table_centered"><?php echo $this->translate("Active") ?></th>
            <th style="width:20%;"><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->paginator as $item):?>
          <tr>
            <td><?php echo $item->getIdentity() ?></td>
            <td><?php echo $this->translate($item->getTitle()); ?></td>
            <td class="admin_table_centered">
              <?php if($item->active): ?>
                <img src="application/modules/Core/externals/images/notice.png" alt="Default" />
              <?php else: ?>
                <?php echo $this->formRadio('default', $item->getIdentity(), array('onchange' => "setDefault({$item->getIdentity()});"), ''); ?>
              <?php endif; ?>
            </td>
            <td class="nowrap">
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'templates', 'action' => 'edit-template', 'packagetemplate_id' => $item->getIdentity()), $this->translate("Edit"),array('class'=>'smoothbox')) ?>
              |
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'templates', 'action' => 'styles', 'packagetemplate_id' => $item->getIdentity()), $this->translate("Manage Template Style")) ?>
              <?php if(!$item->active) { ?>
                |
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'templates', 'action' => 'delete', 'packagetemplate_id' => $item->getIdentity()), $this->translate("Delete"),array('class'=>'smoothbox')) ?> 
              <?php } ?>
              | 
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'templates', 'action' => 'preview', 'packagetemplate_id' => $item->getIdentity()), $this->translate("Preview"),array('class'=>'smoothbox')) ?> 
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </form>
  <div>
    <?php echo $this->paginationControl($this->paginator,null,null,$this->urlParams); ?>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no templates created by you yet.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="text/javascript">
  function setDefault(packagetemplate_id) {
    (scriptJquery.ajax({
      dataType: 'json',
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'payment', 'controller' => 'templates', 'action' => 'set-default'), 'admin_default', true) ?>',
      'data' : {
        'format' : 'json',
        'packagetemplate_id' : packagetemplate_id
      },
      'onRequest' : function(){
        scriptJquery('input[type=radio]').attr('disabled', true);
      },
      success : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    }));
  }
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');
</script>
