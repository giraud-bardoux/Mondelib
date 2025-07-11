<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activity.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php if (!empty($this->getAction))
  $action = $this->getAction;
if (empty($action->action_id))
  return;
$attachmentItems = $action->getAttachments();
$actionAttachment = engine_count($attachmentItems) ? $attachmentItems : array();
?>
<?php if (!$this->noList): ?>
  <li id="activity-item-<?php echo $action->action_id ?>" data-activity-feed-item="<?php echo $action->action_id ?>"
    class="activity_pinfeed activity_pinfeed_hidden clearfix _photo<?php echo $this->userphotoalign; ?><?php if (!empty($fromActivityFeed)) { ?> sescommunityads_ad_id ecmads_ads_listing_item<?php } ?>"
    <?php if (!empty($fromActivityFeed)) { ?> rel="<?php echo $ad->getIdentity(); ?>" <?php } ?>>
  <?php endif; ?>

  <?php if (!empty($fromActivityFeed)) { ?>
    <?php include ('application/modules/Sescommunityads/views/scripts/widget-data/_hiddenData.php'); ?>
  <?php } ?>
  <section class="block <?php if (!empty($fromActivityFeed)) { ?> ecmads_ads_item_img <?php } ?>">
    <?php !empty($this->commentForm) ? $this->commentForm->setActionIdentity($action->action_id) : ""; ?>
    <?php if (!$this->isOnThisDayPage && !engine_in_array('comment', $this->enabledModuleNames)) { ?>
      <script type="text/javascript">
        (function () {
          var action_id = '<?php echo $action->action_id ?>';
          en4.core.runonce.add(function () {
            scriptJquery('#activity-comment-body-' + action_id).autogrow();
            en4.activity.attachComment(scriptJquery('#activity-comment-form-' + action_id));
          });
        })();
      </script>
    <?php } ?>
    <div class="activity_feed_header clearfix">
      <?php // User profile photo ?>
      <div class='activity_feed_item_photo'>
        <?php
        $getSubject = $action->getSubject();
        if ($action && !empty($action->resource_id) && !empty($action->resource_type)) {
          $itemSubject = Engine_Api::_()->getItem($action->resource_type, $action->resource_id);
          if ($itemSubject)
            $getSubject = $itemSubject;
        }
        ?>
        <?php echo $this->htmlLink($getSubject->getHref(), $this->itemPhoto($getSubject, 'thumb.profile', $getSubject->getTitle(false), array('class' => 'core_tooltip', 'data-src' => $getSubject->getGuid()))) ?>
      </div>
      <div class="activity_feed_header_cont">
      <div class="activity_feed_header_info">
          <?php // Main Content ?>
          <?php $contentData = $this->getContent($action, array('group_feed' => @$group_feed_id, 'resource_id' => $action['resource_id'], 'resource_type' => $action['resource_type'])); ?>
          <div class="activity_feed_header_title <?php echo (empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated') ?>">
            <?php if ($this->filterFeed == 'hiddenpost') { ?>
              <div class="activity_feed_options activity_pulldown_wrapper">
                <a href="javascript:void(0);" class="allowed_hide_post_activity activity_tooltip activity_feed_options_btn" data-src="<?php echo $action->getIdentity() ?>" title="Allowed"><i class="far fa-circle"></i></a>
              </div>
            <?php } ?>
            <?php echo isset($contentData[0]) ? $contentData[0] : ''; ?>
            <?php $location = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => 'activity_action', "resource_id" => $action->getIdentity())); ?>
            <?php $members = Engine_Api::_()->getDbTable('tagusers', 'activity')->getActionMembers($action->getIdentity()); ?>
            <?php $tagItems = Engine_Api::_()->getDbTable('tagitems', 'activity')->getActionItems($action->getIdentity()); ?>

            <?php $isCondition = true; ?>

            <?php //Feeling Work ?>
            <?php $feelingposts = Engine_Api::_()->getDbTable('feelingposts', 'activity')->getActionFeelingposts($action->getIdentity()); ?>
            <?php if ($feelingposts) { ?>
              <?php
              $isCondition = false;
              $feelings = Engine_Api::_()->getItem('activity_feeling', $feelingposts->feeling_id);
              if (empty($feelingposts->feeling_custom)) {
                if ($feelings->type == 1) {
                  $feelingIcon = Engine_Api::_()->getItem('activity_feelingicon', $feelingposts->feelingicon_id);
                  $photo = Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '');
                  if ($photo) {
                    $photo = $photo->getPhotoUrl();
                    ?>
                    <?php echo $this->translate("is "); ?><img title="<?php echo strtolower($feelingIcon->title); ?>"
                      class="feeling_icon"
                      src="<?php echo Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '')->getPhotoUrl(); ?>">
                    <?php echo strtolower($feelings->title); ?>         <?php echo strtolower($feelingIcon->title);
                  } ?>
                  <?php
                } else if ($feelings->type == 2 && $feelingposts->resource_type && $feelingposts->feelingicon_id) {
                  $resource = Engine_Api::_()->getItem($feelingposts->resource_type, $feelingposts->feelingicon_id);
                  if ($resource) {
                    echo $this->translate("is "); ?><img title="<?php echo strtolower($resource->title); ?>" class="feeling_icon" src="<?php echo Engine_Api::_()->storage()->get($feelings->file_id, '')->getPhotoUrl(); ?>">
                    <?php echo strtolower($feelings->title); ?> <a href="<?php echo strtolower($resource->getHref()); ?>" class="core_tooltip" data-src="<?php echo $resource->getGuid(); ?>"><?php echo strtolower($resource->title); ?></a>
                  <?php }
                }
              } else { ?>
                <?php
                $feeling = Engine_Api::_()->storage()->get($feelings->file_id, '');
                if ($feeling) {
                  $feeling = $feeling->getPhotoUrl();
                  ?>
                  <?php echo $this->translate("is "); ?><img title="<?php echo $feelingposts->feeling_customtext; ?>" class="feeling_icon" src="<?php echo $feeling; ?>"> <?php echo strtolower($feelings->title); ?>
                  <?php echo $feelingposts->feeling_customtext; ?>
                <?php }
              }
              ?>
            <?php } ?>
            <?php //Feeling Work ?>

            <?php if ($itemTotalCount = engine_count($tagItems)) { ?>
              <?php echo $this->translate("in"); ?>
              <?php
              foreach ($tagItems as $tagItem) {
                $item = Engine_Api::_()->getItem($tagItem['resource_type'], $tagItem['resource_id']);
                if (!$item)
                  continue;
                ?>
                <a href="<?php echo $item->getHref(); ?>" class="core_tooltip"
                  data-src="<?php echo $item->getGuid(); ?>"><?php echo $item->getTitle(); ?></a>
                <?php
              } ?>
            <?php } ?>
            <?php if ($memberTotalCount = engine_count($members)) { ?>
              <?php if($isCondition) { ?>
                <?php echo $this->translate("is with"); ?>
                <?php $isCondition = false; ?>
              <?php } else { ?>
                <?php echo $this->translate("with"); ?>
              <?php } ?>
              <?php
              $counterMember = 1;
              foreach ($members as $member) {
                $user = Engine_Api::_()->getItem('user', $member['user_id']);
                if (!$user)
                  continue;
                ?>
                <?php if ($counterMember == 2 && $memberTotalCount == 2) { ?>
                  and
                <?php } else if ($counterMember == 2 && $memberTotalCount > 2) { ?>
                    and
                    <a href="javascript:;" class="ajaxsmoothbox"
                      data-url="activity/ajax/tag-people/action_id/<?php echo $action->getIdentity(); ?>"><?php echo $this->translate(($memberTotalCount - 1) . ' others') ?></a>
                    <?php
                    break;
                  } ?>
                <a href="<?php echo $user->getHref(); ?>" class="core_tooltip"
                  data-src="<?php echo $user->getGuid(); ?>"><?php echo $user->getTitle(); ?></a>
                <?php
                $counterMember++;
              } ?>
            <?php } ?>
            <?php if ($location) { ?>
              <?php if($isCondition) { ?>
                <?php echo $this->translate("is in"); ?>
                <?php $isCondition = false; ?>
              <?php } else { ?>
              <?php echo $this->translate("in"); ?>
              <?php } ?>
              <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
                <a href="<?php echo 'http://maps.google.com/?q='.$location->venue; ?>" target='_blank'><?php echo $location->venue; ?></a>
              <?php } ?>
            <?php } ?>
          </div>
          <?php
          $icon_type = 'activity_icon_' . $action->type;
          list($attachment) = !empty($actionAttachment) ? $actionAttachment : array(false);
          if (is_object($attachment) && $action->attachment_count > 0 && $attachment->item):
            $icon_type .= ' item_icon_' . $attachment->item->getType() . ' ';
          endif;
          if (!empty($action) && $action->reaction_id) {
            $icon_type .= ' item_icon_core_sticker';
          }
          ?>
          <div class="activity_feed_header_btm">
            <?php if (empty($fromActivityFeed)) { ?>
              <i class="activity_feed_header_btm_icon <?php echo $icon_type ?>"></i>
              <span class="font_color_light">&middot;</span>
            <?php } ?>

            <?php
            if ($this->subject() && file_exists(APPLICATION_PATH . '/application/modules/' . $this->subject()->getModuleName() . '/views/scripts/' . $this->subject()->getType() . '_activity_published.tpl')) {
              include APPLICATION_PATH . '/application/modules/' . $this->subject()->getModuleName() . '/views/scripts/' . $this->subject()->getType() . '_activity_published.tpl';
            }
            ?>
            <?php if (empty($fromActivityFeed)) { ?>
              <a href="<?php echo $action->getHref(); ?>"><?php echo $this->timestamp($action->getTimeValue()); ?></a>
              <span class="font_color_light">&middot;</span>
            <?php } else { ?>
              <span class="_txt font_color_light _sponsored"><?php $dot = "";
              if ($ad->sponsored) {
                echo $this->translate('Sponsored');
                $dot = "&middot;";
              } ?><?php echo $ad->featured && !$ad->sponsored ? $dot . $this->translate('Featured') : ""; ?></span>
            <?php } ?>
            <?php if ($action->privacy == 'onlyme') {
              $classPrivacy = 'icon_activity_me';
              $titlePrivacy = 'Only Me';
            } else if ($action->privacy == 'friends') {
              $classPrivacy = 'icon_activity_friends';
              if ($action->getSubject()->getIdentity() != $this->viewer()->getIdentity())
                $titlePrivacy = ucwords($action->getSubject()->getTitle(false)) . '\'s friends';
              else
                $titlePrivacy = 'Friend\'s Only';
            } else if ($action->privacy == 'networks') {
              $classPrivacy = 'icon_activity_network';
              $titlePrivacy = 'Friends And Networks';
            } else if (strpos($action->privacy, 'network_list') !== false) {
              $classPrivacy = 'icon_activity_network';
              $explode = explode(',', $action->privacy);
              $titlePrivacy = '';
              $counter = 1;
              foreach ($explode as $ex) {
                $item = Engine_Api::_()->getItem('network', str_replace('network_list_', '', $ex));
                if (!$item)
                  continue;
                $titlePrivacy = $item->getTitle(false) . ', ' . $titlePrivacy;
                $counter++;
              }
              $titlePrivacy = rtrim($titlePrivacy, ', ');
              if ($counter > 2)
                $titlePrivacy = 'Multiptle Network ( ' . $titlePrivacy . ')';
            } else if (strpos($action->privacy, 'members_list') !== false || strpos($action->privacy, 'member_list') !== false) {
              $classPrivacy = 'icon_activity_lists';
              $explode = explode(',', $action->privacy);
              $titlePrivacy = '';
              $counter = 1;
              foreach ($explode as $ex) {
                $item = Engine_Api::_()->getItem('user_list', str_replace('member_list_', '', $ex));
                if (!$item)
                  continue;
                $titlePrivacy = $item->getTitle(false) . ', ' . $titlePrivacy;
                $counter++;
              }
              $titlePrivacy = rtrim($titlePrivacy, ', ');
              if ($counter > 2)
                $titlePrivacy = 'Multiptle Lists ( ' . $titlePrivacy . ')';
            } else {
              $classPrivacy = 'icon_activity_public';
              $titlePrivacy = 'Everyone';
            }
            ?>
            <?php if (empty($fromActivityFeed)) { ?>
              <span class="activity_feed_header_pr _user"><i
                  class="activity_tooltip font_color_light <?php echo $classPrivacy; ?>" title="<?php echo $this->translate('Shared with: '); echo $this->translate($titlePrivacy); ?>"></i></span>
              <?php if (engine_in_array('sesiosapp', $this->enabledModuleNames) && !empty($action->posting_type) && $action->posting_type == 1) { ?>
                <span class="font_color_light">&middot;</span>
                <span class="font_color_light updatefrom"><?php echo $this->translate('From iPhone'); ?></span>
                <i class="fa fa-mobile font_color_light updatefrom_icon"></i>
              <?php } else if (engine_in_array('sesandroidapp', $this->enabledModuleNames) && !empty($action->posting_type) && $action->posting_type == 2) { ?>
                  <span class="font_color_light">&middot;</span>
                  <span class="font_color_light updatefrom"><?php echo $this->translate('From Android'); ?></span>
                  <i class="fa fa-mobile font_color_light updatefrom_icon"></i>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
        <div class="activity_feed_header_right">
          <?php if ($this->subject() && $pintotop) { ?>
            <?php $isPinned = $action->isPinPost(array('resource_type' => $this->subject()->getType(), 'resource_id' => $this->subject()->getIdentity(), 'action_id' => $action->getIdentity()));
          } ?>
          <?php if (!empty($fromActivityFeed) && empty($hideOptions) && $ad->user_id != $this->viewer()->getIdentity()) { ?>
            <div class="activity_feed_options options_menu dropdown">
              <a href="javascript:void(0);" class="activity_feed_options_btn btn btn-alt"><i class="icon_option_menu"></i></a>
              <ul class="dropdown-menu options_menu dropdown-menu-end">
                <li><a href="javascript:;" class="ecomm_hide_ad"><?php echo $this->translate('hide ad'); ?></a></li>
                <!--<li><a target="_blank" href="<?php echo $this->url(array('module' => 'sescommunityads', 'controller' => 'index', 'action' => 'why-seeing'), 'sescommunityads_whyseeing', false); ?>" class="ecomm_seeing_ad"><?php echo $this->translate('why am i seeing this?'); ?></a></li>-->
                <li>
                  <?php $useful = $ad->isUseful(); ?>
                  <a href="javascript:;" class="ecomm_useful_ad<?php $useful ? ' active' : ''; ?>" data-rel="<?php echo $ad->getIdentity() ?>" data-selected="<?php echo $this->translate('This ad is useful'); ?>"                    data-unselected="<?php echo $this->translate('Remove from useful'); ?>"><?php echo !$useful ? $this->translate('This ad is useful') : $this->translate('Remove from useful'); ?></a>
                </li>
              </ul>
            </div>
          <?php } ?>
          <?php if (empty($fromActivityFeed) && empty($hideOptions) && $this->viewer()->getIdentity() && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost')) { ?>
            <?php if ($this->subject() && $pintotop && $isPinned) { ?>
              <span title="<?php echo $this->translate("Pinned Post"); ?>" class="activity_tooltip activity_pin_post center_item font_color_light"><i class="fa-solid fa-thumbtack"></i></span>
            <?php } ?>
            <div class="activity_feed_options options_menu dropdown">
              <button href="javascript:void(0);" class="activity_feed_options_btn btn btn-alt" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></button>
              <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end">
                <?php if (!$action->approved) { ?>
                  <li>
                    <a href="javascript:;" class="dropdown-item ajaxsmoothbox" data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'approve-feed', 'action_id' => $action->action_id), 'default', true); ?>"><span><?php echo $this->translate("Approve Feed"); ?></span>
                    </a>
                  </li>
                <?php } ?>
                <?php if (
                  !$this->isOnThisDayPage && $this->viewer()->getIdentity() && ((
                    $this->activity_moderate || (
                      $this->allow_delete && (
                        ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                        ('user' == $action->object_type && $this->viewer()->getIdentity() == $action->object_id)
                      )
                    )
                  ) || ($this->subject() && method_exists($this->subject(), 'canEditActivity') && $this->subject()->canEditActivity($this->subject())))
                ): ?>
                  <li><a id="activity_edit_<?php echo $action->getIdentity(); ?>" href="javascript:;" class="icon_edit ajaxsmoothbox dropdown-item" data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'edit-post', 'action_id' => $action->action_id), 'default', true); ?>"><span><?php echo $this->translate("Edit Feed"); ?></span></a></li>
                <?php endif; ?>
                <?php if (
                  $this->viewer()->getIdentity() && ((
                    $this->activity_moderate || (
                      $this->allow_delete && (
                        ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                        ('user' == $action->object_type && $this->viewer()->getIdentity() == $action->object_id)
                      )
                    )
                  ) || $this->subject() && method_exists($this->subject(), 'canEditActivity') && $this->subject()->canEditActivity($this->subject()))
                ): ?>
                  <li><a class="icon_delete ajaxsmoothbox dropdown-item" href="javascript:;" data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'delete', 'action_id' => $action->action_id), 'default', true); ?>"><span><?php echo $this->translate("Delete Feed"); ?></span></a></li>
                <?php endif; ?>
                <?php if ($pintotop && $this->subject() && $action->subject_id == $this->viewer()->getIdentity()) { ?>
                  <li><a class="dropdown-item pintotopfeedactivity" href="javascript:;" data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'pintotop', 'action_id' => $action->action_id, 'res_id' => $this->subject()->getIdentity(), 'res_type' => $this->subject()->getType()), 'default', true); ?>"><span><?php echo !$isPinned ? $this->translate("Pin Post to Top") : $this->translate("Unpin Post From Top"); ?></span></a>
                  </li>
                <?php } ?>
                <?php if (!$action->schedule_time) { ?>
                  <?php if (Engine_Api::_()->getDbTable('savefeeds', 'activity')->isSaved(array('action_id' => $action->getIdentity(), 'user_id' => $this->viewer()->getIdentity()))) { ?>
                    <li><a href="javascript:;" class="dropdown-item icon_activity_unsave" data-save="<?php echo $this->translate('Save Feed'); ?>" data-unsave="<?php echo $this->translate('Unsave Feed'); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Unsave Feed"); ?></span></a>
                    </li>
                  <?php } else { ?>
                    <li><a href="javascript:;" class="dropdown-item icon_activity_save" data-save="<?php echo $this->translate('Save Feed'); ?>" data-unsave="<?php echo $this->translate('Unsave Feed'); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Save Feed"); ?></span></a>
                    </li>
                  <?php } ?>
                  <li class="dropdown-divider"></li>
                  <li><a href="<?php echo $action->getHref(); ?>" class="ajaxPrevent dropdown-item activity_feed_link"><span><?php echo $this->translate("Feed Link"); ?></span></a></li>
                  <?php if (!$this->isOnThisDayPage) { ?>
                    <?php if ($this->viewer()->getIdentity() == $action->getSubject()->getIdentity()) {
                      if ($action->commentable)
                        $text = $this->translate('Disable Comments');
                      else
                        $text = $this->translate('Enable Comments');
                      ?>
                      <li><a href="javascript:;" class="dropdown-item commentable" data-commentable="<?php echo $action->commentable; ?>" data-save="<?php echo $this->translate('Enable Comments'); ?>" data-unsave="<?php echo $this->translate('Disable Comments'); ?>" data-href="activity/ajax/commentable/action_id/<?php echo $action->getIdentity(); ?>"><span><?php echo $text; ?></span></a></li>
                    <?php } ?>
                  <?php } ?>
                  <?php if ($this->viewer()->getIdentity() != $action->getSubject()->getIdentity()) { ?>
                    <li><a href="javascript:;" class="dropdown-item icon_activity_hide" data-name="<?php echo $action->getSubject()->getTitle(false); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-subjectid="<?php echo $action->subject_id; ?>"><span><?php echo $this->translate("Hide Feed"); ?></span></a>
                    </li>
                    <li><a href="javascript:;" class="dropdown-item icon_activity_hide_all icon_activity_hide_all_<?php echo $action->getIdentity(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-name="<?php echo $action->getSubject()->getTitle(false); ?>"><span><?php echo $this->translate("Hide all by %s", $action->getSubject()->getTitle(false)); ?></span></a></li>
                    <?php
                    if (empty($settings))
                      $settings = Engine_Api::_()->getApi('settings', 'core');
                    $reportLink = $this->baseUrl() . "/report/create/subject/" . $action->getGuid();
                    ?>
                    <li><a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $reportLink; ?>')" class="dropdown-item icon_report"><span><?php echo $this->translate("Report"); ?></span></a></li>
                  <?php } ?>
                <?php } else { ?>
                  <li><a href="javascript:;" class="dropdown-item activity_reschedule_post" data-value="<?php echo date('d-m-Y H:i:s', strtotime($action->schedule_time)); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Reschedule Post"); ?></span></a></li>
                <?php } ?>
              </ul>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php
    //Feed Background Image Work
    if ($action->feedbg_id) { ?>
      <?php
      $background = Engine_Api::_()->getItem('activity_background', $action->feedbg_id);
      $photo = Engine_Api::_()->storage()->get($background->file_id, '');
      if ($photo) {
        $photo = $photo->getPhotoUrl();
      }
      ?>
    <?php } //Feed Background Image Work ?>
    <div class='feed_item_body clearfix'>
      <div class='<?php if ($action->feedbg_id && $photo && empty($location)) { ?> feed_background_image <?php } ?>'
        <?php if ($action->feedbg_id && $photo && empty($location)) { ?>
          style="background-image:url(<?php echo $photo ?>);" <?php } ?>>
        <?php if (!empty($contentData[1])) { ?>
          <span class="activity_feed_item_bodytext" id="activity_feed_item_bodytext_<?php echo $action->getIdentity(); ?>">
            <?php
            if (isset($contentData[1])) {
              echo $contentData[1];
            } else {
              echo '';
            } ?>
          </span>
        <?php } ?>
      </div>

      <?php // Main Content ?>
      <?php
      $buysellActive = false;
      $buysellattachment = '';
      $action->intializeAttachmentcount();

      if (($action->type == 'post_self_buysell' || ($action->attachment_count == 1 && engine_count($actionAttachment) == 1 && $buysellattachment = current($actionAttachment)))) {
        if ($action->type == 'post_self_buysell' || (!empty($buysellattachment->item) && $buysellattachment->item->getType() == 'activity_buysell')) {
          if (empty($buysellattachment)) {
            $buysell = $action->getBuySellItem();
          } else {
            $changeAction = $action;
            $buysellAction = $buysellattachment->meta->action_id;

            $buysell = Engine_Api::_()->getItem('activity_buysell', $buysellattachment->meta->id);
            $action = Engine_Api::_()->getItem('activity_action', $buysell->action_id);
            $buysellattachment = '';
          }
          if ($buysell) {
            $locationBuySell = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => 'activity_buysell', "resource_id" => $buysell->getIdentity()));
            $buysellActive = true;
          } ?>
          <?php
        }
      }
      ?>
      <?php // Attachments 
      $action->intializeAttachmentcount();
      ?>
      <?php $classnumber = $action->attachment_count;
      if($action->attachment_count && $actionAttachment[0]->item && $actionAttachment[0]->item->getType() == 'video') {
        $count = 0;
        foreach( $actionAttachment as $attachment ):
          //if($attachment->item->getIdentity() == 19) continue;
          $count++;
          $classnumber = $count;
        endforeach;
      }
      $attachmentsData = $actionAttachment;
      ?>
      <?php $countAttachment = $attachmentsData ? engine_count($attachmentsData) : 0;
      $counterAttachment = 0;
      $totalAttachmentAttachInFeed = $action->params;
      $totalAttachmentAttachInFeed = !empty($totalAttachmentAttachInFeed['count']) ? $totalAttachmentAttachInFeed['count'] : $countAttachment;
      $viewMoreText = $totalAttachmentAttachInFeed - $attachmentShowCount;
      $showCountAttachment = $attachmentShowCount - 1;
      if ($classnumber > $attachmentShowCount)
        $classnumber = $attachmentShowCount;
      ?>
      <?php
      if ($attachmentsData && engine_count($attachmentsData) == 1) {
        $actionAttachmentLocation = $attachmentsData[0];
        if ($actionAttachmentLocation->item->getType() == 'activity_action') {
          $locationAttachment = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => 'activity_action', "resource_id" => $actionAttachmentLocation->item->getIdentity()));
        }
      }
      ?>
      <?php
      if (!$countAttachment && $location && $googleKey && !$buysellActive  && !$action->reaction_id && empty($action->image_id)) { ?>
        <div class="feed_item_map">
          <div class="feed_item_map_overlay" onClick="style.pointerEvents='none'"></div>
          <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
            <iframe class="feed_item_map_map" frameborder="0" allowfullscreen=""
              src="https://www.google.com/maps/embed/v1/place?q=<?php echo urlencode($location->venue); ?>&key=<?php echo $googleKey; ?>"
              style="border:0"></iframe>
          <?php } ?>
        </div>
      <?php } ?>
      <?php if (!empty($action) && $action->gif_url) { ?>
        <div>
          <span class="activity_feed_item_bodytext">
            <img class="giphy_image" src="<?php echo $action->gif_url; ?>" alt="<?php echo $action->gif_url; ?>">
          </span>
        </div>
      <?php } ?>
      <?php if (!empty($action) && $action->reaction_id && engine_in_array('comment', $this->enabledModuleNames)) { ?>
        <?php $reaction = Engine_Api::_()->getItem('comment_emotionfile', $action->reaction_id); ?>
        <?php if ($reaction) { ?>
          <div class="feed_item_sticker"><img class="_activitypinimg" src="<?php echo Engine_Api::_()->storage()->get($reaction->photo_id, '')->getPhotoUrl(); ?>"></div>
        <?php } ?>
      <?php } ?>
      <?php if (($action->getTypeInfo()->attachable) && $action->attachment_count > 0): // Attachments ?>
        <?php
        //Core link image work if width is greater than 250
        $width = '250';
        $attachment = $actionAttachment;
        if (!empty($attachment) && $attachment[0]->item->getType() == "core_link") {
          $attachment = $attachment[0];
          if ($attachment->item->photo_id) {
            $photoURL = $attachment->item->getPhotoUrl();
            if (strpos($photoURL, 'http') === false) {
              $baseURL = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : 'http://';
              $photoURL = $baseURL . $_SERVER['HTTP_HOST'] . $photoURL;
            }
          }
        }
        ?>
        <?php
        $imageType = "";
        foreach ($actionAttachment as $attachment) {
          $imageType = $attachment->item->getType();
          break;
        }
        
        ?>
        <div class='<?php if ($width > 250): ?> link_attachment_big <?php endif; ?> feed_item_attachments <?php if ($buysellActive || strpos($imageType, '_photo') == true || $imageType == 'video' || strpos($action->type, '_photo') == true): ?> feed_images feed_images_<?php echo $classnumber; ?><?php endif; ?>'>
          <?php if ($action->attachment_count > 0 && engine_count($actionAttachment) > 0): ?>
            <?php if (null != ($richContent = current($actionAttachment)->item->getRichContent()) && empty(current($actionAttachment)->item->feedupload)): ?>
                <?php echo $richContent; ?>
            <?php else: ?>
              <?php foreach ($actionAttachment as $attachment):
                if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video') && $attachment->item->getType() == 'video') {
                  if($attachment->item->status != 1) continue;
                }
                if ($attachmentShowCount == $counterAttachment)
                  break;
                ?>
                <span class='feed_attachment_<?php echo $attachment->meta->type ?>  <?php if (method_exists(current($actionAttachment)->item, 'getAttachmentClass')) :?> <?php echo current($actionAttachment)->item->getAttachmentClass();?><?php endif;?> <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video') && $attachment->item->getType() == 'video'): ?> _video <?php endif; ?>'>

                  <?php if ($attachment->meta->mode == 0): // Silence ?>
                  <?php elseif ($attachment->meta->mode == 1): // Thumb/text/title type actions ?>
                    <div>
                      <?php
                      if (engine_in_array($attachment->item->getType(), array("storage_file", "core_link"))) {
                        $attribs = array('target' => '_blank');
                      } else {
                        $attribs = array();
                      }
                      ?>
                      <?php if ($attachment->item->getPhotoUrl()): ?>
                        <?php if ($countAttachment > 1)
                          $imageType = 'thumb.normalmain';
                        else
                          $imageType = 'thumb.normal';
                        ?>
                        <?php //member profile photo feed with cover work
                          if ($action->type == 'profile_photo_update') { ?>
                          <?php $cover = $action->getSubject()->coverphoto; ?>
                          <?php if ($cover) { ?>
                            <?php $memberCover = Engine_Api::_()->storage()->get($cover, '');
                            if ($memberCover) {
                              $memberCover = $memberCover->getPhotoUrl(); ?>
                              <div class="activity_feed_usercover">
                                <div class="_cover">
                                  <img id="sesusercoverphoto_cover_id" src="<?php echo $memberCover; ?>" />
                                </div>
                                <div class="_mainphoto">
                                  <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array()), array_merge($attribs, array())); ?>
                                </div>
                              </div>
                            <?php }
                          } else { ?>
                            <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array('class' => '_activitypinimg')), array_merge($attribs, array())); ?>
                          <?php } ?>
                        <?php } else { ?>
                          <?php if($attachment->item->getType() == 'video' && isset($attachment->item->feedupload) && !empty(isset($attachment->item->feedupload))) { ?>
                            <?php echo $richContent; ?>
                          <?php } else { ?>
                            <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array('class' => '_activitypinimg')), array_merge($attribs, array())); ?>
                          <?php } ?>
                        <?php } ?>
                      <?php endif; ?>
                      <?php if (!empty($attachment->item) && $attachment->item instanceof Core_Model_Link) { 
                          $explodeCode = explode('|| IFRAMEDATA', $attachment->item->description);
                          ?>
                          <div class="composer_link_iframe_content clearfix">
                            <div class="composer_link_iframe clearfix">
                              <?php echo $explodeCode[1]; ?>
                            </div>
                            <div class="composer_link_iframe_content_info clearfix">
                              <div class="feed_item_link_title">
                                <a href="<?php echo $attachment->item->getHref(); ?>" target="_blank">
                                  <?php echo $attachment->item->title; ?></a>
                              </div>
                              <div class="feed_item_link_desc">
                                <?php echo $explodeCode[0]; ?>
                              </div>
                            </div>
                          </div>
                      <?php } else if ($attachment->item->getType() == 'activity_file') { ?>
                          <div class="activity_attachment_file clearfix">
                            <div class="activity_attachment_file_img">
                              <?php
                              $storage = Engine_Api::_()->getItem('storage_file', $attachment->item->item_id);
                              $filetype = current(explode('_', Engine_Api::_()->core()->fileTypes($storage->mime_major . '/' . $storage->mime_minor)));
                              ?>
                            <?php if ($filetype) { ?>
                                <img src="application/modules/Activity/externals/images/file-icons/<?php echo $filetype . '.png'; ?>">
                            <?php } else { ?>
                                <img src="application/modules/Activity/externals/images/file-icons/default.png">
                            <?php } ?>
                            </div>
                            <div class="activity_attachment_file_info">
                              <div class='feed_item_link_title'>
                              <?php echo $storage->name; //$this->htmlLink($attachment->item->getHref(), $storage->name ? $attachment->name : ''); ?>
                              </div>
                              <div class="activity_attachment_file_type font_color_light"><?php echo ucfirst($filetype); ?></div>
                              <div class='activity_attachment_file_btns d-flex gap-2'>
                              <?php if ($this->viewer()->getIdentity() != 0) { ?>
                                  <a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'download', 'file_id' => $attachment->item->item_id), 'default'); ?>" class="btn btn-alt btn-small ajaxPrevent"><span><?php echo $this->translate("Download"); ?></span></a>
                              <?php } ?>
                              <?php if ($filetype == 'image') { ?>
                                  <a href="<?php echo $storage->map(); ?>" class="btn btn-alt btn-small activity_popup_preview ajaxPrevent"><span><?php echo $this->translate("Preview"); ?></span></a>
                              <?php } else if ($filetype == 'pdf') { ?>
                                    <a href="<?php echo $storage->map(); ?>" target="_blank" class="btn btn-alt btn-small ajaxPrevent"><span><?php echo $this->translate("Preview"); ?></span></a>
                              <?php } ?>
                              </div>
                            </div>
                          </div>
                      <?php } else {
                        ?>
                          <div>
                            <div class='feed_item_link_title'>
                            <?php echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs);
                            ?>
                            </div>
                            <div class='feed_item_link_desc'>
                              <?php

                              if (engine_in_array($attachment->item->getType(), array( 'activity_action', 'activity_buysell'))) {
                                $previousAction = $action;
                                $previousAttachment = $attachment;
                                $actionId = $attachment->item->getIdentity();
                                if($attachment->item->getType() == 'activity_buysell') {
                                  $actionId = $attachment->item->action_id;
                                  $isBuySellShareFeed = true;
                                }
                                $action = Engine_Api::_()->getItem('activity_action', $actionId);
                                $hideOptions = true;
                                include ('application/modules/Activity/views/scripts/_activity.tpl');
                                $action = $previousAction;
                                $hideOptions = false;
                                $isBuySellShareFeed = false;
                                $attachment = $previousAttachment;
                                $previousAction = $previousAttachment = "";
                              } else { ?>
                              <?php $attachmentDescription = $attachment->item->getDescription(); ?>
                              <?php if ($action->body != $attachmentDescription): ?>
                                <?php echo $this->viewMoreActivity($attachmentDescription); ?>
                              <?php endif;
                              }
                              ?>
                            </div>
                          <?php if ($attachment->item && $attachment->item->getType() == 'core_link') { ?>
                            <?php $link = Engine_Api::_()->getItem('core_link', $attachment->item->getIdentity());
                            $parse = parse_url($link->uri);
                            ?>
                            <?php if (!empty($parse['host']) && isset($parse['host'])) { ?>
                              <?php $host = (preg_match("#https?://#", $parse['host']) === 0) ? 'http://' . $parse['host'] : $parse['host']; ?>
                                <div class="_link_source"><a href="<?php echo $host; ?>"><?php echo strtoupper($parse['host']); ?></a>
                                </div>
                            <?php } ?>
                          <?php } ?>
                          </div>
                        <?php
                      } ?>
                    </div>
                  <?php elseif ($attachment->meta->mode == 2): // Thumb only type actions 
                      if ($buysellActive) {
                        $imageAttribs = array('data-url' => 'activity/ajax/feed-buy-sell/action_id/' . $action->getIdentity() . '/photo_id/' . $attachment->item->getIdentity() . '/main_action/' . (!empty($changeAction) ? $changeAction->getIdentity() : $action->getIdentity()), 'class' => 'ajaxsmoothbox');
                        $linkHref = 'javascript:;';
                        $classbuysell = "activity_buysell";
                      } else {
                        $imageAttribs = array();
                        $classbuysell = '';
                        $linkHref = $attachment->item->getHref();
                      }
                    ?>
                    <?php if ($counterAttachment == $showCountAttachment && $viewMoreText > 0) { ?>
                      <?php $imageMoreText = '<p class="_photocounts"><span>+' . $viewMoreText . '</span></p>'; ?>
                    <?php } else {
                      $imageMoreText = '';
                    } ?>
                    <div class="feed_attachment_photo <?php echo $classbuysell; ?>">
                      <?php if($attachment->item->getType() == 'video' && $action->attachment_count == 1) { ?>
                        <?php if (null != ($richContent = $attachment->item->getRichContent())): ?>
                        <?php echo $richContent; ?>
                        <?php endif; ?>
                      <?php } else { ?>
                        <?php if(!empty($attachment->item->feedupload)) {  
                          $imageAttribs = array_merge($imageAttribs, array('class' => 'smoothbox'));
                        } ?>
                        <?php echo $this->htmlLink($linkHref, $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle(), array('class' => '')) . $imageMoreText, $imageAttribs) ?>
                      <?php } ?>
                    </div>
                  <?php elseif ($attachment->meta->mode == 3): // Description only type actions ?>
                    <?php echo $this->viewMoreActivity($attachment->item->getDescription()); ?>
                  <?php elseif ($attachment->meta->mode == 4): // Multi collectible thingy (@todo) ?>
                  <?php endif; ?>
                </span>
                <?php
                $counterAttachment++;
              endforeach; ?>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($buysellActive && empty($isBuySellShareFeed) && $action->type != 'share') { ?>
        <div class="activity_feed_item_buysell_main">
          <div class="activity_feed_item_buysell">
            <div class="activity_feed_item_buysell_title"><?php echo $buysell->title; ?></div>
            <div class="activity_feed_item_buysell_price font_color_hl">
              <?php echo Engine_Api::_()->payment()->getCurrencyPrice($buysell->price); ?>
            </div>
            <?php if ($locationBuySell) { ?>
              <div class="activity_feed_item_buysell_location font_color_light">
                <i class="icon_map"></i>
                <span>
                  <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
                    <a href="<?php echo 'http://maps.google.com/?q='.$locationBuySell->venue; ?>" target='_blank'><?php echo $locationBuySell->venue; ?></a>
                  <?php } ?>
                </span>
              </div>
            <?php } else if($buysell->location) { ?>
              <div class="activity_feed_item_buysell_location font_color_light">
                <i class="icon_map"></i>
                <span>
                  <a href="<?php echo 'http://maps.google.com/?q='.$buysell->location; ?>" target='_blank'><?php echo $buysell->location; ?></a>
                </span>
              </div>
            <?php } ?>
            <?php if ($buysell->description) { ?>
              <div class="activity_feed_item_buysell_des"><?php echo $this->viewMoreActivity($buysell->description); ?>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
        <?php if ($buysellActive && empty($isBuySellShareFeed)  && $action->type != 'share') { ?>
          <div class="activity_feed_item_buysell_btn d-flex gap-2">
            <?php if ($buysell->buy && !$buysell->is_sold) { ?>
              <a class="btn btn-primary" href="<?php echo $buysell->buy; ?>" target="_blank"><i class="fas fa-shopping-cart"></i><?php echo $this->translate("Buy Now"); ?></a>
            <?php } ?>
            <?php if ($this->viewer()->getIdentity() != 0) { ?>
              <?php if (!$buysell->is_sold) { ?>
                <?php if ($action->subject_id != $this->viewer()->getIdentity()) { ?>
                  <?php $url = $this->baseUrl() . '/activity/ajax/message/action_id/' . $action->getIdentity(); ?>
                  <button class="btn btn-alt" onClick="openSmoothBoxInUrl('<?php echo $url; ?>');return false;"><i class="fas fa-comment"></i>Message
                    Seller</button>
                <?php } else { ?>
                  <button class="btn btn-primary mark_as_sold_buysell mark_as_sold_buysell_<?php echo $action->getIdentity(); ?>"
                    data-sold="<?php echo $this->translate('Sold'); ?>" data-href="<?php echo $action->getIdentity(); ?>"><i
                      class="fas fa-check"></i><?php echo $this->translate("Mark as Sold"); ?></button>
                <?php } ?>
              <?php } else { ?>
                <button class="btn btn-success"><i class="fas fa-check"></i><?php echo $this->translate("Sold"); ?></button>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      <?php } ?>
      <?php $getAllHashtags = !is_string($action) ? Engine_Api::_()->getDbTable('hashtags', 'activity')->getAllHashtags($action->getIdentity()) : 0; ?>
      <?php if (engine_count($getAllHashtags) > 0 && !engine_in_array($action->type, array('status'))) { ?>
        <div class="activity_feed_tags">
          <?php
          $hashTagsString = '';
          foreach ($getAllHashtags as $value) {
            if ($value->title == '')
              continue;
            if (strpos($action->body, $value->title) === false) {
            } else {
              $hashTagsString .= '<a href="hashtag?search=' . $value->title . '">#' . ltrim(strip_tags($value->title)) . '</a> ';
            }
          }
          echo $hashTagsString;
          ?>
        </div>
      <?php } ?>
      <?php if (!empty($changeAction)) {
        $action = $changeAction;
        $changeAction = '';
      } ?>
      <?php if (!empty($action) && $action->schedule_time && empty($previousAction)) { ?>
        <div class="activity_feed_schedule_post_time alert alert-info text-center font_small">
          <?php echo $this->translate("This post will be publish on"); ?>
          <b><?php echo date('Y-m-d H:i:s', strtotime($action->schedule_time)); ?></b>.
        </div>
      <?php } ?>
    </div>


    <?php $sescommunityads = empty($sescommunityads) ? engine_in_array('sescommunityads', $this->enabledModuleNames) : $sescommunityads; ?>
    <?php if (empty($fromActivityFeed) && empty($_SESSION['fromActivityFeed']) && !empty($action) && !$action->schedule_time && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost') && empty($previousAction) && $sescommunityads && is_array(Engine_Api::_()->sescommunityads()->allowedTypes($action)) && engine_in_array('boos_post', Engine_Api::_()->sescommunityads()->allowedTypes($action)) && Engine_Api::_()->sescommunityads()->getAllowedActivityType($action->type) && ($action->subject_id == $this->viewer()->getIdentity() && $action->subject_type == "user")) {
      if (Engine_Api::_()->authorization()->isAllowed('sescommunityads', $this->viewer(), 'create')) {
        ?>
        <div class="activity_boost_post clear">
          <?php if (!empty($action->view_count)) { ?>
            <div class="activity_boost_post_reach font_color_light">
              <?php echo $this->translate("%s people Reached", $action->view_count); ?>
            </div>
          <?php } ?>
          <div class="activity_boost_btn">
            <a href="<?php echo $this->url(array("controller" => "index", "action" => "create", 'action_id' => $action->action_id), 'sescommunityads_general', true); ?>" class="btn btn-primary"><?php echo $this->translate('Boost Post'); ?></a>
          </div>
        </div>
        <?php
      }
    }
    if (!empty($_SESSION['fromActivityFeed']))
      $_SESSION['fromActivityFeed'] = "";
    ?>

    <?php
    if (empty($this->ulInclude) && $action->subject_id != $this->viewer()->getIdentity() && $action->subject_type == "user" && empty($previousAction) && empty($fromActivityFeed) && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost')) {
      $action->view_count++;
      $action->save();
    } ?>

    <?php if (!empty($action) && !$action->schedule_time && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost') && empty($previousAction)) { ?>
      <div class="comment_cnt activity_comments clearfix"
        id='comment-likes-activity-item-<?php echo $action->action_id ?>'>
        <?php if (!is_string($action)): ?>
          <?php echo $this->activity($action, array('noList' => true, 'isOnThisDayPage' => $this->isOnThisDayPage, 'viewAllLikes' => $this->viewAllLikes, 'enabledModuleNames' => $this->enabledModuleNames), 'update', $this->viewAllComments); ?>
        <?php endif; ?>
      </div> <!-- End of Comment Likes -->
    <?php } ?>
    <?php if (!$this->noList): ?>
    </section>
  </li><?php endif; ?>
