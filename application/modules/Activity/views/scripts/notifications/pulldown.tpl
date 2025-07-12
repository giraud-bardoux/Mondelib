<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: pulldown.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<?php foreach( $this->notifications as $notification ): ?>

  <?php if(!empty($notification->getTypeInfo()->handler)) { ?>
    <?php 
      try {
        $parts = explode('.', $notification->getTypeInfo()->handler);
        echo $this->action($parts[2], $parts[1], $parts[0], array('notification' => $notification));
      } catch( Exception $e ) {
  //       if( APPLICATION_ENV === 'development' ) {
  //         echo $e->__toString();
  //       }
  //       continue;
      }
    ?>
  <?php } else { ?>
    <li <?php if( !$notification->read ): ?> class="notifications_unread"<?php endif; ?> id="notifications_<?php echo $notification->getIdentity();?>" value="<?php echo $notification->getIdentity();?>">
      <div class="notification_item_photo">
        <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id); ?>
        <?php if($notification->getContentObject() && ($notification->getContentObject() instanceof Core_Model_Item_Abstract)): ?>
          <?php if($this->viewer()->isAdmin() || !engine_in_array($notification->type, array('content_ticketreply', 'content_newticketcreate'))) { ?>
            <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon',$notification->getContentObject()->getTitle(),array("class"=>"notification_subject_icon"))) ?>
          <?php } ?>
        <?php endif; ?>
      </div>
      <div class="notification_item_content">
        <div class="notification_item_title">
          <?php echo $notification->__toString() ?>
        </div>
        <div class="notification_item_date notification_item_general notification_type_<?php echo $notification->type ?>">
          <?php echo $this->timestamp($notification->date); ?>
        </div>
        <?php if($notification->type == 'friend_request') { ?>
          <div class="notification_item_buttons">
            <a href="javascript:void(0);" class="button btn btn-primary" type="submit" onclick='friendRequestSend("confirm", <?php echo $this->string()->escapeJavascript($notification->getSubject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, event)'><?php echo $this->translate('Add Friend');?></a>
            <a href="javascript:void(0);" class="button  btn btn-alt" onclick='friendRequestSend("reject", <?php echo $this->string()->escapeJavascript($notification->getSubject()->getIdentity()) ?>, <?php echo $notification->notification_id ?>, event)'><?php echo $this->translate('Ignore Request');?></a>
          </div>
        <?php } ?>
      </div>
      <div class="notifications_item_delete">
        <a href="javascript:void(0);" class="notifications_delete_show"><i class="fa fa-ellipsis-h"></i></a>
        <div class="notifications_delete_dropdown" id="notifications_delete_dropdown" style="display:none;">
          <a data-class="notifications_donotclose" id="remove_notification_update" href="javascript:void(0);" onclick="removenotification('<?php echo $notification->getIdentity(); ?>');"><i id="remove_notification_update" data-class="notifications_donotclose" class="far fa-times-circle"></i><?php echo $this->translate("Remove this Notification"); ?></a>
        </div>
      </div>
    </li>
  <?php } ?>
<?php endforeach; ?>
<script>
  scriptJquery('.notifications_delete_show').on('click', function(event){
    if(scriptJquery(this).hasClass('showdropdown')){
      scriptJquery(this).removeClass('showdropdown');
    }else{
      scriptJquery('.notifications_delete_show').removeClass('showdropdown');
      scriptJquery(this).addClass('showdropdown');
    }
      return false;
  });
</script>
