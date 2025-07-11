<?php

 /**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _homefeedtabs.tpl 2024-10-28 00:00:00Z 
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
 AttachEventListerSE('click','ul.activity_filter_tabs li a',function(e){
   if(scriptJquery(this).hasClass('viewmore'))
    return false;
   scriptJquery('.activity_filter_img').css('display','flex');
   scriptJquery('.activity_filter_tabsli').removeClass('active activity_active_tabs');
   scriptJquery(this).parent().addClass('active activity_active_tabs');
   var filterFeed = scriptJquery(this).attr('data-src');
   //if(typeof filterResultrequest != 'undefined')
    //filterResultrequest.remove();
    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';
    var hashTag = scriptJquery('#hashtagtext').val();
    
    var adsIds = scriptJquery('.ecmads_ads_listing_item');
    var adsIdString = "";
    if(adsIds.length > 0){
       scriptJquery('.ecmads_ads_listing_item').each(function(index){
         var dataFeedItem = scriptJquery(this).attr('data-activity-feed-item');
         if(typeof dataFeedItem == "undefined")
          adsIdString = scriptJquery(this).attr('rel')+ "," + adsIdString ;
       });
    }
    var feed_filter_text = scriptJquery(this).attr('data-text');
    filterResultrequest = scriptJquery.ajax({
      url: url + "?search=" + hashTag + '&isOnThisDayPage=' + isOnThisDayPage + '&isMemberHomePage=' + isMemberHomePage,
      type: "POST",
      data : {
        format : 'html',
        'filterFeed' : filterFeed,
        'feedOnly' : true,
        'ads_ids': adsIdString,
          'getUpdates':1,
        'nolayout' : true,
        'subject' : '<?php echo !empty($this->subjectGuid) ? $this->subjectGuid : "" ?>',
      },
      evalScripts : true,
      success : function( responseHTML) {
        scriptJquery('#feed_filter_text').html(feed_filter_text);
        if(!activityGetFeeds){
            scriptJquery('#activity-feed').append(responseHTML);
        }else{
            scriptJquery('#activity-feed').html(responseHTML);
        }
        if(scriptJquery('#activity-feed').find('li').length > 0){
          scriptJquery('.activity_noresult_tip').hide();
          if(scriptJquery('#feed_viewmore').css('display') == 'none' && scriptJquery('#feed_loading').css('display') == 'none')
            scriptJquery('#feed_no_more_feed').show();
        }else{
          scriptJquery('#feed_no_more_feed').hide();
          scriptJquery('.activity_noresult_tip').css('display','block');
        }
        //initialize feed autoload counter
        counterLoadTime = 0;
        activitytooltip();
        Smoothbox.bind(document.getElementById('activity-feed'));
        scriptJquery('.activity_filter_img').hide();
        feedUpdateFunction();
          activateFunctionalityOnFirstLoad();

      }
    });
 });
</script>
<?php 
  $filterViewMoreCount = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.visiblesearchfilter',4);
  $lists = $this->lists;
 ?>
<div class="activity_feed_filters" style="display: none;">
  <ul class="activity_filter_tabs">
    <li style="display:none;" class="activity_filter_img"><i class='fas fa-circle-notch fa-spin'></i></li>
   <?php 
   $counter = 1;
   $netwrokStarted = false;
   $listStarted = false;
   $listsCount = engine_count($lists);
   foreach($lists as $activeList){
    if($counter > $filterViewMoreCount)
      break;
    if(isset($activeList['network_id'])){
      if(!$netwrokStarted){  $netwrokStarted = true; ?>
        <li class="_sep sesbm"></li>
     <?php
      } ?>
    <li class="activity_filter_tabsli <?php echo $counter == 1 ? 'active activity_active_tabs' : ''; ?>"><a href="javascript:;" class="activity_tooltip" data-src="<?php echo 'network_filter_'.$activeList['network_id']; ?>" title="<?php echo $this->translate($activeList['title']); ?>">
      <i class="fa <?php echo $activeList['icon']; ?>"></i>
      <span><?php echo $this->translate($activeList['title']); ?></span>
    </a></li>
   <?php   
    }else if(isset($activeList['list_id'])){
    
      if(!$listStarted){  $listStarted = true; ?>
        <li class="_sep sesbm"></li>
     <?php
      } ?>
      <li class="activity_filter_tabsli <?php echo $counter == 1 ? 'active activity_active_tabs' : ''; ?>"><a href="javascript:;" class="activity_tooltip" data-src="<?php echo 'member_list_'.$activeList['list_id']; ?>" title="<?php echo $this->translate($activeList['title']); ?>">
        <i class="fa <?php echo $activeList['icon']; ?>"></i>
        <span><?php echo $this->translate($activeList['title']); ?></span> 
    </a></li>
   <?php   
    }else{
    ?>
   
    <li class="activity_filter_tabsli <?php echo $counter == 1 ? 'active activity_active_tabs' : ''; ?>"><a href="javascript:;" class="activity_tooltip" data-src="<?php echo $activeList['filtertype']; ?>" title="<?php echo $this->translate($activeList['title']); ?>">
      <i class="fa <?php echo $activeList['icon']; ?> item_icon_<?php echo $activeList['filtertype'] ?>"></i>
      <span><?php echo $this->translate($activeList['title']); ?></span></a></li>
   
   <?php 
   }
    ++$counter;
   } ?>
   <?php if($listsCount > $filterViewMoreCount){ ?>
    <li class="activity_feed_filter_more dropdown">
      <a href="javascript:;" class="viewmore" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?php echo $this->translate("More"); ?>&nbsp;<i class="fa-solid fa-angle-down"></i></a>
        	<ul class="activity_filter_tabs dropdown-menu dropdown-option-menu dropdown-menu-end">
          <?php 
           $counter = 1;
           foreach($lists as $activeList){
            if($counter <= $filterViewMoreCount){
              ++$counter;
              continue;
             }
             if(isset($activeList['network_id'])){
                if(!$netwrokStarted){ $netwrokStarted = true; ?>
                  <li class="dropdown-divider"></li>
               <?php
                } ?>
                <li class="activity_filter_tabsli"><a href="javascript:;" class="dropdown-item" data-src="<?php echo 'network_filter_' . $activeList['network_id']; ?>" data-text="<?php echo $this->translate($activeList['title']); ?>"> <i class="icon_network <?php echo $activeList['icon'] ? $activeList['icon'] : 'fas fa-network-wired'; ?>"></i><?php echo $this->translate($activeList['title']); ?></a></li>
             <?php   
              }else if(isset($activeList['list_id'])){
                if(!$listStarted){ $listStarted = true; ?>
                  <li class="dropdown-divider"></li>
               <?php
                } ?>
                <li class="activity_filter_tabsli"><a href="javascript:;" class="dropdown-item" data-src="<?php echo 'member_list_' . $activeList['list_id']; ?>" data-text="<?php echo $this->translate($activeList['title']); ?>"> <i class="icon_activity_lists <?php echo $activeList['icon']; ?>"></i><?php echo $this->translate($activeList['title']); ?></a></li>
             <?php   
              }else{
            ?>
              <li class="activity_filter_tabsli <?php echo $counter == 1 ? 'active activity_active_tabs' : ''; ?>"><a href="javascript:;" class="dropdown-item" data-src="<?php echo $activeList['filtertype']; ?>" data-text="<?php echo $this->translate($activeList['title']); ?>"><i class="<?php echo $activeList['icon'] ? $activeList['icon'] :  'item_icon_'.$activeList['filtertype'] ?>"></i><?php echo $this->translate($activeList['title']); ?></a></li>
           <?php 
              }
           } ?>
        	</ul>

    </li>
    <?php if ($this->viewer()->getIdentity()) { ?>
      <li class="activity_filter_tabsli activity_feed_filter_setting">
        <a href="javascript:;" class="ajaxsmoothbox viewmore" data-bs-toggle="tooltip" title="<?php echo $this->translate('Settings'); ?>" data-url="activity/ajax/settings/"><i class="fa fa-cog" aria-hidden="true"></i></a>
      </li>
    <?php } ?>
  <?php } ?>  
    
  </ul>
</div>
