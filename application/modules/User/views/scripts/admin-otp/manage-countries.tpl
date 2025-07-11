<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: countries.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_otp', 'childMenuItemName' => 'core_admin_otp_countries')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("OTP Settings") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_otp').addClass('active');

  en4.core.runonce.add(function() {
    scriptJquery("#selectall").click(function(){
      if(this.checked){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', true);
        });
      } else {
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', false);
        });
      }
    });
    
    scriptJquery("input[name='enable'],input[name='disable']").on('click', function( event ) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to perform this action on selected entries?")) ?>')) {
          scriptJquery('#multidelete_form').append("<input type='hidden' value='"+name+"' name='"+name+"'>");
          scriptJquery('#multidelete_form').trigger("submit");
        }
      }
    });
  });
</script>

<h3><?php echo $this->translate("Manage Countries") ?></h3>

<p><?php echo $this->translate('This page lists all the countries you can enable disable on your website for country code. Also, you can choose the icons for the countries and make any country code default for your website.'); ?></p>

<div class='admin_search admin_common_search admin_manage_activity_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0) { ?> 
  <div class='clear'>
    <form id="multidelete_form" action="<?php echo $this->url();?>" method="POST">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="_count">
          <?php echo $this->translate(array('%s entry found.', '%s entries found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
        </div>
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <input type='submit' value="Enable" name="enable" class="btn btn-success">
          <input type='submit' value="Disable" name="disable" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator); ?>
        </div>
      </div>
      <table class='admin_table admin_responsive_table'>
        <thead>
          <tr>
            <th class='admin_table_short'><input id="selectall" type='checkbox' /></th>
            <th class="admin_table_centered"><?php echo $this->translate('Icon') ?></th>
            <th class="admin_table_centered"><?php echo $this->translate('Country Name') ?></th>
            <th class="admin_table_centered"><?php echo $this->translate('Phone Code') ?></th>
            <th class="admin_table_centered"><?php echo $this->translate('Enabled') ?></th>
            <th><?php echo $this->translate('Option') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->paginator as $key => $item) { ?>
            <tr id="order_<?php echo $item->getIdentity(); ?>">
              <td><input type='checkbox' class='checkbox' name='selectedItems[]' value="<?php echo $item->getIdentity() ?>"/></td>
              <td data-label="<?php echo $this->translate('Icon') ?>" class="admin_table_centered">
                <?php if(isset($item->icon) && !empty($item->icon)) { ?>
                  <?php $path = Engine_Api::_()->core()->getFileUrl($item->icon); ?>
                  <?php if($path) { ?>
                    <div class="countries_icon"> 
                      <img class="icon " src="<?php echo $path; ?>" alt="<?php echo $item->name; ?>" />
                    </div>  
                  <?php } ?>
                <?php } else { echo "---"; } ?>
              </td>
              <td data-label="<?php echo $this->translate('Country Name') ?>" class="admin_table_centered">
                <?php echo $item->name; ?>
              </td>
              <td data-label="<?php echo $this->translate('Phone Code') ?>" class="admin_table_centered"><?php echo '+'.$item->phonecode; ?></td>
              <td data-label="<?php echo $this->translate('Enabled') ?>" class="admin_table_centered">
                <?php if($item->iso2 != Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) { ?>
                  <?php if($item->enabled): ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'otp', 'action' => 'enable', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('data-bs-toggle' => "tooltip", 'data-bs-placement' => "bottom", 'data-bs-original-title' => $this->translate('Disable')))) ?>
                  <?php else: ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'otp', 'action' => 'enable', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/uncheck.png', '', array('data-bs-toggle' => "tooltip", 'data-bs-placement' => "bottom", 'data-bs-original-title' => $this->translate('Enable')))); ?>
                  <?php endif; ?>
                <?php } else { ?>
                  <img src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png'; ?>" alt="" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="<?php echo $this->translate('This is the default country, so before disabling it, please choose another default country for your site.'); ?>">
                <?php } ?>
              </td>
              <td class="admin_table_options nowrap">
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'otp', 'action' => 'edit-country', 'id' => $item->getIdentity()), $this->translate("Edit"),array('class' => 'smoothbox')) ?>
                <?php if($item->iso2 == Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')){ ?>
                |
                  <?php echo $this->translate("Default"); ?>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no country available matching your search criteria.") ?>
    </span>
  </div>
<?php } ?>
