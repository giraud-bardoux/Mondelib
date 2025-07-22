<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9867 2013-02-12 21:17:02Z jung $
 * @access	   John
 */
?>

<a id="event_profile_members_anchor"></a>

<script type="text/javascript">
  var eventMemberSearch = <?php echo Zend_Json::encode($this->search) ?>;
  var eventMemberPage = Number('<?php echo $this->members->getCurrentPageNumber() ?>');
  var waiting = '<?php echo $this->waiting ?>';
  en4.core.runonce.add(function() {
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    scriptJquery('#event_members_search_input').on('keypress', function(e) {
      if( e.key == 'Enter' ) {
        en4.core.request.send(scriptJquery.ajax({
          url : url,
          method : 'post',
          dataType : 'html',
          data : {
            format : 'html',
            subject : en4.core.subject.guid,
            search : this.value
          }
        }), {
          'element' : scriptJquery('#event_profile_members_anchor').parent()
        });
      }
    });
  });

  var paginateEventMembers = function(page) {
    //var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action' => 'profile-members', 'subject' => $this->subject()->getGuid(), 'format' => 'html'), 'default', true) ?>';
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    en4.core.request.send(scriptJquery.ajax({
      url : url,
      method : 'post',
      dataType : 'html',
      data : {
        format : 'html',
        subject : en4.core.subject.guid,
        search : eventMemberSearch,
        page : page,
        waiting : waiting
      }
    }), {
      'element' : scriptJquery('#event_profile_members_anchor').parent()
    });
  }
</script>

<?php if( !empty($this->waitingMembers) && $this->waitingMembers->getTotalItemCount() > 0 ): ?>
<script type="text/javascript">
  var showWaitingMembers = function() {
    //var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action' => 'profile-members', 'subject' => $this->subject()->getGuid(), 'format' => 'html'), 'default', true) ?>';
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    en4.core.request.send(scriptJquery.ajax({
      url : url,
      method : 'post',
      dataType : 'html',
      data : {
        format  : 'html',
        subject : en4.core.subject.guid,
        waiting : true
      }
    }), {
      'element' : scriptJquery('#event_profile_members_anchor').parent()
    });
  }
  
  var showRegisteredMembers = function() {
    //var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action' => 'profile-members', 'subject' => $this->subject()->getGuid(), 'format' => 'html'), 'default', true) ?>';
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    en4.core.request.send(scriptJquery.ajax({
      url : url,
      method : 'post',
      dataType : 'html',
      data : {
        format  : 'html',
        subject : en4.core.subject.guid,
      }
    }), {
      'element' : scriptJquery('#event_profile_members_anchor').parent()
    });
  }
</script>
<?php endif; ?>

<?php if( !$this->waiting ): ?>
  <div class="profile_members_info">
    <div class="profile_members_total">
      <?php if( '' == $this->search ): ?>
        <?php echo $this->translate(array('This event has %1$s guest.', 'This event has %1$s guests.', $this->members->getTotalItemCount()),$this->locale()->toNumber($this->members->getTotalItemCount())) ?>
      <?php else: ?>
        <?php echo $this->translate(array('This event has %1$s guest that matched the query "%2$s".', 'This event has %1$s guests that matched the query "%2$s".', $this->members->getTotalItemCount()), $this->locale()->toNumber($this->members->getTotalItemCount()), $this->escape($this->search)) ?>
      <?php endif; ?>
      <?php if( !empty($this->waitingMembers) && $this->waitingMembers->getTotalItemCount() > 0 ): ?>
        <div class="profile_members_wating_member">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('See Waiting'), array('onclick' => 'showWaitingMembers(); return false;')) ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="profile_members_search">
      <input id="event_members_search_input" type="text" placeholder="<?php echo $this->translate('Search Guests');?>" <?php if($this->search) { ?> value="<?php echo $this->search; ?>" <?php } ?>>
    </div>
  </div>
<?php else: ?>
  <div class="profile_members_info">
    <div class="profile_members_total">
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate(array('This event has %1$s member waiting approval or waiting for a invite response.', 'This event has %1$s members waiting approval or waiting for a invite response.', $this->members->getTotalItemCount()),$this->locale()->toNumber($this->members->getTotalItemCount())), array('onclick' => 'showRegisteredMembers(); return false;'))  ?>
    </div>
  </div>
<?php endif; ?>

<?php if( $this->members->getTotalItemCount() > 0 ): ?>
  <div class='row profile_members_listing'>
    <?php foreach( $this->members as $member ):
      if( !empty($member->resource_id) ) {
        $memberInfo = $member;
        $member = $this->item('user', $memberInfo->user_id);
      } else {
        $memberInfo = $this->event->membership()->getMemberInfo($member);
      }
      ?>
      <?php if($member->getIdentity()) { ?>
        <div class="col-sm-6 profile_members_listing_item" id="event_member_<?php echo $member->getIdentity() ?>">
          <div class="profile_members_listing_inner">
            <div class='profile_members_listing_left'>
              <div class="profile_members_listing_img">
                <?php echo $this->htmlLink($member->getHref(), $this->itemBackgroundPhoto($member, 'thumb.icon'), array('class' => 'event_members_icon')) ?>
              </div>  
              <div class='profile_members_listing_content'>
                <div class='title'>
                  <?php echo $this->htmlLink($member->getHref(), $member->getTitle(), array('class' => 'font_color')) ?>
                  <?php // Titles ?>
                  <?php if( $this->event->getParent()->getGuid() == ($member->getGuid())): ?>
                    <?php echo $this->translate('(%s)', ( $memberInfo->title ? $memberInfo->title : $this->translate('owner') )) ?>
                  <?php endif; ?>
                </div>
                <span class="event_members_rsvp font_color_light">
                  <?php if( $memberInfo->rsvp == 0 ): ?>
                    <?php echo $this->translate('Not Attending') ?>
                  <?php elseif( $memberInfo->rsvp == 1 ): ?>
                    <?php echo $this->translate('Maybe Attending') ?>
                  <?php elseif( $memberInfo->rsvp == 2 ): ?>
                    <?php echo $this->translate('Attending') ?>
                  <?php else: ?>
                    <?php echo $this->translate('Awaiting Reply') ?>
                  <?php endif; ?>
                </span>
              </div>
            </div>  
            <div class='profile_members_listing_right'>
              <?php if( $this->event->isOwner($this->viewer()) && $this->viewer()->getIdentity() != $member->getIdentity()): ?>
                <div class="dropdown options_menu">
                  <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon_option_menu"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                    <?php if( !$this->event->isOwner($member) && $memberInfo->active == true ): ?>
                      <li>
                        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Remove Member'), array(
                          'class' => 'buttonlink smoothbox icon_friend_remove dropdown-item'
                        )) ?>
                      <li>
                    <?php endif; ?>
                    <?php if( $memberInfo->active == false && $memberInfo->resource_approved == false ): ?>
                      <li>
                        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'approve', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Approve Request'), array(
                            'class' => 'buttonlink smoothbox icon_event_accept dropdown-item'
                          )) ?>
                          <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Reject Request'), array(
                            'class' => 'buttonlink smoothbox icon_event_reject dropdown-item'
                          )) ?>
                        <?php endif; ?>
                        <?php if( $memberInfo->active == false && $memberInfo->resource_approved == true ): ?>
                          <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'cancel', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Cancel Invite'), array(
                            'class' => 'buttonlink smoothbox icon_event_cancel dropdown-item'
                          )) ?>
                      </li>
                    <?php endif; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php } ?>
    <?php endforeach;?>
  </div>

  <?php if( $this->members->count() > 1 ): ?>
    <div>
      <?php if( $this->members->getCurrentPageNumber() > 1 ): ?>
        <div id="user_event_members_previous" class="paginator_previous">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
            'onclick' => 'paginateEventMembers(eventMemberPage - 1)',
            'class' => 'buttonlink icon_previous',
            'style' => '',
          )); ?>
        </div>
      <?php endif; ?>
      <?php if( $this->members->getCurrentPageNumber() < $this->members->count() ): ?>
        <div id="user_event_members_next" class="paginator_next">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
            'onclick' => 'paginateEventMembers(eventMemberPage + 1)',
            'class' => 'buttonlink icon_next'
          )); ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>
