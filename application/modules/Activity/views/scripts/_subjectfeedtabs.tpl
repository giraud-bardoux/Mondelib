<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _subjectfeedtabs.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<script type="application/javascript">
  var filterResultrequest;
  AttachEventListerSE('click', 'ul.activity_filter_tabs li a', function (e) {

    if (scriptJquery(this).hasClass('viewmore'))
      return false;

    scriptJquery('.activity_filter_img').css('display','flex');
    scriptJquery('.activity_filter_tabsli').removeClass('active activity_active_tabs');
    scriptJquery(this).parent().addClass('active activity_active_tabs');
    var filterFeed = scriptJquery(this).attr('data-src');
    var feed_filter_text = scriptJquery(this).attr('data-text');
    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';
    var hashTag = scriptJquery('#hashtagtext').val();
    var adsIds = scriptJquery('.ecmads_ads_listing_item');
    var adsIdString = "";
    if (adsIds.length > 0) {
      scriptJquery('.ecmads_ads_listing_item').each(function (index) {
        if (typeof dataFeedItem == "undefined")
          adsIdString = scriptJquery(this).attr('rel') + "," + adsIdString;
      });
    }
    filterResultrequest = scriptJquery.ajax({
      url: url + "?search=" + hashTag + '&isOnThisDayPage=' + isOnThisDayPage + '&isMemberHomePage=' + isMemberHomePage,
      type: "POST",
      data: {
        format: 'html',
        'filterFeed': filterFeed,
        'feedOnly': true,
        'action_id': activityGetAction_id,
        'getUpdates': 1,
        'nolayout': true,
        'ads_ids': adsIdString,
        'subject': en4.core.subject.guid,
      },
      evalScripts: true,
      success: function (responseHTML) {
        scriptJquery('#feed_filter_text').html(feed_filter_text);
        if (!activityGetFeeds) {
          scriptJquery('#activity-feed').append(responseHTML);
        } else {
          scriptJquery('#activity-feed').html(responseHTML);
        }
        if (scriptJquery('#activity-feed').find('li').length > 0)
          scriptJquery('.activity_noresult_tip').hide();
        else
          scriptJquery('.activity_noresult_tip').show();
        //initialize feed autoload counter
        counterLoadTime = 0;
        activitytooltip();
        Smoothbox.bind(document.getElementById('activity-feed'));
        scriptJquery('.activity_filter_img').hide();
        activateFunctionalityOnFirstLoad();
      }
    });
  });
</script>
<?php
$lists = $this->lists;
?>
<div class="activity_feed_filters activity_feed_profile_filters mprofile_filter_tabs clearfix" style="display: none;">
  <ul class="activity_filter_tabs">
    <li style="display:none;" class="activity_filter_img"><i class='fas fa-circle-notch fa-spin'></i></li>
    <?php
    $counter = 1;
    foreach ($lists as $activeList) {
      if (@$activeList['filtertype'] == 'all') {
        ?>
        <li class="activity_filter_tabsli">
          <a href="javascript:;" data-text="<?php echo $this->translate($activeList['title']); ?>" data-src="<?php echo @$activeList['filtertype']; ?>"><i class="<?php echo $activeList['icon']; ?>"></i><?php echo $this->translate(@$activeList['title']); ?></a>
        </li>
      <?php
      }
    } ?>
    <li class="activity_filter_tabsli">
      <a href="javascript:;" data-text="<?php echo $this->translate(strlen($this->subject()->getTitle(false)) > 20 ? $this->string()->truncate($this->subject()->getTitle(false), 20) . '...' : $this->subject()->getTitle(false)); ?>" data-src="<?php echo 'own'; ?>">
        <i class="icon_activity_subject"></i>
        <?php echo strlen($this->subject()->getTitle(false)) > 20 ? $this->string()->truncate($this->subject()->getTitle(false), 20) . '...' : $this->subject()->getTitle(false); ?>
      </a>
    </li>
    <?php if ($this->subject() && method_exists($this->subject(), 'approveAllowed') && method_exists($this->subject(), 'canApproveActivity') && $this->subject()->canApproveActivity($this->subject())) {
      $approveAllowed = $this->subject()->approveAllowed();
      if ($approveAllowed) {
        ?>
        <li class="activity_filter_tabsli">
          <a href="javascript:;" data-text="<?php echo $this->translate("Un-Approved Feeds"); ?>" data-src="<?php echo 'unapprovedfeed'; ?>"><i class="icon_activity_unapproved"></i><?php echo $this->translate("Un-Approved Feeds"); ?></a>
        </li>
      <?php }
      }
    ?>
    <?php if ($this->viewer()->getIdentity() && $this->subject()->getGuid() == $this->viewer()->getGuid()) { ?>
      <li class="activity_filter_tabsli">
        <a href="javascript:;" data-text="<?php echo $this->translate("Posts You've Hidden"); ?>" data-src="hiddenpost"><i class="icon_activity_hide"></i><?php echo $this->translate("Posts You've Hidden"); ?></a>
      </li>
      <li class="activity_filter_tabsli">
        <a href="javascript:;" data-text="<?php echo $this->translate("Posts You're Tagged In"); ?>" data-src="taggedinpost"><i class="icon_activity_tag"></i><?php echo $this->translate("Posts You're Tagged In"); ?></a>
      </li>
    <?php } ?>
  </ul>
</div>
<script type="application/javascript">
  en4.core.runonce.add(function() {
    var elem = scriptJquery('.activity_filter_tabs').children();
    if (elem.length == 2) {
      scriptJquery('.activity_feed_profile_filters').hide();
    } else {
      // scriptJquery(elem).eq(1).addClass('active');
    }
  });
</script>