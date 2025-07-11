<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: friendship-requests.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php if( $this->friendRequests->getTotalItemCount() > 0 ): ?>  
  <?php foreach( $this->friendRequests as $notification ): ?>
    <?php $subject = Engine_Api::_()->getItem('user', $notification->subject_id);?>
    <li id="user-widget-request-<?php echo $notification->notification_id ?>" class="clearfix <?php if( !$notification->read ): ?>pulldown_content_list_highlighted<?php endif; ?>"  value="<?php echo $notification->getIdentity();?>" onclick="redirectPage(event);">
      <div class="pulldown_item_photo">
        <?php echo $this->htmlLink($subject->getHref(), $this->itemBackgroundPhoto($subject, 'thumb.icon')) ?>
      </div>
      <div class="pulldown_item_content">
        <p class="pulldown_item_content_title">
          <?php echo $notification->__toString() ?>
        </p>
        <p class="pulldown_item_content_btns clearfix">
          <a href="javascript:void(0);" class="button" type="submit" onclick='friendRequestSend("confirm", <?php echo $this->string()->escapeJavascript($notification->getSubject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, event)'><?php echo $this->translate('Add Friend');?></a>
          <a href="javascript:void(0);" class="button" onclick='friendRequestSend("reject", <?php echo $this->string()->escapeJavascript($notification->getSubject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, event)'><?php echo $this->translate('Ignore Request');?></a>
        </p>
      </div>
    </li>
  <?php endforeach; ?>
<?php else:?>
  <div class="pulldown_loading"><?php echo $this->translate('You have no friend requests.');?></div>
<?php endif;?>
