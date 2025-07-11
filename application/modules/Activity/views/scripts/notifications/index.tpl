<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<script type="text/javascript">

  var notificationPageCount = <?php echo sprintf('%d', $this->notifications->count()); ?>;
  var notificationPage = <?php echo sprintf('%d', $this->notifications->getCurrentPageNumber()); ?>;
  var loadMoreNotifications = function() {
    notificationPage++;
    scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/notifications/index',
      dataType : 'html',
      method : 'post',
      data : {
				isAjax:1,
        format : 'html',
        page : notificationPage
      },
      success : function(responseHTML) {
        scriptJquery('#notifications_loading_main').css('display', 'none');
        if( '' != responseHTML.trim() && notificationPageCount > notificationPage ) {
          scriptJquery('#notifications_viewmore').css('display', '');
        }
        scriptJquery('#notifications_main')[0].innerHTML += responseHTML;
      }
    });
  };
  
  en4.core.runonce.add(function() {
    if(scriptJquery('#notifications_viewmore_link').length){
      scriptJquery('#notifications_viewmore_link').on('click', function() {
        scriptJquery('#notifications_viewmore').css('display', 'none');
        scriptJquery('#notifications_loading_main').css('display', '');
        loadMoreNotifications();
      });
    }
    
    if (document.getElementById('notifications_viewmore'))
      document.getElementById('notifications_viewmore').style.display = "<?php echo ($this->notifications->count() == 0 ? 'none' : ($this->notifications->count() == $this->notifications->getCurrentPageNumber() ? 'none' : '' )) ?>";

    if(scriptJquery('#notifications_markread_link_main')){
      scriptJquery('#notifications_markread_link_main').on('click', function() {
        scriptJquery('#notifications_markread_main').css('display', 'none');
        en4.activity.hideNotifications('<?php echo $this->translate("0 Updates");?>');
      });
    }
    
    scriptJquery('#notifications_main').on('click', function(event){
        event.preventDefault(); //Prevents the browser from following the link.
        if(event.target.id != 'notification_id') {
        
          var current_link = scriptJquery(event.target);
          var notification_li = current_link.parents('li');
          
          // if this is true, then the user clicked on the li element itself
          if( notification_li.attr('id') == 'core_menu_mini_menu_update' ) {
            notification_li = current_link;
          }

          var forward_link;
          if( current_link.attr('href') ) {
            forward_link = current_link.attr('href');
          }else if(current_link.hasClass("notification_subject_icon")){
            forward_link = current_link.parents("a").attr('href');
          } else{
            forward_link = current_link.find('a:last-child').attr('href');
          }
          if(forward_link == undefined) {
            forward_link = scriptJquery("#"+notification_li.attr('id')).find('.notification_item_photo').find('a').attr('href');
            if(forward_link == undefined)
              forward_link = en4.core.baseUrl;
          }
              
          if( notification_li.hasClass('notifications_unread')){
            notification_li.removeClass('notifications_unread');
            scriptJquery.ajax({
              url: en4.core.baseUrl + 'activity/notifications/markread',
              data: {
                format     : 'json',
                notification_id : notification_li.val()
              },
              method:'post',
              dataType: 'json',
              success: function (response) {
                window.location = forward_link;
              },
              error: function (err) {
                console.log(err);
              }
            });
          } else {
            window.location = forward_link;
          }
        }
    });
  });
  
  function deletenotification(notification_id) {
		
    scriptJquery.ajax({
      url: en4.core.baseUrl + 'activity/notifications/remove-notification',
      data: {
        format : 'html',
        notification_id: notification_id,
      },
      method:'post',
      dataType: 'html',
      success: function (response) {
        var result = scriptJquery.parseJSON(response);
        if(result.status == 1) {
          scriptJquery('#notification_'+notification_id).remove();
        }
      },
    });
  }
  
</script>
<?php if(!$this->isAjax) { ?>
  <div class='notifications_layout'>
    <div class='layout_content'>
      <div class="notifications_layout_inner">
        <div class="notifications_layout_head d-flex justify-content-between align-items-center">  
          <h3 class="m-0 p-0"><?php echo $this->translate("Recent Updates") ?></h3>
          <?php if( $this->notifications->getTotalItemCount() > 0 ): ?>
            <div class="dropdown">
              <button class="btn btn-alt" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon_option_menu"></i>
              </button>
              <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end">
                <?php //if( $this->hasunread ): ?>
                  <li class="notifications_markread" id="notifications_markread_main">
                    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Mark All Read'), array('id' => 'notifications_markread_link_main', 'class' => 'dropdown-item notifications_markread_link')) ?>
                  </li>
                <?php //endif; ?>
                <li class="notifications_delete" id="notifications_delete"><a href="<?php echo $this->url(array('action' => 'delete-notifications'), 'recent_activity', true); ?>" id="notifications_delete_link" class="smoothbox dropdown-item notifications_delete_link"><span><?php echo $this->translate("Delete All"); ?></span></a></li>
              </ul>
            </div>
            <?php endif; ?>
        </div>
        <ul class='notifications' <?php if( $this->notifications->getTotalItemCount() > 0 ) { ?> id="notifications_main" <?php } ?>>
<?php } ?>
          <?php if( $this->notifications->getTotalItemCount() > 0 ): ?>
            <?php foreach( $this->notifications as $notification ):
              $notificationType = Engine_Api::_()->getDbTable('NotificationTypes', 'activity')->getNotificationType($notification->type); 
              ob_start();
              if($notificationType->is_request) {
                try {
                  $parts = explode('.', $notification->getTypeInfo()->handler);
                  echo $this->action($parts[2], $parts[1], $parts[0], array('notification' => $notification));
                } catch( Exception $e ) {
                  if( APPLICATION_ENV === 'development' ) {
                    echo $e->__toString();
                  }
                  continue;
                }
              } else { ?>
                <?php try { ?>
                  <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id);?>
                  <li<?php if( !$notification->read ): ?> class="notifications_unread"<?php $this->hasunread = true; ?> <?php endif; ?> value="<?php echo $notification->getIdentity();?>" id="notification_<?php echo $notification->getIdentity();?>">
                    <?php // removed onclick event onclick="javascript:en4.activity.markRead($notification->getIdentity() ?>
                      <div class="notification_item_photo">
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
                        <?php echo $this->translate(' Posted %1$s', $this->timestamp($notification->date));?>
                      </div>
                      <div class="notification_item_buttons">
                        <a id="notification_id" href="javascript:void(0);" class="delete_noti" onclick="deletenotification('<?php echo $notification->getIdentity(); ?>');"><i class="fas fa-trash-alt"></i><?php echo $this->translate("Delete"); ?></a>
                      </div>
                    </div>
                  </li>
                <?php } catch( Exception $e ) {
                  ob_end_clean();
                  if( APPLICATION_ENV === 'development' ) {
                    echo $e->__toString();
                  }
                  continue;
                }
            }
            ob_end_flush(); ?>
            <?php endforeach; ?>
          <?php else: ?>
            <li>
              <div class="no_result_tip w-100">
                <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="No Result"></i>
                <p class=" m-0"><?php echo $this->translate("You have no notifications.") ?></p>
              </div>
            </li>
          <?php endif; ?>

      <?php if(!$this->isAjax) { ?>
        </ul>
        <?php if( $this->notifications->getTotalItemCount() > 1 ): ?>
          <div class="notifications_options">
            <div class="notifications_viewmore" id="notifications_viewmore" style="display: none;"> 
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => 'notifications_viewmore_link', 'class' => 'buttonlink notifications_viewmore_link icon_viewmore')) ?> 
            </div>
            <div class="notifications_viewmore" id="notifications_loading_main" style="display: none;"><a href="javascript:void(0);"><i class="fa fa-spinner fa-spin"></i></a></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
