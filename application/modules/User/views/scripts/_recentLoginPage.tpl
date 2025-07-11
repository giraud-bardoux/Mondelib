<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _recentLoginPage.tpl 9979 2013-03-19 22:07:33Z john $
 * @author     John
 */
?>
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.recent.login', 1) && isset($_COOKIE['user_login_users']) && !empty ($_COOKIE['user_login_users'])) { ?>
  <?php $recent_login = Zend_Json::decode($_COOKIE['user_login_users']); ?>
  <?php if (engine_count($recent_login) > 0) { ?>
    <div class="recent_login">
      <div class="recent_login_head">
        <?php echo $this->translate("Recent logins"); ?>
      </div>
      <div class="recent_login_list">
        <?php foreach ($recent_login as $users) { ?>
          <?php $userArray = explode("_", $users); ?>
          <?php $user = Engine_Api::_()->getItem('user', $userArray[0]); ?>
          <?php if($user && isset($user->user_id)) { ?>
            <div id="recent_login_<?php echo $user->getIdentity(); ?>" class="recent_login_list_item">
              <a class="ajaxsmoothbox recent_login_list_item_link" id="triggerid<?php echo $user->user_id; ?>" href="<?php echo $this->baseUrl() . "/user/auth/poplogin?user_id=" . $user->user_id; ?>">
                <div class="_img">
                  <?php echo $this->itemBackgroundPhoto($user, 'thumb.icon'); ?>
                </div>
                <div class="_cont">
                  <p class="_name"><?php echo $user->getTitle(); ?></p>
                  <?php $notificationCount = Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($user); ?>
                  <?php if($notificationCount > 0) { ?>
                    <p class="_notification font_small font_color_light"><?php echo $this->translate(array('%s notification', '%s notifications', $notificationCount),$notificationCount); ?></p>
                  <?php } ?>
                </div>
              </a>
              <a href="javascript:void(0);" onclick="removeRecentUser('<?php echo $user->getIdentity(); ?>', 'recent_login_remove');" class="_close"  data-bs-toggle="modal" data-bs-target="#recent_login_remove"><i class="fa fa-times"></i></a>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
    
    <!-- Remove Account -->
    <?php include APPLICATION_PATH .  '/application/modules/User/views/scripts/_removeRecentLoginPopup.tpl';?>
    
    <script type="text/javascript">
      owlJqueryObject(".recent_login_list").owlCarousel({
          loop:false,
          responsiveClass:true,
          nav:true,
          dots:false,
          autoWidth:true,
          <?php $orientation = ($this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr');
          if($orientation == 'rtl') { ?>
          rtl:true,
        <?php  }?>
      });
      owlJqueryObject(".owl-prev").html('<i class="fa fa-angle-left"></i>');
      owlJqueryObject(".owl-next").html('<i class="fa fa-angle-right"></i>');

    </script>
  <?php } ?>
<?php } ?>
