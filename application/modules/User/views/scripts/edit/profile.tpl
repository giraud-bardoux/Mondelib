<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: profile.tpl 9984 2013-03-20 00:00:04Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('item' => $this->user, 'modulename' => 'user')); ?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
      <?php
        /* Include the common user-end field switching javascript */
        echo $this->partial('_jsSwitch.tpl', 'fields', array(
          'topLevelId' => (int) @$this->topLevelId,
          'topLevelValue' => (int) @$this->topLevelValue
        ));

        $this->headTranslate(array(
          'Everyone', 'All Members', 'Friends', 'Only Me',
        ));
      ?>
      <script type="text/javascript">
        en4.core.runonce.add(function() {
          en4.user.buildFieldPrivacySelector(
            scriptJquery('.global_form *[data-field-id]'),
          );
        });
      </script> 
      <div class="user_profile_edit user_setting_global_form">
        <?php if(!empty($this->editProfileType) && Engine_Api::_()->authorization()->getPermission($this->user, 'user', 'editprofiletype')) { ?>
          <div class="user_edit_profiletype_link"><a href="<?php echo $this->url(array('controller' => 'edit', 'action' => 'edit-profile-type', 'id' => $this->user->getIdentity()), 'user_extended', true); ?>" class="smoothbox"><i class="fas fa-user-edit"></i><span><?php echo $this->translate("Edit Profile Type"); ?></span></a></div>
        <?php } ?>
        <?php echo $this->form->render($this) ?> 
      </div>
    </div>
  </div>
</div>
