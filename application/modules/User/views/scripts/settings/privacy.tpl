<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: privacy.tpl 9873 2013-02-13 00:39:46Z shaun $
 * @author     Steve
 */
?>
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
      <div class="user_setting_global_form user_setting_notification_form">
        <?php echo $this->form->render($this) ?>
      </div>
      <?php if(engine_count($this->blockedUsers) > 0) { ?>
        <div id="blockedUserList" style="display:none;">
          <ul class="blocklist">
            <?php foreach ($this->blockedUsers as $user): ?>
              <?php if($user instanceof User_Model_User && $user->getIdentity()) :?>
                <li>
                  <?php echo $this->itemBackgroundPhoto($user, 'thumb.icon', $user->getTitle()) ?>
                  <span class="_name"><?php echo $user->getTitle() ?></span>
                  <?php echo $this->htmlLink(array('controller' => 'block', 'action' => 'remove', 'user_id' => $user->getIdentity()), 'Unblock', array('class'=>'smoothbox btn btn-primary')) ?>
                </li>
              <?php endif;?>
            <?php endforeach; ?>
          </ul>
        </div>
        <script type="text/javascript">
        <!--
        en4.core.runonce.add(function() {
          scriptJquery('#blockedUserList ul').appendTo(scriptJquery('#blockList-element'));
        });
        // -->
        </script>
      <?php } else { ?>
        <script type="text/javascript">
          scriptJquery('#blockList-wrapper').hide();
        </script>
      <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript">
<!--
scriptJquery(document).ready(function() {
  scriptJquery('#blockedUserList ul').appendTo(scriptJquery('#blockList-element'));
});
// -->

<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('userprofilelock')) { ?>
	<?php if(!empty($this->user->profilelock)) { ?>
		scriptJquery(document).ready(function() {
			scriptJquery('#privacy-wrapper').remove();
		}); 
	<?php } ?>
<?php } ?>
</script>
