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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_signup', 'childMenuItemName' => 'core_admin_main_settings_fields')); ?>

<?php $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_signup', array(), 'core_admin_main_settings_fields'); ?>
<h2 class="page_heading"><?php echo $this->translate('Signup & Profile Settings') ?></h2>
<?php if( count($navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($navigation)->render(); ?>
  </div>
<?php endif; ?>

<?php
  $option_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('option_id', null);
  if($option_id)
  $getProfileTypeData = Engine_Api::_()->user()->getProfileTypeData(array('option_id' => $option_id));
  
  // Render the admin js
  echo $this->render('_jsAdmin.tpl')
?>

<h2><?php echo $this->translate("Profile Questions") ?></h2>
<p>
  <?php echo $this->translate('Your members will be asked to provide some information about themselves when joining the community or editing their profile. Create some profile questions that allow them to describe themselves in a way that relates to the theme of your community. To reorder the profile questions, click on their names and drag them up or down. If you want to show different sets of questions to different types of members, you can create multiple "profile types". This is useful, for example, if you want your community to have "fans" and "musicians", each with a different set of profile questions.<br /> You can allow your members to change their profile types from <a href="admin/authorization/level">Member Level Settings</a>. You can also change individual user\'s profile type from <a href="admin/user/manage">Manage Members</a> settings.') ?>
</p>
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="https://community.socialengine.com/blogs/597/26/profile-questions" target="_blank">See KB article</a>';
} 
?>	
<br />
<br />	

<div class="admin_fields_type">
  <h3><?php echo $this->translate("Editing Profile Type:") ?></h3>
  <?php echo $this->formSelect('profileType', $this->topLevelOption->option_id, array(), $this->topLevelOptions) ?>
</div>
<div class="admin_fields_options">
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addquestion"><?php echo $this->translate("Add Question") ?></a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addheading"><?php echo $this->translate("Add Heading") ?></a>
  
  <?php if( engine_count($this->topLevelOptions) > 1 && @$getProfileTypeData == 0): ?>
    <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_renametype"><?php echo $this->translate("Rename Profile Type") ?></a>
    <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_deletetype"><?php echo $this->translate("Delete Profile Type") ?></a>
  <?php endif; ?>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addtype"><?php echo $this->translate("Create New Profile Type") ?></a>
  <?php echo $this->htmlLink(array('module' => 'authorization', 'controller' => 'level', 'action' => 'map-profile-type', 'profileTypeId' => $this->topLevelOptionId, 'option_id' => $option_id, 'reset' => false), $this->translate('Map with Member Level'), array('class' => 'smoothbox buttonlink admin_fields_options_mapping', )) ?>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_saveorder" style="display:none;"><?php echo $this->translate("Save Order") ?></a>
</div>

<ul class="admin_fields">
  <?php foreach( $this->secondLevelMaps as $map ): ?>
    <?php echo $this->adminFieldMeta($map) ?>
  <?php endforeach; ?>
</ul>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_signup').addClass('active');
</script>
