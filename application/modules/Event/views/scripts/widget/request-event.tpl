<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: request-event.tpl 9747 2012-07-26 02:08:08Z john $
 * @author	   John
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
      <a data-class="notifications_donotclose" href="javascript:void(0);" class="button btn btn-primary" type="submit" onclick='eventWidgetRequestSend("accept", <?php echo $this->string()->escapeJavascript($notification->getObject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, 2)'><?php echo $this->translate('Attending');?></a>
      
      <a data-class="notifications_donotclose" href="javascript:void(0);" class="button btn btn-primary" type="submit" onclick='eventWidgetRequestSend("accept", <?php echo $this->string()->escapeJavascript($notification->getObject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, 1)'><?php echo $this->translate('Maybe Attending');?></a>
      
      <a data-class="notifications_donotclose" href="javascript:void(0);" class="button btn btn-alt" onclick='eventWidgetRequestSend("reject", <?php echo $this->string()->escapeJavascript($notification->getObject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>)'><?php echo $this->translate('Ignore Request');?></a>
    </div>
  </div>
  <div class="notifications_item_delete">
    <a href="javascript:void(0);" class="notifications_delete_show"><i class="fa fa-ellipsis-h"></i></a>
    <div class="notifications_delete_dropdown" id="notifications_delete_dropdown" style="display:none;">
      <a id="remove_notification_update" data-class="notifications_donotclose" href="javascript:void(0);" onclick="removenotification('<?php echo $notification->getIdentity(); ?>');"><i id="remove_notification_update" data-class="notifications_donotclose" class="far fa-times-circle"></i><?php echo $this->translate("Remove this Notification"); ?></a>
    </div>
  </div>
</li>
