<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10128 2014-01-24 18:47:54Z lucas $
 * @author     John
 */
?>

<script type="text/javascript">
  
  var toggleFriendsPulldown = function(event, element, user_id) {

    var eventTargetId = event.target.id;
    event = new Event(event);
    element = scriptJquery(element);

//     if(element.prop("tagName") != 'DIV' ) {
//       return;
//     }
    
    scriptJquery('.profile_friends_lists').each(function(e) {
      var otherElement = scriptJquery(this);
      if( otherElement.attr("id") == 'user_friend_lists_' + user_id ) {
        return;
      }
      var pulldownElement = otherElement.find('.pulldown_active');
      if( pulldownElement ) {
        pulldownElement.addClass('pulldown').removeClass('pulldown_active');
      }
    });
    if(element.hasClass('pulldown') ) {
      element.removeClass('pulldown').addClass('pulldown_active');
    } else if(eventTargetId != 'new_list') {
      element.addClass('pulldown').removeClass('pulldown_active');
    }
    //OverText.update();
  }
  var handleFriendList = function(event, element, user_id, list_id) {
    new Event(event).preventDefault();
    element = scriptJquery(element);
    if(!element.hasClass('friend_list_joined')) {
      // Add
      en4.user.friends.addToList(list_id, user_id);
      element.addClass('friend_list_joined').removeClass('friend_list_unjoined');
    } else {
      // Remove
      en4.user.friends.removeFromList(list_id, user_id);
      element.removeClass('friend_list_joined').addClass('friend_list_unjoined');
    }
  }
  var createFriendList = function(event, element, user_id) {
    if(event.which == 13) {
      element = scriptJquery(element);
      var list_name = element.val();
      element.val('');
      element.blur();
      var request = en4.user.friends.createList(list_name, user_id);
      request.complete(function(responseJSON) {
        if( responseJSON.status ) {
          scriptJquery.crtEle('li', {
            'class' : 'friend_list_joined' + ' user_profile_friend_list_' + responseJSON.list_id,
            'onclick' : 'handleFriendList(event, $(this), \'' + user_id + '\', \'' + responseJSON.list_id + '\');'
          }).html('\n\<span><a href="javascript:void(0);" onclick="deleteFriendList(event, ' + responseJSON.list_id + ');">x</a></span>\n\<div>' + list_name + '</div>').insertBefore(scriptJquery('#new_list').parent());
        } else {
          //alert('whoops');
        }
      });
    }
  }
  
  var deleteFriendList = function(event, list_id) {
    event = new Event(event);
    event.preventDefault();

    // Delete
    scriptJquery('.user_profile_friend_list_' + list_id).remove();

    // Send request
    en4.user.friends.deleteList(list_id);
  }
  
  en4.core.runonce.add(function(){
    //scriptJquery('.profile_friends_lists input').each(function(element) { new OverText(element); });
    
    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('#user_profile_friends').parent();
    scriptJquery('#user_profile_friends_previous').css("display",'<?php echo ( $this->friends->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>');
    scriptJquery('#user_profile_friends_next').css("display",'<?php echo ( $this->friends->count() == $this->friends->getCurrentPageNumber() ? 'none' : '' ) ?>');

    scriptJquery('#user_profile_friends_previous').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        method : 'post',
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->friends->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('#user_profile_friends_next').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        method : 'post',
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->friends->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>

    scriptJquery('.friends_lists_menu_input input').each(function(element){
      scriptJquery(this).on('blur', function() {
        scriptJquery(this).parents('.drop_down_frame').css("visibility","hidden");
      });
    });
  });
</script>

<div class="user_profile_friends row" id="user_profile_friends">
  <?php foreach( $this->friends as $membership ):
    if( !isset($this->friendUsers[$membership->resource_id]) ) continue;
    $member = $this->friendUsers[$membership->resource_id];
    ?>
    <?php if($member->getIdentity()) { ?>
      <div class="col-sm-6 user_profile_friends_item" id="user_friend_<?php echo $member->getIdentity() ?>">
        <div class="user_profile_friends_inner">
          <div class="user_profile_friends_left">
            <div class="user_profile_friends_img">
              <?php echo $this->htmlLink($member->getHref(), $this->itemBackgroundPhoto($member, 'thumb.profile'), array('class' => 'profile_friends_icon')) ?>
            </div>
            <div class="profile_friends_content">
              <div class="_title">
                <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>
              </div>  
              <span class="member_status_text"><?php echo $this->getHelper('getActionContent')->smileyToEmoticons($member->status); ?></span>
              <?php if( $this->viewer()->isSelf($this->subject()) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.lists')): // BEGIN LIST CODE ?>
                <div class="profile_friends_lists" id="user_friend_lists_<?php echo $member->user_id ?>">
                  <div id="pulldown_toggle" class="pulldown" style="display:inline-block;" onClick="toggleFriendsPulldown(event, this, '<?php echo $member->user_id ?>');">
                     <a class="add_list_btn" href="javascript:void(0);"><?php echo $this->translate('Add to list') ?></a>
                    <div class="pulldown_contents_wrapper">
                      <div class="pulldown_contents">
                        <ul>
                          <?php foreach( $this->lists as $list ):
                            $inList = engine_in_array($list->list_id, (array)@$this->listsByUser[$member->user_id]);
                            ?>
                            <li class="<?php echo ( $inList !== false ? 'friend_list_joined' : 'friend_list_unjoined' ) ?> user_profile_friend_list_<?php echo $list->list_id ?>" onclick="handleFriendList(event, scriptJquery(this), '<?php echo $member->user_id ?>', '<?php echo $list->list_id ?>');">
                              <span>
                                <a href="javascript:void(0);" onclick="deleteFriendList(event, <?php echo $list->list_id ?>);">x</a>
                              </span>
                              <div>
                                <?php echo $list->title ?>
                              </div>
                            </li>
                          <?php endforeach; ?>
                          <li>
                            <input id="new_list" type="text" placeholder="<?php echo $this->translate('New list...') ?>" onclick="new Event(event).preventDefault();" onkeypress="createFriendList(event, scriptJquery(this), '<?php echo $member->user_id ?>');" />
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>  
                </div>
              <?php endif; // END LIST CODE ?>            
            </div>
          </div>
          <div class="user_profile_friends_right">
            <?php echo $this->userFriendship($member) ?>
          </div>
        </div>
      </div>
    <?php } ?>
  <?php endforeach ?>
</div>
<div class="profile_paginator">
  <div id="user_profile_friends_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="user_profile_friends_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
