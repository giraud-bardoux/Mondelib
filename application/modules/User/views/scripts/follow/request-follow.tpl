<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: request-follow.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php $notification = $this->notification; ?>


<li <?php if( !$notification->read ): ?> class="notifications_unread"<?php endif; ?> id="notifications_<?php echo $notification->getIdentity();?>" value="<?php echo $notification->getIdentity();?>">
  <div class="notification_item_photo">
    <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id);?>
    <?php if($notification->getContentObject() && ($notification->getContentObject() instanceof Core_Model_Item_Abstract)): ?>
      <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon',$notification->getContentObject()->getTitle(),array("class"=>"notification_subject_icon"))) ?>
    <?php endif; ?>
  </div>
  <div class="notification_item_content">
    <div class="notification_item_title">
      <?php echo $notification->__toString() ?>
    </div>
    <div class="notification_item_date notification_item_general notification_type_<?php echo $notification->type ?>">
      <?php echo $this->timestamp($notification->date); ?>
    </div>
    <div class="notification_item_buttons">
      <?php $getFollowUserStatus = Engine_Api::_()->getDbTable('follows', 'user')->getFollowUserStatus($user->user_id); ?>
      
      <a data-class="notifications_donotclose" data-notification-id="<?php echo $notification->getIdentity();?>" id="user_follow_accept_<?php echo $getFollowUserStatus['follow_id']; ?>" href='javascript:;' data-action="accept" data-follow_id="<?php echo $getFollowUserStatus['follow_id']; ?>" data-url='<?php echo $user->getIdentity(); ?>' class='button btn btn-primary user_follow follow_accept_btn user_follow_<?php echo $user->getIdentity(); ?>'><?php echo $this->translate('Confirm'); ?></a>
      
      <a data-class="notifications_donotclose" data-notification-id="<?php echo $notification->getIdentity();?>" id="user_follow_reject_<?php echo $getFollowUserStatus['follow_id']; ?>" href='javascript:;' data-action="reject" data-follow_id="<?php echo $getFollowUserStatus['follow_id']; ?>" data-url='<?php echo $user->getIdentity(); ?>' class='button btn btn-alt user_follow follow_reject_btn user_follow_<?php echo $user->getIdentity(); ?>'><?php echo $this->translate('Delete'); ?></a>
    </div>
  </div>
  <div class="notifications_item_delete">
    <a href="javascript:void(0);" class="notifications_delete_show"><i class="fa fa-ellipsis-h"></i></a>
    <div class="notifications_delete_dropdown" id="notifications_delete_dropdown" style="display:none;">
      <a id="remove_notification_update" data-class="notifications_donotclose" href="javascript:void(0);" onclick="removenotification('<?php echo $notification->getIdentity(); ?>');"><i id="remove_notification_update" data-class="notifications_donotclose" class="far fa-times-circle"></i><?php echo $this->translate("Remove this Notification"); ?></a>
    </div>
  </div>
</li>
