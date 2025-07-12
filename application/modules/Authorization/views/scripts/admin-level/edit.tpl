<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'authorization_admin_main_manage', 'childMenuItemName' => 'authorization_admin_main_level')); ?>

<?php $permissionTable = $this->permissionTable; ?>

<script type="text/javascript">
  var fetchLevelSettings = function(level_id) {
    window.location.href = en4.core.baseUrl + 'admin/authorization/level/edit/id/' + level_id;
    //alert(level_id);
  }

</script>

<h2 class="page_heading">
  <?php echo $this->translate("Member Levels") ?>
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

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this) ?>
  </div>
</div>
<script type="text/javascript">

  function hideShowMessageSettings(value) {
    if(value == 'none') {
      scriptJquery('#messages_editor-wrapper').hide();
    } else {
      scriptJquery('#messages_editor-wrapper').show();
    } 
  }

  function showProfileMemberLevel(value) { 
    if(value == 1) {
      scriptJquery('#editprotylevel-wrapper').show();
    } else {
      scriptJquery('#editprotylevel-wrapper').hide();
    } 
  }
  
  function showPreview() {
    Smoothbox.open(scriptJquery('#show_default_preview'));
  }

  en4.core.runonce.add(function() {
    showProfileMemberLevel('<?php echo $permissionTable->getAllowed('user', $this->level_id, 'editprofiletype'); ?>');

    hideShowMessageSettings('<?php echo $permissionTable->getAllowed('messages', $this->level_id, 'auth'); ?>');

    showHideSettings('changeemail', '<?php echo $permissionTable->getAllowed('user', $this->level_id, 'changeemail'); ?>');
    
    priceVerified('<?php echo $permissionTable->getAllowed('user', $this->level_id, 'paid_verified'); ?>');
  });
  
  function showHideSettings(settingName, value) {
    if(value == 1) {
      if(settingName == 'changeemail') {
        scriptJquery('#emailverify-wrapper').show();
      }
      
    } else {
      if(settingName == 'changeemail') {
        scriptJquery('#emailverify-wrapper').hide();
      }
    }
  }
  
  function priceVerified(value) {
    if(value == 1) {
      scriptJquery('#price_verified-wrapper').show();
      scriptJquery('#recurrence-wrapper').show();
    } else { 
      scriptJquery('#price_verified-wrapper').hide();
      scriptJquery('#recurrence-wrapper').hide();
    }
  }
</script>

<style type="text/css">
  .is_hidden {
    display: none;
  }
</style>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_levels').addClass('active');
</script>
