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
<?php
	$staticBaseUrl = $this->layout()->staticBaseUrl;
	
  $enabledModuleNames = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames(); 
  $settings = Engine_Api::_()->getApi('settings', 'core');
  
  $getFeelings = Engine_Api::_()->getDbTable('feelings', 'activity')->getFeelings(array('fetchAll' => 1, 'admin' => 0));

  $getEmojis = Engine_Api::_()->getDbTable('emojis', 'activity')->getEmojis(array('fetchAll' => 1));

  $this->headTranslate(array('More','Close','Permalink of this Post','Copy link of this feed:','Go to this feed','You won\'t see this post in Feed.',"Undo","Hide all from",'You won\'t see'," post in Feed.","Select","It is a long established fact that a reader will be distracted","If you find it offensive, please","file a report.", "Choose Feeling or activity...", "How are you feeling?", "ADD POST", "Schedule Post", "Your post successfully scheduled on ", "Your feed is currently being processed - you will be notified when it is ready to be viewed."));

	if($this->feeddesign == 2) {
		$randonNumber = 'pinFeed'; 
	}
?>
<script type="application/javascript">

	<?php if(!$this->feedOnly && $this->autoloadTimes > 0 && $this->scrollfeed ) { ?>
		var autoloadTimes = '<?php echo $this->autoloadTimes; ?>';
		var counterLoadTime = 0;
		en4.core.runonce.add(function() {
			scriptJquery(window).scroll( function() {
				var containerId = '#activity-feed';
				if(typeof scriptJquery(containerId).offset() != 'undefined' && scriptJquery('#feed_viewmore_activityact').length > 0) {
					var heightOfContentDiv = scriptJquery(containerId).height();
					var fromtop = scriptJquery(this).scrollTop() + 300;
					if(fromtop > heightOfContentDiv - 100 && scriptJquery('#feed_viewmore_activityact').css('display') == 'block' && autoloadTimes > counterLoadTime){
						document.getElementById('feed_viewmore_activityact_link').click();
						counterLoadTime++;
					}
				}
			});
		});
  <?php } ?>

	function setFocus(){
		document.getElementById("activity_body").focus();
	}
	var activityGetFeeds = <?php echo $this->getUpdates ?>;
	var activityGetAction_id = <?php echo $this->action_id; ?>;
  var subject_guid = '<?php echo $this->subjectGuid ?>';
	if(!activityGetFeeds){
		en4.core.runonce.add(function() {
			scriptJquery('ul.activity_filter_tabs li a:first').trigger("click");
		});
	}
	function activateFunctionalityOnFirstLoad() {
		var action_id = <?php echo $this->action_id; ?>;
		activityGetFeeds = true;

		if(!action_id) {
			scriptJquery(".activity_feed_filters").show();
      scriptJquery(".activity_feed_profile_filters").show();
			if (scriptJquery('#activity-feed').find('li').length > 0)
				scriptJquery('.activity_noresult_tip').hide();
			else
				scriptJquery('.activity_noresult_tip').show();
		}else{
			if (!scriptJquery('#activity-feed').find('li').length > 0)
				scriptJquery(".no_content_activity_id").show();
		}
		scriptJquery(".activity_content_load_img").hide();
	}

	<?php if($this->feeddesign != 2) { ?>
		function feedUpdateFunction(){
      en4.core.runonce.trigger();
    }
	<?php } ?>
</script>

<?php $viewer = $this->viewer(); ?>
<script type="application/javascript">
	en4.core.runonce.add(function() {
		carouselReaction();
	});

	var privacySetAct = false;
	<?php if( !$this->feedOnly && $this->action_id){ ?>
    en4.core.runonce.add(function() {
		scriptJquery('.tab_<?php echo $this->identity; ?>.tab_layout_activity_feed').find('a').click();
	});
	<?php } ?>
</script>

<?php if( !$this->feedOnly && $this->isMemberHomePage && 0): ?>
  <div class="activity_tabs_wrapper clearfix ">
    <ul id="activity_tabs_cnt" class="activity_tabs clearfix">
      <li data-url="2" class="activity_update_tab">
        <a href="javascript:;">
          <span><?php echo $this->translate("What's New"); ?></span>
          <span id="count_new_feed"></span>
        </a>
      </li>
    </ul>
  </div>
  <script type="application/javascript">
   en4.core.runonce.add(function() {
      if(scriptJquery('#activity_tabs_cnt').children().length == 1){
        scriptJquery('#activity_tabs_cnt').parent().remove(); 
      }
    });
  </script>
  <div id="activity_tab_2" class="activity_tabs_content">
<?php endif; ?> 
<?php if( (!empty($this->feedOnly) || !$this->endOfFeed ) && (empty($this->getUpdate) && empty($this->checkUpdate)) ):
    $adsEnable = $settings->getSetting('activity.adsenable', 0);
?>
<script type="text/javascript">
  
  function defaultSettingsActivity(){
  
      var activity_count = <?php echo sprintf('%d', $this->activityCount) ?>;
      var next_id = <?php echo sprintf('%d', $this->nextid) ?>;
      var subject_guid = '<?php echo $this->subjectGuid ?>';
      var endOfFeed = <?php echo ( $this->endOfFeed ? 'true' : 'false' ) ?>;
      var activityViewMore = window.activityViewMore = function(next_id, subject_guid) {
        //if( en4.core.request.isRequestActive() ) return;
        var hashTag = scriptJquery('#hashtagtext').val();
        var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';         
         if(typeof itemSubjectGuid != "undefined")
            var itemSubject = itemSubjectGuid;
          else
            var itemSubject = "";
        document.getElementById('feed_viewmore_activityact').style.display = 'none';
        document.getElementById('feed_loading').style.display = '';
        
        var adsIds = scriptJquery('.ecmads_ads_listing_item');
        var adsIdString = "";
        if(adsIds.length > 0){
           scriptJquery('.ecmads_ads_listing_item').each(function(index){
             adsIdString = scriptJquery(this).attr('rel')+ "," + adsIdString ;
           });
        }
        
          var request = scriptJquery.ajax({
            type:"POST",
          url : url+"?search="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
          type: 'post',
          data : {
            format : 'html',
            'maxid' : next_id,
            'feedOnly' : true,
            'nolayout' : true,
            'getUpdates' : true,
            'subject' : subject_guid,
            'ads_ids': adsIdString,
            'contentCount':scriptJquery('#activity-feed').find("[id^='activity-item-']").length,
            'filterFeed':scriptJquery('.activity_filter_tabs .active > a').attr('data-src'),
          },
          evalScripts : true,
          success : function( responseHTML) {
            scriptJquery("#activity-feed").append(responseHTML);
            // en4.core.runonce.trigger();
            // Smoothbox.bind(document.getElementById('activity-feed'));
            feedUpdateFunction();
            <?php if($adsEnable){ ?>
            displayGoogleAds();
            <?php  } ?>
            activitytooltip();
          }
        });
      }
      
      if( next_id > 0 && !endOfFeed ) {
        scriptJquery('#feed_viewmore_activityact').show();
        scriptJquery('#feed_loading').hide();
        if(scriptJquery('#feed_viewmore_activityact_link').length){
          scriptJquery('#feed_viewmore_activityact_link').off('click').click( function(event){
            activityViewMore(next_id, subject_guid);
          });
        }
      } else {
        
        scriptJquery('#feed_viewmore_activityact').hide();
        scriptJquery('#feed_loading').hide();
      }
  }
  <?php if($adsEnable){ ?>
  function displayGoogleAds(){
    try{
      scriptJquery('ins').each(function(){
          (adsbygoogle = window.adsbygoogle || []).push({});
      });
      if(scriptJquery('script[src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"]').length == 0){        
        var script = document.createElement('script');
        script.src = '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        document.head.appendChild(script);  
      }
    }catch(e){
      //silence  
    }
  }
  <?php } ?>
    en4.core.runonce.add(function() {defaultSettingsActivity();<?php if($adsEnable){ ?>displayGoogleAds();<?php } ?>});
    defaultSettingsActivity();
  </script>
<?php endif; ?>

<?php if( !empty($this->feedOnly) && empty($this->checkUpdate)): // Simple feed only for AJAX
  echo $this->activityLoop($this->activity, array(
    'action_id' => $this->action_id,
    'communityadsIds' => $this->communityadsIds,
    'viewAllComments' => $this->viewAllComments,
    'viewAllLikes' => $this->viewAllLikes,
    'getUpdate' => $this->getUpdate,
    'ulInclude'=>!$this->getUpdates ? 0 : $this->feedOnly,
    'contentCount'=>$this->contentCount,
    'userphotoalign' => $this->userphotoalign,
    'filterFeed'=>$this->filterFeed,
    'isMemberHomePage' => $this->isMemberHomePage,
    'isOnThisDayPage' => $this->isOnThisDayPage,
    'enabledModuleNames' => $enabledModuleNames
  ));
  return; // Do no render the rest of the script in this mode
endif; ?>

<?php if( !empty($this->checkUpdate) ): // if this is for the live update
  if ($this->activityCount){ ?>
   <script type='text/javascript'>
          document.title = '(<?php echo $this->activityCount; ?>) ' + ActivityUpdateHandler.title;
          ActivityUpdateHandler.options.next_id = "<?php echo $this->firstid; ?>";
          <?php if($this->autoloadfeed){ ?>
            ActivityUpdateHandler.getFeedUpdate("<?php echo $this->firstid; ?>");
            scriptJquery("#feed-update").html('');
          <?php } ?>
          scriptJquery('#count_new_feed').html("<span><?php echo $this->activityCount; ?></span>");
        </script>
   <div class='tip' style="display:<?php echo ($this->autoloadfeed) ? 'none' : '' ?>">
          <span>
            <a href='javascript:void(0);' onclick='javascript:ActivityUpdateHandler.getFeedUpdate("<?php echo $this->firstid ?>");scriptJquery("#feed-update").html('');scriptJquery("#count_new_feed").html("");scriptJquery("#count_new_feed").hide();'>
              <?php echo $this->translate(array(
                  '%d new update is available - click this to show it.',
                  '%d new updates are available - click this to show them.',
                  $this->activityCount),
                $this->activityCount); ?>
            </a>
          </span>
        </div>
 <?php } 
  return; // Do no render the rest of the script in this mode
endif; ?>

<?php if( !empty($this->getUpdate) ): // if this is for the get live update ?>
<script type="text/javascript">
     ActivityUpdateHandler.options.last_id = <?php echo sprintf('%d', $this->firstid) ?>;
   </script>
<?php endif; ?>
<style>
 #scheduled_post, #datetimepicker_edit{display:block !important;}
 </style>
<script type="application/javascript">
  var userphotoalign = '<?php echo $this->userphotoalign; ?>';
</script>
<?php if($this->enableComposer && !$this->isOnThisDayPage): ?>
<script type="application/javascript">
  var activityDesign = '<?php echo $this->design; ?>';
  var activitycommentreverseorder = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', 1); ?>;
  
  var enableStatusBoxHighlight = '<?php echo $this->enableStatusBoxHighlight; ?>';
  var counterLoopComposerItem = counterLoopComposerItemDe4 = 1;
  var composeInstance;
  en4.core.runonce.add(function () {
    try {
     composeInstance = new Composer('activity_body',{
        overText : true,
        allowEmptyWithoutAttachment : false,
        allowEmptyWithAttachment : true,
        postLength:<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000); ?>,
        hideSubmitOnBlur : false,
        submitElement : false,
        useContentEditable : true  ,
        menuElement : 'compose-menu',
        baseHref : '<?php echo $this->baseUrl() ?>',
        lang : {
          'Post Something...' : '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
        }
    });
     }catch(err){ console.log(err); }
      
      AttachEventListerSE('submit','#activity-form',function(e) {
        if(typeof musicfeedupload != 'undefined' && musicfeedupload) {
          return;
        }
        if(scriptJquery(this).hasClass("_request-going")){
          return false;
        }
        var activatedPlugin = composeInstance.getActivePlugin();

        if(activatedPlugin)
         var pluginName = activatedPlugin.getName();
        else 
          var pluginName = '';
        let validationFunctionExists = false;
        try{
          let fnName = "checkValidation_"+pluginName;
          validationFunctionExists = typeof eval(fnName) == "function";
        }catch(e){
          validationFunctionExists = false;
        }

        if(scriptJquery('#image_id').length > 0 && scriptJquery('#image_id').val() != '' || scriptJquery('#reaction_id').val() != '' || scriptJquery('#tag_location').val() != '' || scriptJquery('#toValues').val() != '' || ( scriptJquery('#feeling_activity').length > 0 && scriptJquery('#feeling_activity').val() != '' && scriptJquery('#feelingactivityid').val() != '')) {
          //silence  
        }else if(pluginName != 'buysell' && !validationFunctionExists){
          if( composeInstance.pluginReady ) {
            if( !composeInstance.options.allowEmptyWithAttachment && composeInstance.getContent().trim() == '' ) {
              scriptJquery('.activity_post_box').addClass('_blank');
              e.preventDefault();
              return;
            }
          } else {
            if( !composeInstance.options.allowEmptyWithoutAttachment && composeInstance.getContent().trim() == '' ) {
              e.preventDefault();
              scriptJquery('.activity_post_box').addClass('_blank');
              return;
            }
          }
        }else if (validationFunctionExists){
          let fnName = "checkValidation_"+pluginName;
          var isValidPoll = eval(fnName+"()");
          if(isValidPoll == false){
            e.preventDefault();
            return;
          }
        }
		    else if(pluginName == 'buysell'){
          if(!scriptJquery('#buysell-title').val()){
              if(!scriptJquery('.buyselltitle').length) {
                var errorHTMlbuysell = '<div class="activity_post_error buyselltitle"><?php echo $this->translate("Please enter the title of your product.");?></div>';
                scriptJquery('.activity_sell_composer_title').append(errorHTMlbuysell);
                scriptJquery('#buysell-title').parent().addClass('_blank');
                scriptJquery('#buysell-title').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }
          if(scriptJquery('#buy-url').val() && !isUrl(scriptJquery('#buy-url').val())){
              if(!scriptJquery('.buyurl').length) {
                var errorHTMlbuyurl = '<div class="activity_post_error buyselltitle"><?php echo $this->translate("Please enter valid url.");?></div>';
                scriptJquery('.activity_sell_composer_title').append(errorHTMlbuyurl);
                scriptJquery('#buy-url').parent().addClass('_blank');
                scriptJquery('#buy-url').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }else if(!scriptJquery('#buysell-price').val()){
              if(!scriptJquery('.buysellprice').length) {
                var errorHTMlbuysell = '<div class="activity_post_error buysellprice"><?php echo $this->translate("Please enter the price of your product.");?></div>';
                scriptJquery('.activity_sell_composer_price').append(errorHTMlbuysell);
                scriptJquery('#buysell-price').parent().parent().addClass('_blank');
                scriptJquery('#buysell-price').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }
          
            var field = '<input type="hidden" name="attachment[type]" value="buysell">';
            if(!scriptJquery('.fileupload-cnt').length)
              scriptJquery('#activity-form').append('<div style="display:none" class="fileupload-cnt">'+field+'</div>');
            else
              scriptJquery('.fileupload-cnt').html(field);
              
        }

        //Location composer check for google location
        if(scriptJquery('#tag_location').val() && !scriptJquery('#activitylng').val() && !scriptJquery('#resetaftersubmit').val()) {
          var errorHTMLLocation = '<div id="activity_post_tag_location" class="activity_post_error d-block"><?php echo $this->translate("Please choose a valid location.");?></div>';
          scriptJquery('#activity_post_tag_input').append(errorHTMLLocation);
          scriptJquery('#activity_post_tag_input').addClass('_blank');
          scriptJquery('#activity_post_tag_input').css('border','1px solid red');
          e.preventDefault();
          return;
        }

        scriptJquery('.activity_post_box').removeClass('_blank');
        scriptJquery('.activity_post_loader').removeClass('d-none');
        <?php if($this->submitWithAjax){ ?>
          e.preventDefault();
          var url = "<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>";
          submitActivityFeedWithAjax(url,'<i class="fas fa-circle-notch fa-spin"></i>','<?php echo $this->translate("Share") ?>',this);
          return;
       <?php } ?>
      });
      
      if(scriptJquery('#hashtagtext').val() && typeof composeInstance != "undefined") {
        composeInstance.setContent('#'+scriptJquery('#hashtagtext').val()).trigger('keyup');
      }

      scriptJquery("#activity_body").css("height", "auto");
      
 });
 AttachEventListerSE('keyup', '#buysell-title, #buysell-price, #buy-url', function() {
  if(!scriptJquery(this).val())
    return;
  scriptJquery(this).parent().removeClass('_blank');
  scriptJquery(this).parent().parent().removeClass('_blank');
  scriptJquery(this).css('border', '');
  scriptJquery(this).parent().find('.activity_post_error').remove();

 });
</script>

  <?php if($this->enablestatusbox == 0) { ?>
    <?php $display = 'none'; ?>
  <?php } else if($this->enablestatusbox == 1 && $viewer && $this->subject()) { ?>
    <?php if($viewer->getIdentity() && ($viewer->getIdentity() == $this->subject()->getIdentity())) { ?>
      <?php $display = 'block'; ?>
    <?php } else { ?>
      <?php $display = 'none'; ?>
    <?php } ?>
  <?php } else if($this->enablestatusbox == 2) { ?>
    <?php $display = 'block'; ?>
  <?php } ?>
  <div class="activity_post_container_wrapper clearfix  <?php if($this->design == 2){ ?>activity_cd_p<?php } ?>">
	<div class="activity_post_container_overlay"></div>
	<div class="activity_post_container clearfix block" style="display:<?php echo $display ?>;">
    <form enctype="multipart/form-data" method="post" action="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>" class="" id="activity-form">
      
    	<div class="activity_post_box clearfix" id="activity_post_box_status">
        <div class="activity_post_box_img" id="activity_post_box_img">
          <?php echo $this->htmlLink('javascript:;', $this->itemPhoto($this->viewer(), 'thumb.icon', $this->viewer()->getTitle()), array()) ?>
        </div>
       <?php if($this->design == 2){ ?>
        <div class="activity_post_box_close" style="display:none;"><a class="fas fa-times activity_post_box_close_a font_color" data-bs-toggle="tooltip" title="<?php echo $this->escape($this->translate('Close')) ?>" href="javascript:;"></a></div>
       <?php } ?>
        <textarea style="display:none;" id="activity_body" class="resetaftersubmit" cols="1" rows="1" name="body" placeholder="<?php echo $this->escape($this->translate("Post Something...")) ?>"></textarea>
        <input type="hidden" name="return_url" value="<?php echo $this->url() ?>" />
        <?php if( $this->viewer() && $this->subject() && !$this->viewer()->isSelf($this->subject())): ?>
          <input type="hidden" name="subject" value="<?php echo $this->subject()->getGuid() ?>" />
        <?php endif; ?>
        <input type="hidden" name="crosspostVal" id="crosspostVal"  class="resetaftersubmit" value="">
        <input type="hidden" name="reaction_id" class="resetaftersubmit" id="reaction_id" value="" />
        <?php if( $this->formToken ): ?>
          <input type="hidden" name="token" value="<?php echo $this->formToken ?>" />
        <?php endif ?>
         <input type="hidden" id="hashtagtext" name="hashtagtext" value="<?php echo isset($_GET['hashtag']) ? $_GET['hashtag'] : (@$_GET['search'] ? @$_GET['search'] : ''); ?>" />
        <input type="hidden" name="fancyalbumuploadfileids" class="resetaftersubmit" id="fancyalbumuploadfileids">
        
        <input type="hidden" name="fancyalbumuploadfileidsvideo" class="resetaftersubmit" id="fancyalbumuploadfileidsvideo">
        <input type="hidden" name="multipleupload" class="resetaftersubmit" id="multipleupload">
        
        <div class="activity_post_error"><?php echo $this->translate("It seems, that the post is blank. Please write or attach something to share your post.");?></div>
         <div id="activity_post_tags" class="activity_post_tags font_color_light" style="display:none;">
            <span style="display:none;" id="feeling_elem_act">- </span> <span style="display:none;" id="dash_elem_act">-</span>	
            <span id="tag_friend_cnt" style="display:none;"> with </span> <span id="location_elem_act" style="display:none;"></span>
          </div>

            <?php $activityfeedbg_limit_show = $settings->getSetting('activity.feedbgmax', 12); ?>
              <?php 
              $getFeaturedBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'activity')->getBackgrounds( array('admin' => 1, 'fetchAll' => 1, 'activityfeedbg_limit_show' => 5, 'featured' => 1) );
              $featured = $backgrounds = array();
              foreach($getFeaturedBackgrounds as $getFeaturedBackground) {
                $featured[] = $getFeaturedBackground->background_id;
              }
              // if featured images are available show in first then rest of images are come according to member level.
              // featured + member_level
              if(engine_count($featured) > 5) {
                $activityfeedbg_limit_show = 5;
              }
              $getBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'activity')->getBackgrounds( array('admin' => 1, 'fetchAll' => 1, 'activityfeedbg_limit_show' => $activityfeedbg_limit_show, 'featuredbgIds' => $featured)); 
              foreach($getBackgrounds as $getBackground) {
                $backgrounds[] = $getBackground->background_id;
              }
              if(engine_count($featured) > 0) {
                $backgrounds = array_merge($featured, $backgrounds);
              }
              ?>
              <?php if( engine_count( $backgrounds ) > 0 ) { ?>
                <div id="feedbg_main_continer" style="display:none;">
                  <a href="javascript:void(0);" id="hideshowfeedbgcont"><i onclick="hideshowfeedbgcont();" class="fa fa-angle-left"></i></a>
                  <ul id="feedbg_content">
                    <li>
                      <a class="feedbg_active" id="feedbg_image_defaultimage" href="javascript:void(0);" onclick="feedbgimage('defaultimage')"><img height="30px;" width="30px;" id="feed_bg_image_defaultimage" alt="" src="<?php echo 'application/modules/Activity/externals/images/white.png'; ?>" /></a>
                    </li>
                    <?php foreach($backgrounds as $getBackground) {
                      $getBackground = Engine_Api::_()->getItem('activity_background', $getBackground);
                    ?>
                      <?php if($getBackground->file_id) {
                        $photo = Engine_Api::_()->storage()->get($getBackground->file_id, '');
                        if($photo) {
                          $photo = $photo->getPhotoUrl(); ?>
                       <li>
                         <a id="feedbg_image_<?php echo $getBackground->background_id; ?>" href="javascript:void(0);" onclick="feedbgimage('<?php echo $getBackground->background_id; ?>', 'photo');setFocus();"><img height="30px;" width="30px;" id="feed_bg_image_<?php echo $getBackground->background_id; ?>" data-id="<?php echo $getBackground->background_id; ?>" alt="" src="<?php echo $photo; ?>" /></a>
                       </li>
                      <?php  }
                      }
                      ?>
                    <?php } ?>
  <!--                  <li class="_more">
                      <a href="#" class="activity_tooltip" title='<?php //echo $this->translate("More"); ?>'><i class="fa fa-th-large"></i></a>
                    </li>-->
                    <?php  ?>
                  </ul>
                  <input type="hidden" name="feedbgid" id="feedbgid" value="" class="resetaftersubmit">
                  <input type="hidden" name="feedbgid_isphoto" id="feedbgid_isphoto" value="1" class="resetaftersubmit">
                </div>
              <?php } ?>
        <div id="activity-menu" class="activity-menu activity_post_tools">
          <span class="activity-menu-selector" id="activity-menu-selector"></span>
          
        <?php if($this->design == 1) { ?>
          <?php if(engine_in_array('shedulepost',$this->composerOptions)){ ?>
              <script type="text/javascript"> var enabledShedulepost = 1; </script>
              <div class="activity_popup_overlay activity_shedulepost_overlay" style="display:none;"></div>
              <div class="activity_popup activity_shedulepost_select " style="display:none;">
                <div class="activity_popup_header"><?php echo $this->translate("Schedule Post"); ?></div>
                <div class="activity_popup_cont">
                  <b><?php echo $this->translate("Schedule Your Post"); ?></b>
                  <p><?php echo $this->translate("Select date and time on which you want to publish your post."); ?></p>
                  <div class="activity_time_input_wrapper">
                    <div id="datetimepicker" class="input-append date activity_time_input">
                      <input type="text" name="scheduled_post" id="scheduled_post" class="resetaftersubmit"></input>
                      <span class="add-on" title="Select Time"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                    </div>
                    <div class="activity_error activity_shedulepost_error"></div>
                  </div>
                </div>
                <div class="activity_popup_btns">
                 <button type="submit" class="schedule_post_schedue"><?php echo $this->translate("Schedule"); ?></button>
                 <button class="close schedule_post_close"><?php echo $this->translate("Cancel"); ?></button>
                </div>
              </div>
          <?php } ?>
          
          <?php if($this->isMemberHomePage && engine_in_array('tagUseActivity',$this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_tag">
              <a href="javascript:;" id="activity_tag" data-bs-toggle="tooltip"  class="activity_tooltip" title="<?php echo $this->translate('Tag People'); ?>">&nbsp;</a>
            </span>
          <?php } ?>
          <?php if($this->subject() && $this->viewer()->isSelf($this->subject()) && engine_in_array('tagUseActivity',$this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_tag">
              <a href="javascript:;" id="activity_tag" data-bs-toggle="tooltip"  class="activity_tooltip" title="<?php echo $this->translate('Tag People'); ?>">&nbsp;</a>
            </span>
          <?php } ?>
          
          <?php if($this->isMemberHomePage && engine_in_array('locationactivity',$this->composerOptions) && $settings->getSetting('enableglocation', 1)) { ?>
            <span class="activity_post_tool_i tool_i_location">
              <a href="javascript:;" id="activity_location" data-bs-toggle="tooltip" title="<?php echo $this->translate('Check In'); ?>" class="activity_tooltip">
                <i><svg x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g id="_01_align_center"><path d="M255.104,512.171l-14.871-12.747C219.732,482.258,40.725,327.661,40.725,214.577c0-118.398,95.981-214.379,214.379-214.379   s214.379,95.981,214.379,214.379c0,113.085-179.007,267.682-199.423,284.932L255.104,512.171z M255.104,46.553   c-92.753,0.105-167.918,75.27-168.023,168.023c0,71.042,110.132,184.53,168.023,236.473   c57.892-51.964,168.023-165.517,168.023-236.473C423.022,121.823,347.858,46.659,255.104,46.553z"></path><path d="M255.104,299.555c-46.932,0-84.978-38.046-84.978-84.978s38.046-84.978,84.978-84.978s84.978,38.046,84.978,84.978   S302.037,299.555,255.104,299.555z M255.104,172.087c-23.466,0-42.489,19.023-42.489,42.489s19.023,42.489,42.489,42.489   s42.489-19.023,42.489-42.489S278.571,172.087,255.104,172.087z"></path></g></svg></i>
              </a>
            </span>
          <?php } ?>
          <?php if($this->subject() && $this->viewer()->isSelf($this->subject()) && engine_in_array('locationactivity',$this->composerOptions) && $settings->getSetting('enableglocation', 1)) { ?>
            <span class="activity_post_tool_i tool_i_location">
              <a href="javascript:;" id="activity_location" data-bs-toggle="tooltip" title="<?php echo $this->translate('Check In'); ?>" class="activity_tooltip">
                <i><svg x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g id="_01_align_center"><path d="M255.104,512.171l-14.871-12.747C219.732,482.258,40.725,327.661,40.725,214.577c0-118.398,95.981-214.379,214.379-214.379   s214.379,95.981,214.379,214.379c0,113.085-179.007,267.682-199.423,284.932L255.104,512.171z M255.104,46.553   c-92.753,0.105-167.918,75.27-168.023,168.023c0,71.042,110.132,184.53,168.023,236.473   c57.892-51.964,168.023-165.517,168.023-236.473C423.022,121.823,347.858,46.659,255.104,46.553z"></path><path d="M255.104,299.555c-46.932,0-84.978-38.046-84.978-84.978s38.046-84.978,84.978-84.978s84.978,38.046,84.978,84.978   S302.037,299.555,255.104,299.555z M255.104,172.087c-23.466,0-42.489,19.023-42.489,42.489s19.023,42.489,42.489,42.489   s42.489-19.023,42.489-42.489S278.571,172.087,255.104,172.087z"></path></g></svg></i>
              </a>
            </span>
          <?php } ?>
          
          <?php if(engine_in_array('stickers',$this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_sticker">
              <a href="javascript:;" class="activity_tooltip emoji_comment_select activity_emoji_content_a" data-bs-toggle="tooltip" title="<?php echo $this->translate('Stickers'); ?>">
                <i><svg viewBox="0 0 24 24"><path d="m23.967 10.417a12.04 12.04 0 1 0 -13.55 13.55 3.812 3.812 0 0 0 .489.032 3.993 3.993 0 0 0 2.805-1.184l9.1-9.1a3.962 3.962 0 0 0 1.156-3.298zm-21.9.474a10.034 10.034 0 0 1 19.8-.884 12.006 12.006 0 0 0 -11.86 11.852 9.988 9.988 0 0 1 -7.944-10.968zm10.233 10.509a2.121 2.121 0 0 1 -.278.225 10 10 0 0 1 9.606-9.607 2.043 2.043 0 0 1 -.224.279z"></path></svg></i>
              </a>
            </span>
          <?php } ?>
          
          <?php if(engine_count($getFeelings) > 0 && engine_in_array('feelingssctivity',$this->composerOptions)): ?>
            <span class="activity_post_tool_i tool_i_feelings" id="activity_feelings">
              <a href="javascript:;" id="activity_feelingsa" class="activity_tooltip" data-bs-toggle="tooltip" title="<?php echo $this->translate('Feeling/Activity'); ?>"><i><svg viewBox="0 0 24 24"><path d="M6,12c-.553,0-1-.448-1-1,0-1.892,1.232-4,3-4s3,2.108,3,4c0,.552-.447,1-1,1s-1-.448-1-1c0-1.054-.68-2-1-2s-1,.946-1,2c0,.552-.447,1-1,1Zm7-1c0,.552,.447,1,1,1s1-.448,1-1c0-1.054,.68-2,1-2s1,.946,1,2c0,.552,.447,1,1,1s1-.448,1-1c0-1.892-1.232-4-3-4s-3,2.108-3,4Zm-1,7c3.107,0,5.563-2.162,5.666-2.254,.411-.367,.446-.997,.08-1.409-.367-.412-.998-.449-1.41-.084-.02,.018-2.005,1.748-4.336,1.748s-4.311-1.726-4.336-1.748c-.412-.365-1.041-.33-1.41,.081-.367,.412-.332,1.044,.08,1.412,.103,.092,2.559,2.254,5.666,2.254Zm7.957-11.998c.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077ZM5.25,17c-.966,0-1.75,.862-1.75,1.925,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077,.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925ZM22.313,8.038c-.531,.15-.84,.703-.689,1.234,.249,.884,.376,1.801,.376,2.728,0,5.514-4.486,10-10,10-.927,0-1.845-.126-2.728-.376-.535-.149-1.085,.158-1.234,.69-.15,.531,.158,1.084,.689,1.234,1.061,.3,2.161,.452,3.272,.452,6.617,0,12-5.383,12-12,0-1.11-.152-2.211-.452-3.272-.15-.532-.701-.838-1.234-.69ZM1.181,15c.06,0,.121-.005,.182-.017,.543-.1,.902-.621,.803-1.164-.109-.597-.165-1.208-.165-1.819C2,6.486,6.486,2,12,2c.612,0,1.225,.055,1.819,.165,.554,.101,1.064-.26,1.164-.803s-.26-1.064-.803-1.164c-.714-.131-1.447-.198-2.181-.198C5.383,0,0,5.383,0,12c0,.731,.066,1.465,.198,2.181,.089,.482,.509,.819,.982,.819Z"></path></svg></i></a>
            </span>
          <?php endif; ?>
           
          <?php if($settings->getSetting('activity.giphyapi', '') && engine_in_array('activityfeedgif',$this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_gif">
              <a href="javascript:;" class="activity_tooltip gif_comment_select activity_gif_content_a" data-bs-toggle="tooltip" title="<?php echo $this->translate('GIF'); ?>">
                <i><svg viewBox="0 0 24 24"><path d="m19,2H5C2.243,2,0,4.243,0,7v10c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5V7c0-2.757-2.243-5-5-5Zm3,15c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3V7c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v10Zm-9-9v8c0,.552-.447,1-1,1s-1-.448-1-1v-8c0-.552.447-1,1-1s1,.448,1,1Zm7,0c0,.552-.447,1-1,1h-2c-.552,0-1,.449-1,1v1h2c.553,0,1,.448,1,1s-.447,1-1,1h-2v3c0,.552-.447,1-1,1s-1-.448-1-1v-6c0-1.654,1.346-3,3-3h2c.553,0,1,.448,1,1Zm-14,2v4c0,.551.448,1,1,1s1-.449,1-1c-.553,0-1-.448-1-1s.447-1,1-1c1.299,0,2,1.03,2,2,0,1.654-1.346,3-3,3s-3-1.346-3-3v-4c0-1.654,1.346-3,3-3s3,1.346,3,3c0,.552-.447,1-1,1s-1-.448-1-1-.448-1-1-1-1,.449-1,1Z"></path></svg></i>
              </a>
            </span>
            <input type="hidden" name="image_id" class="resetaftersubmit" id="image_id" value="" />
          <?php } ?>
        <?php } ?>

        <?php if(engine_in_array('smilesActivity',$this->composerOptions) && engine_count($getEmojis) > 0) { ?>
          <span class="activity_post_tool_i tool_i_emoji feeling_emoji_comment_select" id="activity_feeling_emojis">
            <a href="javascript:;" id="activity_feeling_emojisa" title="<?php echo $this->translate('Emojis'); ?>" data-bs-toggle="tooltip">&nbsp;</a>
          </span>
        <?php } ?>
        
				<?php //if(engine_in_array('smilesActivity',$this->composerOptions) && 0): ?>
          <!-- <span class="activity_post_tool_i tool_i_emoji">
            <a href="javascript:;" id="activityemoji-statusbox" class="activity_tooltip" data-bs-toggle="tooltip" title="<?php //echo $this->translate('Emoticons'); ?>"></a>
            <div id="activityemoji_statusbox" class="comment_emotion_container">
              <div class="comment_emotion_container_inner clearfix">
                <div class="comment_emotion_holder">
                  <div class="loading_container" style="height:100%;"></div>
                </div>
              </div>
            </div>
          </span> -->
        <?php //endif; ?>
        </div>
      </div>
      
      <div id="composer-tray-container"></div>
      <div class="activity_post_tag_container clearfix activity_post_tag_cnt" style="display:none;">
        <span class="tag">With</span>
        <div class="activity_post_tags_holder">
          <div id="toValues-element">
          </div>
        	<div class="activity_post_tag_input">
          	<input type="text" class="resetaftersubmit" placeholder="<?php echo $this->translate('Who are you with?'); ?>" id="tag_friends_input" />
            <div id="toValues-wrapper" style="display:none">
            <input type="hidden" id="toValues" name="tag_friends" class="resetaftersubmit">
            </div>
          </div>
          <a href="javascript:;" class="cancelTagLink"><i class="fa fa-times"></i></a>
        </div>	
      </div>
      <div class="activity_post_tag_container clearfix activity_post_location_container" style="display:none;">
        <span class="tag">At</span>
        <div class="activity_post_tags_holder">
          <div id="locValues-element"></div>
        	<div class="activity_post_tag_input" id="activity_post_tag_input">
          	<input type="text" placeholder="<?php echo $this->translate('Where are you?'); ?>" name="tag_location" id="tag_location" class="resetaftersubmit"/>
            <input type="hidden" name="activitylng" id="activitylng" value="" class="resetaftersubmit">
            <input type="hidden" name="activitylat" id="activitylat" value="" class="resetaftersubmit">
          </div>
          <a href="javascript:;" class="cancelLink"><i class="fa fa-times"></i></a>
        </div>	
      </div>
      <div id="activity_page_tags"></div>
       <div id="activity_business_tags"></div>
        <div id="activity_group_tags"></div>
      <?php // Feeling work ?>
      <?php if(engine_in_array('activity',$enabledModuleNames)) { ?>
        <div id="activity_post_feeling_container" class="activity_post_tag_container clearfix activity_post_feeling_container" style="display:none;">
          <span id="feelingActType" class="tag" style="display:none;"></span>
          <div class="activity_post_tags_holder">
            <div id="feelingValues-element"></div>
            <div class="activity_post_tag_input">
              <input autofocus autocomplete="off" type="text" placeholder="<?php echo $this->translate('Choose Feeling or activity...'); ?>" name="feeling_activity" id="feeling_activity" class="resetaftersubmit"/>
              
              <a onclick="feelingactivityremoveact();" href="javascript:void(0);" class="feeling_activity_remove_act notclose" id="feeling_activity_remove_act" title="<?php echo $this->translate('Remove'); ?>"><i class="fa fa-times"></i></a>
              
              <input type="hidden" name="feelingactivityid" id="feelingactivityid" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivityiconid" id="feelingactivityiconid" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_resource_type" id="feelingactivity_resource_type" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_custom" id="feelingactivity_custom" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_customtext" id="feelingactivity_customtext" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_type" id="feelingactivity_type" value="" class="resetaftersubmit">
            </div>
          </div>
          
          <div class="activity_post_feelingautocompleter_container activity_post_feelings_autosuggest" style="display:none;">
          	<div class="clearfix custom_scrollbar">
              <ul class="activityfeelingactivity-ul" id="showSearchResults"></ul>
            </div>	
          </div>
          
          <div class="activity_post_feelingcontent_container activity_post_feelings_autosuggest" style="display:none;">
          	<div class="clearfix custom_scrollbar">
              <ul id="all_feelings">
                <?php $feelings = Engine_Api::_()->getDbTable('feelings', 'activity')->getFeelings(array('fetchAll' => 1, 'admin' => 0));  ?>
                <?php foreach($feelings as $feeling): ?>
                  <?php $photo = Engine_Api::_()->storage()->get($feeling->file_id, '');
                      if($photo) {
                      $photo = $photo->getPhotoUrl(); ?>
                  <li data-title="<?php echo $feeling->title; ?>" class="activity_feelingactivitytype clearfix" data-rel="<?php echo $feeling->feeling_id; ?>" data-type="<?php echo $feeling->type; ?>">
                    <a href="javascript:void(0);" class="activity_feelingactivitytypea">
                      <img id="activityfeelingactivitytypeimg_<?php echo $feeling->feeling_id; ?>" title="<?php echo $feeling->title ?>" src="<?php echo $photo; ?>">
                      <span><?php echo $this->translate($feeling->title); ?></span>
                    </a>
                  </li>
                  <?php } ?>
                <?php endforeach; ?>
              </ul>
            </div>  
          </div>	
        </div>
      <?php } ?>
      <?php // Feeling work ?>
      
      <?php if($this->design == 2) { ?>
        <div class="activity_post_media_options clearfix">
          <div id="activity_post_media_options_before" class="d-none"></div>
            <?php if(engine_in_array('shedulepost',$this->composerOptions)) { ?>
              <script type="text/javascript"> var enabledShedulepost = 1; </script>
              <div class="activity_popup_overlay activity_shedulepost_overlay" style="display:none;"></div>
              <div class="activity_popup activity_shedulepost_select " style="display:none;">
                <div class="activity_popup_header"><?php echo $this->translate('Schedule Post'); ?></div>
                <div class="activity_popup_cont">
                  <b><?php echo $this->translate("Schedule Your Post"); ?></b>
                  <p><?php echo $this->translate("Select date and time on which you want to publish your post."); ?></p>
                  <div class="activity_time_input_wrapper">
                    <div id="datetimepicker" class="input-append date activity_time_input">
                      <input type="text" name="scheduled_post" id="scheduled_post" class="resetaftersubmit"></input>
                      <span class="add-on activity_tooltip" title="View Calendar"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                    </div>
                    <div class="activity_error activity_shedulepost_error"></div>
                  </div>
                </div>
                <div class="activity_popup_btns">
                 <button type="submit" class="schedule_post_schedue"><?php echo $this->translate('Schedule'); ?></button>
                 <button class="close schedule_post_close"><?php echo $this->translate('Cancel'); ?></button>
                </div>
              </div>
            <?php } ?>
            <?php if($this->isMemberHomePage && engine_in_array('tagUseActivity',$this->composerOptions)){ ?>
              <span class="activity_post_media_options_icon tool_i_tag">
                <a href="javascript:;" id="activity_tag" class="activity_tooltip" title="<?php echo $this->translate('Tag People'); ?>"><span><?php echo $this->translate('Tag People'); ?></span></a>
              </span>
            <?php } ?>
            <?php if($this->subject() && $this->viewer()->isSelf($this->subject()) && engine_in_array('tagUseActivity',$this->composerOptions)){ ?>
              <span class="activity_post_media_options_icon tool_i_tag">
                <a href="javascript:;" id="activity_tag" class="activity_tooltip" title="<?php echo $this->translate('Tag People'); ?>"><span><?php echo $this->translate('Tag People'); ?></span></a>
              </span>
            <?php } ?>
            <?php if($this->isMemberHomePage && $this->isGoogleApiKeySaved && $settings->getSetting('enableglocation', '1') == 1  && engine_in_array('locationactivity',$this->composerOptions)) { ?>
              <span class="activity_post_media_options_icon tool_i_location">
                <a href="javascript:;" id="activity_location" title="Check In" class="activity_tooltip">
                  <i><svg x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g id="_01_align_center"><path d="M255.104,512.171l-14.871-12.747C219.732,482.258,40.725,327.661,40.725,214.577c0-118.398,95.981-214.379,214.379-214.379   s214.379,95.981,214.379,214.379c0,113.085-179.007,267.682-199.423,284.932L255.104,512.171z M255.104,46.553   c-92.753,0.105-167.918,75.27-168.023,168.023c0,71.042,110.132,184.53,168.023,236.473   c57.892-51.964,168.023-165.517,168.023-236.473C423.022,121.823,347.858,46.659,255.104,46.553z"/><path d="M255.104,299.555c-46.932,0-84.978-38.046-84.978-84.978s38.046-84.978,84.978-84.978s84.978,38.046,84.978,84.978   S302.037,299.555,255.104,299.555z M255.104,172.087c-23.466,0-42.489,19.023-42.489,42.489s19.023,42.489,42.489,42.489   s42.489-19.023,42.489-42.489S278.571,172.087,255.104,172.087z"/></g></svg></i>  
                  <span><?php echo $this->translate('Check In'); ?></span>
                </a>
              </span>
            <?php } ?>
            <?php if($this->subject() && $this->viewer()->isSelf($this->subject()) &&  $this->isGoogleApiKeySaved && $settings->getSetting('enableglocation', '1') == 1  && engine_in_array('locationactivity',$this->composerOptions)) { ?>
              <span class="activity_post_media_options_icon tool_i_location">
                <a href="javascript:;" id="activity_location" title="Check In" class="activity_tooltip">
                  <i><svg x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g id="_01_align_center"><path d="M255.104,512.171l-14.871-12.747C219.732,482.258,40.725,327.661,40.725,214.577c0-118.398,95.981-214.379,214.379-214.379   s214.379,95.981,214.379,214.379c0,113.085-179.007,267.682-199.423,284.932L255.104,512.171z M255.104,46.553   c-92.753,0.105-167.918,75.27-168.023,168.023c0,71.042,110.132,184.53,168.023,236.473   c57.892-51.964,168.023-165.517,168.023-236.473C423.022,121.823,347.858,46.659,255.104,46.553z"/><path d="M255.104,299.555c-46.932,0-84.978-38.046-84.978-84.978s38.046-84.978,84.978-84.978s84.978,38.046,84.978,84.978   S302.037,299.555,255.104,299.555z M255.104,172.087c-23.466,0-42.489,19.023-42.489,42.489s19.023,42.489,42.489,42.489   s42.489-19.023,42.489-42.489S278.571,172.087,255.104,172.087z"/></g></svg></i>  
                  <span><?php echo $this->translate('Check In'); ?></span>
                </a>
              </span>
            <?php } ?>
            <?php if(engine_in_array('stickers',$this->composerOptions)) { ?>
              <span class="activity_post_media_options_icon tool_i_sticker">
                <a href="javascript:;" class="activity_tooltip emoji_comment_select activity_emoji_content_a" title="<?php echo $this->translate('Stickers'); ?>">
                  <i><svg viewBox="0 0 24 24"><path d="m23.967 10.417a12.04 12.04 0 1 0 -13.55 13.55 3.812 3.812 0 0 0 .489.032 3.993 3.993 0 0 0 2.805-1.184l9.1-9.1a3.962 3.962 0 0 0 1.156-3.298zm-21.9.474a10.034 10.034 0 0 1 19.8-.884 12.006 12.006 0 0 0 -11.86 11.852 9.988 9.988 0 0 1 -7.944-10.968zm10.233 10.509a2.121 2.121 0 0 1 -.278.225 10 10 0 0 1 9.606-9.607 2.043 2.043 0 0 1 -.224.279z"></path></svg></i>
                  <span class="emoji_comment_select"><?php echo $this->translate('Stickers'); ?></span>
                </a>
              </span>
            <?php } ?>
          
            <?php //Feeling Work ?>
            <?php if(engine_count($getFeelings) > 0 && engine_in_array('feelingssctivity',$this->composerOptions)): ?>
              <span class="activity_post_media_options_icon tool_i_feelings"  id="activity_feelings">
                <a id="activity_feelingsa" href="javascript:;"  class="activity_tooltip" title="<?php echo $this->translate('Feeling/Activity'); ?>">
                  <i><svg data-name="Layer 1" viewBox="0 0 24 24"><path d="M6,12c-.553,0-1-.448-1-1,0-1.892,1.232-4,3-4s3,2.108,3,4c0,.552-.447,1-1,1s-1-.448-1-1c0-1.054-.68-2-1-2s-1,.946-1,2c0,.552-.447,1-1,1Zm7-1c0,.552,.447,1,1,1s1-.448,1-1c0-1.054,.68-2,1-2s1,.946,1,2c0,.552,.447,1,1,1s1-.448,1-1c0-1.892-1.232-4-3-4s-3,2.108-3,4Zm-1,7c3.107,0,5.563-2.162,5.666-2.254,.411-.367,.446-.997,.08-1.409-.367-.412-.998-.449-1.41-.084-.02,.018-2.005,1.748-4.336,1.748s-4.311-1.726-4.336-1.748c-.412-.365-1.041-.33-1.41,.081-.367,.412-.332,1.044,.08,1.412,.103,.092,2.559,2.254,5.666,2.254Zm7.957-11.998c.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077ZM5.25,17c-.966,0-1.75,.862-1.75,1.925,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077,.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925ZM22.313,8.038c-.531,.15-.84,.703-.689,1.234,.249,.884,.376,1.801,.376,2.728,0,5.514-4.486,10-10,10-.927,0-1.845-.126-2.728-.376-.535-.149-1.085,.158-1.234,.69-.15,.531,.158,1.084,.689,1.234,1.061,.3,2.161,.452,3.272,.452,6.617,0,12-5.383,12-12,0-1.11-.152-2.211-.452-3.272-.15-.532-.701-.838-1.234-.69ZM1.181,15c.06,0,.121-.005,.182-.017,.543-.1,.902-.621,.803-1.164-.109-.597-.165-1.208-.165-1.819C2,6.486,6.486,2,12,2c.612,0,1.225,.055,1.819,.165,.554,.101,1.064-.26,1.164-.803s-.26-1.064-.803-1.164c-.714-.131-1.447-.198-2.181-.198C5.383,0,0,5.383,0,12c0,.731,.066,1.465,.198,2.181,.089,.482,.509,.819,.982,.819Z"/></svg></i>
                  <span class="activity_feelingsspan"><?php echo $this->translate('Feeling/Activity'); ?></span>
                </a>
              </span>
            <?php endif; ?>
            <?php if($settings->getSetting('activity.giphyapi', '') && engine_in_array('activityfeedgif',$this->composerOptions)) { ?>
              <span class="activity_post_media_options_icon tool_i_gif" >
                <a href="javascript:;" class="activity_tooltip gif_comment_select activity_gif_content_a" title="<?php echo $this->translate('GIF'); ?>">
                  <i><svg viewBox="0 0 24 24"><path d="m19,2H5C2.243,2,0,4.243,0,7v10c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5V7c0-2.757-2.243-5-5-5Zm3,15c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3V7c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v10Zm-9-9v8c0,.552-.447,1-1,1s-1-.448-1-1v-8c0-.552.447-1,1-1s1,.448,1,1Zm7,0c0,.552-.447,1-1,1h-2c-.552,0-1,.449-1,1v1h2c.553,0,1,.448,1,1s-.447,1-1,1h-2v3c0,.552-.447,1-1,1s-1-.448-1-1v-6c0-1.654,1.346-3,3-3h2c.553,0,1,.448,1,1Zm-14,2v4c0,.551.448,1,1,1s1-.449,1-1c-.553,0-1-.448-1-1s.447-1,1-1c1.299,0,2,1.03,2,2,0,1.654-1.346,3-3,3s-3-1.346-3-3v-4c0-1.654,1.346-3,3-3s3,1.346,3,3c0,.552-.447,1-1,1s-1-.448-1-1-.448-1-1-1-1,.449-1,1Z"></path></svg></i>
                  <span class="gif_comment_select"><?php echo $this->translate('GIF'); ?></span>
                </a>
                <input type="hidden" name="image_id" class="resetaftersubmit" id="image_id" value="" />
              </span>
            <?php } ?>
          </div>
        <?php } ?>
       <?php $privacyFeed = $settings->getSetting('activity.view.privacy'); ?>
       <?php $privacyFeedHold = $settings->getSetting($this->viewer()->getIdentity().".activity.user.setting"); ?>
      <div id="compose-menu" class="activity_compose_menu">
        <input type="hidden" name="privacy" id="privacy"  value="<?php echo !empty($privacyFeedHold) ? $privacyFeedHold : $privacyFeed[0] ; ?>">
        <div class="activity_compose_menu_btns notclose">
        	<div class="activity_chooser activity_content_pulldown_wrapper" style="display:none;">
          	<a href="javascript:void(0);" class="activity_privacy_btn activity_chooser_btn"><i class="_icon fa fa-users font_color_light"></i><span><?php echo $this->translate('Select Pages'); ?></span><i class="_arrow fa-solid fa-angle-down"></i></a>
            <div class="activity_content_pulldown" style="display:none;">
            	<ul class="activity_content_pulldown_list">
              </ul>
            </div>
          </div>
          
          <?php if($this->allowprivacysetting){ ?>
            <div class="activity_privacy_chooser activity_chooser dropdown">
              <a href="javascript:void(0);" class="activity_privacy_btn activity_chooser_btn btn btn-alt" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i id="activity_privacy_icon"></i><span id="adv_pri_option"></span><i class="_arrow fa-solid fa-angle-down"></i></a>
              <ul class="adv_privacy_optn dropdown-menu dropdown-option-menu dropdown-menu-end">
                <?php if(engine_in_array('everyone',$privacyFeed)){ ?>
                  <li data-src="everyone" class=""><a href="javascript:;" class="dropdown-item"><i class="icon_activity_public"></i><span><?php echo $this->translate('Everyone'); ?></span></a></li>
                <?php } ?>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && engine_in_array('networks',$privacyFeed)){ ?>
                  <li data-src="networks"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $this->translate('Friends or Networks'); ?></span></a></li>

                <?php } ?>
                <?php if(engine_in_array('friends',$privacyFeed)){ ?>
                  <li data-src="friends"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_friends"></i><span><?php echo $this->translate('Friends Only'); ?></span></a></li>
                <?php } ?>
                <?php if(engine_in_array('onlyme',$privacyFeed)){ ?>
                  <li data-src="onlyme"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_me"></i><span><?php echo $this->translate('Only Me'); ?></span></a></li>
                <?php } ?>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && $this->allownetworkprivacy){ ?>
                <?php if(engine_count($this->usernetworks)){ ?>
                <li class="dropdown-divider"></li>
                <?php foreach($this->usernetworks as $usernetworks){ ?>
                  <li data-src="network_list" class="network activity_network" data-rel="<?php echo $usernetworks->getIdentity(); ?>"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $this->translate($usernetworks->getTitle()); ?></span></a></li>
                <?php }
                if(engine_count($this->usernetworks) > 1){
                  ?>
                  <li class="multiple mutiselect" data-rel="network-multi"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $this->translate('Multiple Networks'); ?></span></a></li>
                <?php 
                  }
                } ?>
                <?php } ?>
                <?php if($this->allowlistprivacy){ ?>
                <?php if(engine_count($this->userlists)){ ?>
                <li class="dropdown-divider"></li>
                <?php foreach($this->userlists as $userlists){ ?>
                  <li data-src="members_list" class="lists activity_list" data-rel="<?php echo $userlists->getIdentity(); ?>"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_lists"></i><span><?php echo $this->translate($userlists->getTitle()); ?></span></a></li>
                <?php } 
                  if(engine_count($this->userlists) > 1){
                ?>
                  <li class="multiple mutiselect" data-rel="lists-multi"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_lists"></i><span><?php echo $this->translate('Multiptle Lists'); ?></span></a></li>
                <?php 
                  }
                } ?>
                <?php } ?>
              </ul>
            </div>
          <?php } ?>
        	<button id="compose-submit" type="submit" class="btn btn-primary"><?php echo $this->translate("Share") ?></button>
        </div>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000)){ ?>
          <div class="compose-content-counter" style="display: inline-block;"><?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000);?></div>
        <?php } ?>
        <span class="composer_crosspost_toggle activity_tooltip" href="javascript:void(0);" title="<?php echo $this->translate('Crosspost');?>" style="display:none;"></span>
      </div>
      <div class="activity_post_loader d-none">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <span><?php echo $this->translate("Posting");?>
      </div>
  	</form>
  <?php //if($this->design == 2){ ?>
    <div class="activity_popup_overlay activity_confirmation_popup_overlay" style="display:none;"></div>
    <div class="activity_popup activity_confirmation_popup " style="display:none;">
      <div class="activity_popup_header"><?php echo $this->translate("Finish Your Post?"); ?></div>
      <div class="activity_popup_cont"><?php echo $this->translate("If you leave now, your post won't be saved."); ?></div>
      <div class="activity_popup_btns">
        <button id="discard_post"><?php echo $this->translate("Discard Post"); ?></button>
        <button id="goto_post"><?php echo $this->translate("Go to Post"); ?></button>
      </div>
    </div>
  <?php //} ?>
    <?php foreach( $this->composePartials as $partial ): ?>
      <?php echo $this->partial($partial[0], $partial[1], array('isMemberHomePage' => $this->isMemberHomePage)) ?>
    <?php endforeach; ?>
    
  </div>
  </div>
<?php endif; ?>
<script type="text/javascript">
    AttachEventListerSE('click',':not(#activityemoji-statusbox)',function(){
        if(scriptJquery("#activityemoji-statusbox")){
          if(scriptJquery("#activityemoji-statusbox").hasClass('active')){
            scriptJquery("#activityemoji-statusbox").removeClass('active');
            scriptJquery("#activityemoji_statusbox").hide();
          }
        }
      });
      AttachEventListerSE('click','#activity_tag, .cancelTagLink, .activitytag_clk',function(e){
         scriptJquery('.activity_post_tag_cnt').toggle();
         scriptJquery(this).toggleClass('active');

        if(scriptJquery(this).hasClass('cancelTagLink')){
          scriptJquery('#activity_tag').removeClass('active');
         scriptJquery('#activity_location').removeClass('active');
        }

      });
      AttachEventListerSE('click','#activity_location, .cancelLink, .seloc_clk',function(e){
        that = scriptJquery(this);
        if(scriptJquery(this).hasClass('cancelLink')){
         scriptJquery('#activity_location').removeClass('active');
         scriptJquery('.activity_post_location_container').hide();
           return;
        }
        if(scriptJquery(this).hasClass('.seloc_clk'))
           that = scriptJquery('#activity_location');
         if(scriptJquery(this).hasClass('active')){
           scriptJquery(this).removeClass('active');
           scriptJquery('.activity_post_location_container').hide();
           return;
         }
        

         scriptJquery('.activity_post_location_container').show();
         scriptJquery(this).addClass('active');
      });
      
        
        function hideshowfeedbgcont() {

          if(!scriptJquery('#feedbg_content').hasClass('activity_feedbg_small_content')) {
            // document.getElementById('feedbg_main_continer').style.display = 'none';
            scriptJquery('#feedbg_content').addClass('activity_feedbg_small_content');
            scriptJquery('#hideshowfeedbgcont').html('<i onclick="hideshowfeedbgcont();" class="fa fa-angle-right right_img"></i>');
          } else {
            //document.getElementById('feedbg_content').style.display = 'block';
            // document.getElementById('feedbg_main_continer').style.display = 'block';
            scriptJquery('#feedbg_content').removeClass('activity_feedbg_small_content');
            scriptJquery('#hideshowfeedbgcont').html('<i onclick="hideshowfeedbgcont();" class="fa fa-angle-left"></i>');
          }
        }
        
        function feedbgimage(feedbgid, type, fromClose = '') {
          
          var feedbgidval = scriptJquery('#feedbgid').val();
          if(feedbgid == 'defaultimage') {
            scriptJquery('#activity-form').removeClass('feed_background_image');
            scriptJquery('.activity_post_box').css("background-image","");
            scriptJquery('#feedbgid').val(0);
            scriptJquery('#feedbgid_isphoto').val(0);
            scriptJquery("#feedbg_main_continer > ul > li > a").removeClass('feedbg_active');
            scriptJquery('#feedbg_image_'+feedbgid).addClass('feedbg_active');
            scriptJquery('#activity_body').focus();
            autosize(scriptJquery('#activity_body').removeAttr("class").removeAttr("data-autosize-on"));
          } else {
            if(feedbgidval)
              scriptJquery('#feedbg_image_'+feedbgidval).removeClass('feedbg_active');
            else
              scriptJquery('#feedbg_image_defaultimage').removeClass('feedbg_active');
              
            if(type == 'photo') {
              var imgSource = scriptJquery('#feed_bg_image_'+feedbgid).attr('src');
            } else if(type == 'video') {
              var imgSource = scriptJquery('#feed_bg_image_'+feedbgid).attr('data-src');
              
            }
            scriptJquery('#activity-form').addClass('feed_background_image');
            if(type == 'photo') {
              scriptJquery('#activity_videoid').remove();
              scriptJquery('.activity_post_box').css("background-image","url("+ imgSource +")");
            }
            scriptJquery('#feedbgid').val(feedbgid);
            scriptJquery('#feedbg_image_'+feedbgid).addClass('feedbg_active');
            scriptJquery('#feedbgid_isphoto').val(1);
            autosize(scriptJquery('#activity_body').removeAttr("class").removeAttr("data-autosize-on"));
          }
        }
      
      //Feeling Work
      <?php if(engine_in_array('activity',$enabledModuleNames)) { ?> 
          AttachEventListerSE('click','#activity_feelings',function(e) {
            that = scriptJquery(this);
            if(scriptJquery(this).hasClass('.seloc_clk'))
              that = scriptJquery('#activity_feelings');
            if(scriptJquery(this).hasClass('active')) {
              scriptJquery(this).removeClass('active');
              scriptJquery('.activity_post_feeling_container').hide();
              scriptJquery('.activity_post_feelingcontent_container').hide();
                return;
            }
            scriptJquery(this).addClass('active');
            scriptJquery('.activity_post_feeling_container').show();
            if(scriptJquery('#feelingactivityid').val() == '') {
              scriptJquery('.activity_post_feelingcontent_container').show();
            }
          });

          scriptJquery(document).click(function(e) {
            
            if((document.getElementById('activity_feelings') && !document.getElementById('activity_feelings').contains(e.target)) && (document.getElementById('activity_post_feeling_container') && !document.getElementById('activity_post_feeling_container').contains(e.target)) && scriptJquery(e.target).attr('id') != 'showFeelingContanier') {

              if(scriptJquery('#activity_post_feeling_container').css('display') == 'flex') {
                scriptJquery('.activity_post_feeling_container').hide();
                scriptJquery('.activity_post_feelingcontent_container').hide();
                scriptJquery('#feelingActType').html('');
                scriptJquery('#feelingActType').hide();
                scriptJquery('#feeling_activity').attr("placeholder", en4.core.language.translate("Choose Feeling or activity..."));
                scriptJquery('.activityfeelingactivity-ul').html('');
                if(scriptJquery('#activity_feelings').hasClass('active'))
                  scriptJquery('#activity_feelings').removeClass('active');
                if(scriptJquery('#feelingactivityid').val())
                  document.getElementById('feelingactivityid').value = '';
                
              } 
            } else if(scriptJquery(e.target).attr('id') == 'feelingActType') {
              scriptJquery('#feelingActType').html('');
              scriptJquery('#feelingActType').hide();
              scriptJquery('#feeling_activity').attr("placeholder", en4.core.language.translate("Choose Feeling or activity..."));
              scriptJquery('.activityfeelingactivity-ul').html('');
              if(scriptJquery('#feelingactivityid').val())
                document.getElementById('feelingactivityid').value = '';
              if(scriptJquery('#feeling_activity').val())
                document.getElementById('feeling_activity').value = '';
              if(scriptJquery('#feelingactivityiconid').val())
                document.getElementById('feelingactivityiconid').value = '';
              scriptJquery('.activity_post_feelingcontent_container').show();
              scriptJquery('#feeling_elem_act').html('');
            }
          });
          
          AttachEventListerSE('click', '.activity_feelingactivitytype', function(e){
      
            var feelingsactivity = scriptJquery(this);
            var feelingId = scriptJquery(this).attr('data-rel');
            var feelingType = scriptJquery(this).attr('data-type');
            var feelingTitle = scriptJquery(this).attr('data-title');
            scriptJquery('#feelingActType').show();
            scriptJquery('#feelingActType').html(feelingTitle);
            scriptJquery('#feeling_activity').attr("placeholder", en4.core.language.translate("How are you feeling?"));
            scriptJquery('#feeling_activity').trigger('focus');
            document.getElementById('feelingactivityid').value = feelingId;
            document.getElementById('feelingactivity_type').value = feelingType;
            scriptJquery('.activity_post_feelingcontent_container').hide();
            
            //Autocomplete Feeling trigger
            scriptJquery('#feeling_activity').trigger('change').trigger('keyup').trigger('keydown');
            
            //Feed Background Image Work
            if(document.getElementById('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
              document.getElementById('hideshowfeedbgcont').style.display = 'none';
              scriptJquery('#feedbgid_isphoto').val(0);
              scriptJquery('.activity_post_box').css('background-image', 'none');
              scriptJquery('#activity-form').removeClass('feed_background_image');
              scriptJquery('#feedbg_main_continer').css('display','none');
            }
          });
          
          
          //Autosuggest Feeling Work
          en4.core.runonce.add(function() {
            scriptJquery("#feeling_activity").keyup(function() {
              var search_string = scriptJquery("#feeling_activity").val();
              if(search_string == '') {
                search_string = 'default';
              }

              var autocompleteFeeling;
              postdata = {
                'text' : search_string, 
                'feeling_id': document.getElementById('feelingactivityid').value,
                'feeling_type': document.getElementById('feelingactivity_type').value,
              }
              
              if (autocompleteFeeling) {
                autocompleteFeeling.abort();
              }
              
              autocompleteFeeling = scriptJquery.post("<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'getfeelingicons'), 'default', true) ?>",postdata,function(data) {
                var parseJson = JSON.parse( data );
                if(parseJson.status == 1 && parseJson.html) {
                  scriptJquery('.activity_post_feelingautocompleter_container').show();
                  scriptJquery("#showSearchResults").html(parseJson.html);
                } else {
                
                  if(scriptJquery('#feeling_activity').val()) {
                    scriptJquery('.activity_post_feelingautocompleter_container').show();

                    var html = '<li data-title="'+scriptJquery('#feeling_activity').val()+'" class="activity_feelingactivitytypeli clearfix" data-rel=""><a href="javascript:void(0);" class="activity_feelingactivitytypea"><img class="feeling_icon" title="'+scriptJquery('#feeling_activity').val()+'" src="'+scriptJquery('#activityfeelingactivitytypeimg_'+scriptJquery('#feelingactivityid').val()).attr('src')+'"><span>'+scriptJquery('#feeling_activity').val()+'</span></a></li>';
                    scriptJquery("#showSearchResults").html(html);
                  } else {
                    scriptJquery('.activity_post_feelingautocompleter_container').show();
                    scriptJquery("#showSearchResults").html(html);
                  }
                }
              });
            });
          });

          AttachEventListerSE('click', '.activity_feelingactivitytypeli', function(e) {

            document.getElementById('feelingactivityiconid').value = scriptJquery(this).attr('data-rel');
            document.getElementById('feelingactivity_resource_type').value = scriptJquery(this).attr('data-type');
            
            if(!scriptJquery(this).attr('data-rel')) {
              document.getElementById('feelingactivity_custom').value = 1;
              document.getElementById('feelingactivity_customtext').value = scriptJquery('#feeling_activity').val();
            }

            if(scriptJquery(this).attr('data-icon')) {
              var finalFeeling = '-- ' + '<img class="feeling_icon" title="'+scriptJquery(this).attr('data-title').toLowerCase()+'" src="'+scriptJquery(this).attr('data-icon')+'"><span>' + ' ' +  scriptJquery('#feelingActType').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanier" class="" onclick="showFeelingContanier()">'+scriptJquery(this).attr('data-title').toLowerCase()+'</a>';
            } else {
              var finalFeeling = '-- ' + '<img class="feeling_icon" title="'+scriptJquery(this).attr('data-title').toLowerCase()+'" src="'+scriptJquery(this).find('a').find('img').attr('src')+'"><span>' + ' ' +  scriptJquery('#feelingActType').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanier" class="" onclick="showFeelingContanier()">'+scriptJquery(this).attr('data-title').toLowerCase()+'</a>';
            }
            
            scriptJquery('#activity_post_tags').css('display', 'block');
            scriptJquery('#feeling_activity').val(scriptJquery(this).attr('data-title').toLowerCase());
            scriptJquery('#feeling_elem_act').show();
            scriptJquery('#feeling_elem_act').html(finalFeeling);
            scriptJquery('#dash_elem_act').hide();
            scriptJquery('#activity_post_feeling_container').hide();
          });
          //Autosuggest Feeling Work

            AttachEventListerSE('click', '#feeling_activity', function(e) {

              if(scriptJquery('#feelingactivityid').val() == '')
                scriptJquery('.activity_post_feelingcontent_container').show();
            });
            
            AttachEventListerSE('keyup', '#feeling_activity', function(e) {
            
              socialShareSearch();

              if(!scriptJquery('#feeling_activity').val()) {
                if (e.which == 8) {
                  scriptJquery('#feelingActType').html('');
                  scriptJquery('#feelingActType').hide();
                  scriptJquery('.activityfeelingactivity-ul').html('');
                  if(scriptJquery('#feelingactivityid').val())
                    document.getElementById('feelingactivityid').value = '';
                  if(scriptJquery('#feelingactivityid').val() == '')
                    scriptJquery('.activity_post_feelingcontent_container').show();
                  
                  var toValueACTIVITYFeedbg = scriptJquery('#toValues').val();
                  if((toValueACTIVITYFeedbg.length == 0 && !scriptJquery('#feelingactivityid').val())) {
                    scriptJquery('#activity_post_tags').css('display', 'none');
                  }
                  
                  //Feed Background Image Work
                  if(document.getElementById('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
                    var feedbgid = scriptJquery('#feedbgid').val();
                    document.getElementById('hideshowfeedbgcont').style.display = 'block';
                    scriptJquery('#feedbg_main_continer').css('display','block');
                    var feedagainsrcurl = scriptJquery('#feed_bg_image_'+feedbgid).attr('src');
                    scriptJquery('.activity_post_box').css("background-image","url("+ feedagainsrcurl +")");
                    scriptJquery('#feedbgid_isphoto').val(1);
                    scriptJquery('#feedbg_main_continer').css('display','block');
                    if(feedbgid) {
                      scriptJquery('#activity-form').addClass('feed_background_image');
                    }
                  }
                }
              }
            });
            
            //static search function
            function socialShareSearch() {

              // Declare variables
              var socialtitlesearch, socialtitlesearchfilter, allsocialshare_lists, allsocialshare_lists_li, allsocialshare_lists_p, i;
              
              socialtitlesearch = document.getElementById('feeling_activity');
              socialtitlesearchfilter = socialtitlesearch.value.toUpperCase();
              allsocialshare_lists = document.getElementById("all_feelings");
              allsocialshare_lists_li = allsocialshare_lists.getElementsByTagName('li');

              // Loop through all list items, and hide those who don't match the search query
              for (i = 0; i < allsocialshare_lists_li.length; i++) {
              
                allsocialshare_lists_a = allsocialshare_lists_li[i].getElementsByTagName("a")[0];


                if (allsocialshare_lists_a.innerHTML.toUpperCase().indexOf(socialtitlesearchfilter) > -1) {
                    allsocialshare_lists_li[i].style.display = "";
                } else {
                  //  allsocialshare_lists_li[i].style.display = "none";
                }
              }
            }
            
            en4.core.runonce.add(function() {
              scriptJquery('#feeling_activity').keyup(function(e) {
                if (e.which == 8) {
                  document.getElementById('feelingactivityiconid').value = '';
                  document.getElementById('feelingactivity_custom').value = '';
                  document.getElementById('feelingactivity_customtext').value = '';
                  scriptJquery('#feeling_elem_act').html('');
                  //scriptJquery('#feeling_activity').attr("placeholder", "Choose Feeling or activity...");
                }
              });
            });

            function showFeelingContanier() {
            
              if(scriptJquery('#activity_post_feeling_container').css("display") == 'table') {
                scriptJquery('#showFeelingContanier').removeClass('active');
                scriptJquery('#activity_post_feeling_container').hide();
              } else {
                scriptJquery('#showFeelingContanier').addClass('active');
                scriptJquery('#feeling_activity_remove_act').show();
                scriptJquery('#activity_post_feeling_container').show();
              }
            } 
            
            function feelingactivityremoveact() {
              scriptJquery('#activity_post_feeling_container').hide();
              scriptJquery('#activity_feelings').removeClass('active');
              //scriptJquery('#feeling_activity_remove_act').hide();
              scriptJquery('#feelingActType').html('');
              scriptJquery('#feelingActType').hide();
              scriptJquery('.activityfeelingactivity-ul').html('');
              if(scriptJquery('#feelingactivityid').val())
                document.getElementById('feelingactivityid').value = '';
              scriptJquery('#feeling_activity').val('');
              document.getElementById('feelingactivityiconid').value = '';
              scriptJquery('#feeling_elem_act').html('');
              //Feed Background Image Work
              if(document.getElementById('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
                var feedbgid = scriptJquery('#feedbgid').val();
                document.getElementById('hideshowfeedbgcont').style.display = 'block';
                scriptJquery('#feedbg_main_continer').css('display','block');
                var feedagainsrcurl = scriptJquery('#feed_bg_image_'+feedbgid).attr('src');
                scriptJquery('.activity_post_box').css("background-image","url("+ feedagainsrcurl +")");
                scriptJquery('#feedbgid_isphoto').val(1);
                scriptJquery('#feedbg_main_continer').css('display','block');
                if(feedbgid) {
                  scriptJquery('#activity-form').addClass('feed_background_image');
                }
              }
              var toValueACTIVITYFeedbg = scriptJquery('#toValues').val();
              if((toValueACTIVITYFeedbg.length == 0 && !scriptJquery('#feelingactivityid').val())) {
                scriptJquery('#activity_post_tags').css('display', 'none');
              }
            }
          //Feeling Work End
          <?php } ?>
    </script>
<script type="text/javascript">
scriptJquery('#discard_post').click(function(){
  hideStatusBoxSecond();
  scriptJquery('.activity_confirmation_popup_overlay').hide();
  scriptJquery('.activity_confirmation_popup').hide();
  scriptJquery('.activity_post_media_options').removeClass('activity_composer_active');
});
scriptJquery('#goto_post').click(function(){
scriptJquery('.activity_confirmation_popup').hide();  
scriptJquery('.activity_confirmation_popup_overlay').hide();
});
<?php if($this->allowprivacysetting){ ?>
  //set default privacy of logged-in user
  en4.core.runonce.add(function() {
    scriptJquery('.adv_privacy_optn > li[class!="dropdown-divider"]:first').find('a').trigger('click')
    privacySetAct = true;
  });
<?php  }else{ ?>
  var privacySetAct = true;
<?php } ?>
AttachEventListerSE('click','.adv_privacy_optn li a',function(e){
e.preventDefault();
if(!scriptJquery(this).parent().hasClass('multiple')){
scriptJquery('.adv_privacy_optn > li').removeClass('active');
var text = scriptJquery(this).text();
scriptJquery(this).parent().addClass('active');
scriptJquery('#adv_pri_option').html(text);
scriptJquery('#activity_privacy_icon').remove();
scriptJquery('<i id="activity_privacy_icon" class="'+scriptJquery(this).find('i').attr('class')+'"></i>').insertBefore('#adv_pri_option');

if(scriptJquery(this).parent().hasClass('activity_network'))
  scriptJquery('#privacy').val(scriptJquery(this).parent().attr('data-src')+'_'+scriptJquery(this).parent().attr('data-rel'));
else if(scriptJquery(this).parent().hasClass('activity_list'))
  scriptJquery('#privacy').val(scriptJquery(this).parent().attr('data-src')+'_'+scriptJquery(this).parent().attr('data-rel'));
else
scriptJquery('#privacy').val(scriptJquery(this).parent().attr('data-src'));
}
// scriptJquery('.activity_privacy_btn').parent().removeClass('activity_pulldown_active');
});

AttachEventListerSE('click','.mutiselect',function(e){
if(scriptJquery(this).attr('data-rel') == 'network-multi')
var elem = 'activity_network';
else
var elem = 'activity_list';
var elemens = scriptJquery('.'+elem);
var html = '';
for(i=0;i<elemens.length;i++){
html += '<li><input class="checkbox" type="checkbox" value="'+scriptJquery(elemens[i]).attr('data-rel')+'">'+scriptJquery(elemens[i]).text()+'</li>';
}
en4.core.showError('<form id="'+elem+'_select" class="activity_privacyselectpopup"><p>Please select network to display post</p><ul class="clearfix">'+html+'</ul><div class="activity_privacyselectpopup_btns clearfix"><button type="submit">Save</button><button class="close" onclick="Smoothbox.close();return false;">Close</button></div></form>');
scriptJquery ('.activity_privacyselectpopup').parent().parent().addClass('activity_privacyselectpopup_wrapper');
//pre populate
var valueElem = scriptJquery('#privacy').val();
if(valueElem && valueElem.indexOf('network_list_') > -1 && elem == 'activity_network'){
var exploidV =  valueElem.split(',');
for(i=0;i<exploidV.length;i++){
   var id = exploidV[i].replace('network_list_','');
   scriptJquery('.checkbox[value="'+id+'"]').prop('checked', true);
}
}else if(valueElem && valueElem.indexOf('member_list_') > -1 && elem == 'activity_list'){
var exploidV =  valueElem.split(',');
for(i=0;i<exploidV.length;i++){
   var id = exploidV[i].replace('member_list_','');
   scriptJquery('.checkbox[value="'+id+'"]').prop('checked', true);
}
}
});
AttachEventListerSE('submit','#activity_list_select',function(e){
e.preventDefault();
var isChecked = false;
var activity_list_select = scriptJquery('#activity_list_select').find('[type="checkbox"]');
var valueL = '';
for(i=0;i<activity_list_select.length;i++){
if(!isChecked)
  scriptJquery('.adv_privacy_optn > li').removeClass('active');
if(scriptJquery(activity_list_select[i]).is(':checked')){
  isChecked = true;
  var el = scriptJquery(activity_list_select[i]).val();
  scriptJquery('.lists[data-rel="'+el+'"]').addClass('active');
  valueL = valueL+'member_list_'+el+',';
}
}
if(isChecked){
 scriptJquery('#privacy').val(valueL);
 scriptJquery('#adv_pri_option').html("<?php echo $this->translate('Multiple Lists'); ?>");
 scriptJquery('.activity_privacy_btn').attr('title',"<?php echo $this->translate('Multiple Lists'); ?>");;
scriptJquery(this).find('.close').trigger('click');
}
scriptJquery('#activity_privacy_icon').removeAttr('class').addClass('icon_activity_lists');
});
AttachEventListerSE('submit','#activity_network_select',function(e){
e.preventDefault();
var isChecked = false;
var activity_network_select = scriptJquery('#activity_network_select').find('[type="checkbox"]');
var valueL = '';
for(i=0;i<activity_network_select.length;i++){
  if(!isChecked)
    scriptJquery('.adv_privacy_optn > li').removeClass('active');
  if(scriptJquery(activity_network_select[i]).is(':checked')){
    isChecked = true;
    var el = scriptJquery(activity_network_select[i]).val();
    scriptJquery('.network[data-rel="'+el+'"]').addClass('active');
    valueL = valueL+'network_list_'+el+',';
  }
}
if(isChecked){
 scriptJquery('#privacy').val(valueL);
 scriptJquery('#adv_pri_option').html('Multiple Network');
 scriptJquery('.activity_privacy_btn').attr('title','Multiple Network');;
scriptJquery(this).find('.close').trigger('click');
}
scriptJquery('#activity_privacy_icon').removeAttr('class').addClass('icon_activity_network');
});
<?php if($settings->getSetting('enableglocation', 1)) { ?>
  // function initGoogleMap(){
var input = document.getElementById('tag_location');
if(input){
  if(isGoogleKeyEnabled){
  var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
      var place = autocomplete.getPlace();
      if (!place.geometry) {
        return;
      }
      scriptJquery('#locValues-element').html('<span class="tag">'+scriptJquery('#tag_location').val()+' <a href="javascript:void(0);" class="loc_remove_act notclose">x</a></span>');
      scriptJquery('#dash_elem_act').show();
      scriptJquery('#location_elem_act').show();
      scriptJquery('#location_elem_act').html('at <a href="javascript:;" class="seloc_clk">'+scriptJquery('#tag_location').val()+'</a>');
      scriptJquery('#tag_location').hide();
      document.getElementById('activitylng').value = place.geometry.location.lng();
      document.getElementById('activitylat').value = place.geometry.location.lat();
      
      //Location composer
      if(scriptJquery('#activity_post_tag_location')) {
        scriptJquery('#activity_post_tag_location').addClass('d-none');
        scriptJquery('#activity_post_tag_input').removeAttr('style');
        scriptJquery('#activity_post_tag_input').removeClass('_blank');
        scriptJquery('#activity_post_tag_location').remove();
      }
    
      //Feed Background Image Work
      if(document.getElementById('feedbgid')) {
        scriptJquery('#activity_body').focus();
        autosize(scriptJquery('#activity_body').removeAttr("class").removeAttr("data-autosize-on"));
        scriptJquery('#activity_post_tags').css('display', 'block');
        scriptJquery('#feedbgid_isphoto').val(0);
        scriptJquery('.activity_post_box').css('background-image', 'none');
        scriptJquery('#activity-form').removeClass('feed_background_image');
        scriptJquery('#feedbg_main_continer').css('display','none');
        
      }
  });
}
}
  // }
<?php } ?>
AttachEventListerSE('click','.loc_remove_act',function(e){
scriptJquery('#activitylng').val('');
scriptJquery('#activitylat').val('');
scriptJquery('#tag_location').val('');
scriptJquery('#locValues-element').html('');
scriptJquery('#tag_location').show();
scriptJquery('#location_elem_act').hide();
if(!scriptJquery('#toValues-element').children().length)
   scriptJquery('#dash_elem_act').hide();
   
var feedbgid = scriptJquery('#feedbgid').val();
var feedagainsrcurl = scriptJquery('#feed_bg_image_'+feedbgid).attr('src');
scriptJquery('.activity_post_box').css("background-image","url("+ feedagainsrcurl +")");
scriptJquery('#feedbgid_isphoto').val(1);
scriptJquery('#feedbg_main_continer').css('display','block');
if(feedbgid) {
  scriptJquery('#activity-form').addClass('feed_background_image');
}
if(feedbgid == 0) {
  scriptJquery('#activity-form').removeClass('feed_background_image');
}
})    

// Populate data
var maxRecipients = 50;
var to = {
id : false,
type : false,
guid : false,
title : false
};

function removeFromToValue(id) {   
  id = `${id}` 
  //check for edit form
  if(scriptJquery('#ajaxsmoothbox_main').length){
    removeFromToValueEdit(id);
    return;
  }
    
  // code to change the values in the hidden field to have updated values
  // when recipients are removed.
  var toValues = document.getElementById('toValues').value;
  var toValueArray = toValues.split(",");
  var toValueIndex = "";

  var checkMulti = id.indexOf(',') > -1;

  // check if we are removing multiple recipients
  if (checkMulti!=-1){
    var recipientsArray = id.split(",");
    for (var i = 0; i < recipientsArray.length; i++){
      removeToValue(recipientsArray[i], toValueArray);
    }
  }
  else{
    removeToValue(id, toValueArray);
  }
  scriptJquery('#tag_friends_input').prop("disabled",false);
  var firstElem = scriptJquery('#toValues-element > span').eq(0).text();
  var countElem = scriptJquery('#toValues-element').children().length;
  var html = '';

  if(!firstElem.trim()){
    scriptJquery('#tag_friend_cnt').html('');
    scriptJquery('#tag_friend_cnt').hide();
    if(!scriptJquery('#tag_location').val())
    scriptJquery('#dash_elem_act').hide();
    return;
  }else if(countElem == 1){
    html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
  }else if(countElem > 2){
    html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
    html = html + ' and <a href="javascript:;" class="activitytag_clk">'+(countElem-1)+' others</a>';
  }else{
    html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
    html = html + ' and <a href="javascript:;" class="activitytag_clk">'+scriptJquery('#toValues-element > span').eq(1).text().replace('x','')+'</a>';
}
  scriptJquery('#activity_post_tags').css('display', 'block');
  scriptJquery('#tag_friend_cnt').html('with '+html);
  scriptJquery('#tag_friend_cnt').show();
  scriptJquery('#dash_elem_act').show();
}

function removeToValue(id, toValueArray){
for (var i = 0; i < toValueArray.length; i++){
  if (toValueArray[i]==id) toValueIndex =i;
}

toValueArray.splice(toValueIndex, 1);
scriptJquery('#toValues').val(toValueArray.join());

if(toValueArray.length == 0 && !scriptJquery('#feelingactivityid').val())
  scriptJquery('#activity_post_tags').css('display', 'none');
}

<?php if($viewer->getIdentity()) { ?>
en4.core.runonce.add(function() {
   AutocompleterRequestJSON('tag_friends_input', "<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'suggest'), 'default', true) ?>", function(selecteditem) {
     scriptJquery("#tag_friends_input").val("");
     if( scriptJquery('#toValues').val().split(',').length >= maxRecipients ){
        scriptJquery('#tag_friends_input').prop("disabled",true);
      }
      let totalVal = scriptJquery('#toValues').val() ? scriptJquery('#toValues').val().split(',') : [];
      if(totalVal.length > 0 && totalVal.indexOf(selecteditem.id.toString()) > -1){
        return;
      }
      scriptJquery("#toValues").val((totalVal.length > 0 ? scriptJquery('#toValues').val()+"," : "")+selecteditem.id);
      scriptJquery('#toValues-element').append('<span class="tag" id="tospan_'+selecteditem.title+'_'+selecteditem.id+'">'+selecteditem.title+'<a href="javascript:;" onclick="scriptJquery(this).parent().remove();removeFromToValue('+selecteditem.id+')">x</a></span>')
      var firstElem = scriptJquery('#toValues-element > span').eq(0).text();
      var countElem = scriptJquery('#toValues-element  > span').children().length;
      var html = '';
      if(countElem == 1){
        html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
      }else if(countElem > 2){
        html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
        html = html + ' and <a href="javascript:;"  class="activitytag_clk">'+(countElem-1)+' others</a>';
      }else{
        html = '<a href="javascript:;" class="activitytag_clk">'+firstElem.replace('x','')+'</a>';
        html = html + ' and <a href="javascript:;" class="activitytag_clk">'+scriptJquery('#toValues-element > span').eq(1).text().replace('x','')+'</a>';
      }
      scriptJquery('#activity_post_tags').css('display', 'block');
      scriptJquery('#tag_friend_cnt').html('with '+html);
      scriptJquery('#tag_friend_cnt').show();
      scriptJquery('#dash_elem_act').show();
  });
});
<?php } ?>
</script>
<script type="application/javascript">
var isMemberHomePage = <?php echo !empty($this->isMemberHomePage) ? $this->isMemberHomePage : 0; ?>;
var isOnThisDayPage = <?php echo !empty($this->isOnThisDayPage) ? $this->isOnThisDayPage : 0; ?>;
          AttachEventListerSE('click','.schedule_post_schedue',function(e){
           e.preventDefault();
           var value = scriptJquery('#scheduled_post').val();
           if(scriptJquery('.activity_shedulepost_error').css('display') == 'block' || !value){
            return;   
           }
           scriptJquery('.activity_shedulepost_overlay').hide();
           scriptJquery('.activity_shedulepost_select').hide();
           scriptJquery('.activity_shedulepost').addClass('active');
           scriptJquery('html').removeClass('overflow-hidden');
           scriptJquery('body').removeClass('overflow-hidden');

          });
          AttachEventListerSE('click','#activity_shedulepost',function(e){
           e.preventDefault();
           scriptJquery('.activity_shedulepost_overlay').show();
           scriptJquery('.activity_shedulepost_select').show();
           scriptJquery(this).addClass('active');
           scriptJquery('html').addClass('overflow-hidden');
           scriptJquery('body').addClass('overflow-hidden');
           makeDateTimePicker();
           activitytooltip();
          });
          AttachEventListerSE('click','.schedule_post_close',function(e){
              e.preventDefault();
            scriptJquery('.activity_shedulepost_overlay').hide();
            scriptJquery('.activity_shedulepost_select').hide();
            scriptJquery('html').removeClass('overflow-hidden');
            scriptJquery('body').removeClass('overflow-hidden');
            if(scriptJquery('.activity_shedulepost_error').css('display') == 'block')
              scriptJquery('.activity_shedulepost_error').html('').hide();
            scriptJquery('#scheduled_post').val('');
             scriptJquery('#activity_shedulepost').removeClass('active');
             scriptJquery('.bootstrap-datetimepicker-widget').hide();
          });
          var schedule_post_datepicker;
          function makeDateTimePicker(){
            if(scriptJquery('.activity_shedulepost_edit_overlay').length){
              var elem = 'scheduled_post_edit';
              var datepicker = 'datetimepicker_edit';
            }else{
              var elem = 'scheduled_post';
              var datepicker  = 'datetimepicker';
            }
            //if(!scriptJquery('#'+elem).val()){
              var now = new Date();
              now.setMinutes(now.getMinutes() + 10);
           // }
            schedule_post_datepicker = scriptJquery('#'+datepicker).datetimepicker({
            format: 'dd/MM/yyyy hh:mm:ss',
            maskInput: false,           // disables the text input mask
            pickDate: true,            // disables the date picker
            pickTime: true,            // disables de time picker
            pick12HourFormat: true,   // enables the 12-hour format time picker
            pickSeconds: true,         // disables seconds in the time picker
            startDate: now,      // set a minimum date
            endDate: Infinity          // set a maximum date
          });
          schedule_post_datepicker.on('changeDate', function(e) {
            var time = e.localDate.toString();
            var timeObj = new Date(time).getTime();
            //add 10 minutes
            var now = new Date();
            now.setMinutes(now.getMinutes() + 10);
            if(scriptJquery('.activity_shedulepost_edit_overlay').length){
              var error = 'activity_shedulepost_edit_error';
            }else{
              var error = 'activity_shedulepost_error';
            }
            if(timeObj < now.getTime()){
              scriptJquery('.'+error).html("<?php echo $this->translate('choose time 10 minutes greater than current time.'); ?>").show();
              return false;
            }else{
             scriptJquery('.'+error).html('').hide();
            }
          });  
          }
          </script>      
     
<?php if(empty($this->subjectGuid) && !$this->isOnThisDayPage) { ?>
  <?php echo $this->partial('_homefeedtabs.tpl', 'activity', array('identity' => $this->identity, 'lists' => $this->lists)); ?>
<?php } else if(!$this->isOnThisDayPage && $this->subject() && ($this->subject()->getType() == 'group' || $this->subject()->getType() == 'user' || (method_exists($this->subject(),'allowFeedTabs') && $this->subject()->allowFeedTabs()))) { ?>
  <?php echo $this->partial('_subjectfeedtabs.tpl', 'activity', array('identity'=>$this->identity,'lists'=>$this->lists)); ?>
<?php } else { ?>
<div class="activity_feed_filters displayN" style="display: none">
  <ul class="activity_filter_tabs clearfix">
    <li class="activity_filter_tabsli activity_active_tabs">
      <a href="javascript:;" class="activity_tooltip" data-src="all">
        <span></span>
      </a>
    </li>
  </ul>
</div>
<script type="application/javascript">
  var filterResultrequest;
  AttachEventListerSE('click','ul.activity_filter_tabs li a',function(e){
//    if(scriptJquery(this).parent().hasClass('active') || scriptJquery(this).hasClass('viewmore'))
//     return false;
    if(scriptJquery(this).hasClass('viewmore'))
      return false;

    scriptJquery('.activity_filter_img').show();
    scriptJquery('.activity_filter_tabsli').removeClass('active activity_active_tabs');
    scriptJquery(this).parent().addClass('active activity_active_tabs');
    var filterFeed = scriptJquery(this).attr('data-src');

    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';
    var hashTag = scriptJquery('#hashtagtext').val();
    var adsIds = scriptJquery('.ecmads_ads_listing_item');
    var adsIdString = "";
    if(adsIds.length > 0){
      scriptJquery('.ecmads_ads_listing_item').each(function(index){
        if(typeof dataFeedItem == "undefined")
          adsIdString = scriptJquery(this).attr('rel')+ "," + adsIdString ;
      });
    }
    filterResultrequest = scriptJquery.ajax({
      type: "POST",
      url : url+"?search="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage,
      data : {
        format : 'html',
        'filterFeed' : filterFeed,
        'feedOnly' : true,
        'action_id':activityGetAction_id,
        'getUpdates':1,
        'nolayout' : true,
        'ads_ids': adsIdString,
        'subject' : en4.core.subject.guid,
      },
      evalScripts : true,
      success : function(responseHTML) {

        if(!activityGetFeeds){
          scriptJquery('#activity-feed').append(responseHTML);
        }else{
          scriptJquery('#activity-feed').html(responseHTML);
        }
        
        if(scriptJquery('#activity-feed').find('li').length > 0)
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
<style>
  .displayN{
    display: none !important;
  }
</style>
<?php
}
 ?>

<?php if ($this->updateSettings && !$this->action_id && !$this->isOnThisDayPage): // wrap this code around a php if statement to check if there is live feed update turned on ?>
  <script type="text/javascript">
    var ActivityUpdateHandler;
    en4.core.runonce.add(function() {
      try {
          ActivityUpdateHandler = new ActivityUpdateHandler({
            'baseUrl' : en4.core.baseUrl,
            'basePath' : en4.core.basePath,
            'showImmediately':true,
            'identity' : 4,
            'delay' : <?php echo $this->updateSettings;?>,
            'last_id': <?php echo sprintf('%d', $this->firstid) ?>,
            'subject_guid' : '<?php echo $this->subjectGuid ?>'
          });
          setTimeout("ActivityUpdateHandler.start()",1250);
          //activityUpdateHandler.start();
          window._ActivityUpdateHandler = ActivityUpdateHandler;
      } catch( e ) {
        //if( $type(console) )
      }
      // if(scriptJquery('#activity-feed').children().length && <?php echo (int)$this->getUpdates; ?> == 1)
      //  scriptJquery('.activity_noresult_tip').hide();
      // else
      //  scriptJquery('.activity_noresult_tip').show();
    });
  </script>
<?php endif;?>

<?php if( $this->post_failed == 1 ): ?>
  <div class="tip">
    <span>
      <?php $url = $this->url(array('module' => 'user', 'controller' => 'settings', 'action' => 'privacy'), 'default', true) ?>
      <?php echo $this->translate('The post was not added to the feed. Please check your %1$sprivacy settings%2$s.', '<a href="'.$url.'">', '</a>') ?>
    </span>
  </div>
<?php endif; ?>

<?php // If requesting a single action and it does not exist, show error ?>
<?php if( !$this->activity ): ?>
  <?php if( $this->action_id ): ?>
    <span style="display: none" class="no_content_activity_id">
      <h2><?php echo $this->translate("Activity Item Not Found") ?></h2>
      <p>
        <?php echo $this->translate("The page you have attempted to access could not be found.") ?>
      </p>
    </span>
  <?php endif; ?>
<?php endif; ?>
<?php if(!$this->action_id): ?>
  <div class="activity_content_load_img">
    <ul class="feed mt-2">
      <?php for($i=1;$i<=($this->action_id ? 1 : 4);$i++) { ?>
        <li>
          <div class="feed_content_loader">
            <div class="photo_box"></div>
            <div class="cont_line _title"></div>
            <div class="cont_line _date"></div>
            <div class="_cont"><div class="cont_line"></div><div class="cont_line"></div><div class="cont_line"></div></div>
            <div class="_footer"><div class="cont_line"></div><div class="cont_line"></div><div class="cont_line"></div></div>
            <div class="loader_animation"></div>
          </div>
        </li>
      <?php } ?>
    </ul>
  </div>
<?php endif; ?>
<div class="activity_noresult_tip block overflow-hidden " style="display:<?php echo !sprintf('%d', $this->activityCount) && $this->getUpdates ? 'block' : 'none'; ?>;">
  <div class="no_result_tip">
    <img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="No Result">
    <?php if(!$this->isOnThisDayPage){ ?>
      <p><?php echo $this->translate("Nothing has been posted here yet - be the first!") ?></p>
    <?php }else{ ?>
      <p><?php echo $this->translate('No memories for you on this day.') ?></p>
    <?php } ?>
  </div>
</div>
<div id="feed-update"></div>
<?php echo $this->activityLoop($this->activity, array(
  'action_id' => $this->action_id,
  'communityadsIds' => $this->communityadsIds,
  'viewAllComments' => $this->viewAllComments,
  'viewAllLikes' => $this->viewAllLikes,
  'getUpdate' => $this->getUpdate,
  'getUpdates' => $this->getUpdates,
  'isOnThisDayPage'=>$this->isOnThisDayPage,
  'isMemberHomePage' => $this->isMemberHomePage,
  'userphotoalign' => $this->userphotoalign,
  'filterFeed'=>$this->filterFeed,
  'feeddesign'=>$this->feeddesign,
  'enabledModuleNames' => $enabledModuleNames
)) ?>
<?php if(!$this->isOnThisDayPage): ?>
<div class="load_more" id="feed_viewmore_activityact" style="display: none;">
	<a href="javascript:void(0);" id="feed_viewmore_activityact_link" class="btn btn-alt"><span><?php echo $this->translate('View More');?></span></a>
</div>
<div class="load_more" id="feed_loading" style="display: none;">
  <span><i class="icon_loading"></i></span>
</div>
<?php if( !$this->feedOnly && $this->isMemberHomePage && !$this->isOnThisDayPage): ?>
</div>
<?php endif; ?>
<div class="activity_tip activity_tip_box" id="feed_no_more_feed" style="display:none;">
	<span>No more post</span>
</div>
<script type="application/javascript">

  AttachEventListerSE('click','#activity_tabs_cnt li a',function(e) {
    var id = scriptJquery(this).parent().attr('data-url');
    var instid = scriptJquery(this).parent().parent().attr('data-url');

    if(instid == 4) return;

    scriptJquery('.activity_tabs_content').hide();


    scriptJquery('#activity_tabs_cnt > li').removeClass('active');
    scriptJquery(this).parent().addClass('active');
    scriptJquery('#activity_tab_'+id).show();

    if(id == 1 || id == 3) {
      scriptJquery('#feed_no_more_feed').addClass('dNone');
    }else
      scriptJquery('#feed_no_more_feed').removeClass('dNone');
    if(id == 3) return;
    if(scriptJquery('#activity_tab_'+id).find('.activity_loading_img').length){
      var url = en4.core.baseUrl+scriptJquery('#activity_tab_'+id).find('.activity_loading_img').attr('data-href');
      //get content

      requestsent = (scriptJquery.ajax({
      method: 'post',
      'url': url,
      'data': {
        format: 'html'
      },
      success : function(responseHTML) {
       scriptJquery('#activity_tab_'+id).html(responseHTML);
      }
    }));
    }
  });

</script>
<?php endif; ?>
<?php if($this->isOnThisDayPage){ ?>
<div class="block activity_feed_thanks_block text-center">
	<img src="application/modules/Activity/externals/images/thanks.png"alt="" />
  <span><?php echo $this->translate("Thanks for coming!"); ?></span>
</div>
<?php } ?>

<?php if($this->feeddesign == 2){  ?>
	<script type="application/javascript">
		var wookmark = undefined;
		var isactivityloadedfirst= true;
	 //Code for Pinboard View
		var wookmark<?php echo $randonNumber ?>;
		function pinboardLayoutFeed_<?php echo $randonNumber ?>(force){
			if(isactivityloadedfirst == true){
				scriptJquery('#activity-feed').append('<li id="activity_feed_loading" style="margin-bottom:20px;"><div class="loading_container" style="height:100px;"></div></li>')
			}
			//scriptJquery('.new_image_pinboard').css('display','none');
			var imgLoad = imagesLoaded('._activitypinimg');
			var imgleangth = imgLoad.images.length;
			if(imgleangth > 0){
				var counter = 1; 
				imgLoad.on('progress',function(instance,image){
					scriptJquery(image.img).removeClass('_activitypinimg');
					scriptJquery(image.img).closest('.activity_pinfeed_hidden').removeClass('activity_pinfeed_hidden');
					imageLoadedAll<?php echo $randonNumber ?>();
					if(counter == 1){
						//scriptJquery('.activity_pinfeed_hidden').removeClass('activity_pinfeed_hidden');
						//scriptJquery('._activitypinimg').removeClass('._activitypinimg');
					}
					if(counter == imgleangth){
						scriptJquery('#activity_feed_loading').remove();
					}
					counter = counter +1;
				});
			}else{
				scriptJquery('.activity_pinfeed_hidden').removeClass('activity_pinfeed_hidden');
				scriptJquery('._activitypinimg').removeClass('._activitypinimg');
				imageLoadedAll<?php echo $randonNumber ?>();
				scriptJquery('#activity_feed_loading').remove();
			}
		}
		function imageLoadedAll<?php echo $randonNumber ?>(force){
		 scriptJquery('#activity-feed').addClass('core_pinboard_<?php echo $randonNumber; ?>');
		//  if (typeof wookmark<?php echo $randonNumber ?> == 'undefined') {
				(function() {
					function getWindowWidth() {
						return Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
					}				
					wookmark<?php echo $randonNumber ?> = new Wookmark('.core_pinboard_<?php echo $randonNumber; ?>', {
						itemWidth: <?php echo isset($this->activity_pinboard_width) ? str_replace(array('px','%'),array(''),$this->activity_pinboard_width) : '300'; ?>, // Optional min width of a grid item
						outerOffset: 0, // Optional the distance from grid to parent
           <?php if($orientation = ($this->layout()->orientation == 'right-to-left')){ ?>
              align:'right',
            <?php }else{ ?>
              align:'left',
            <?php } ?>
						flexibleWidth: function () {
							// Return a maximum width depending on the viewport
							return getWindowWidth() < 1024 ? '100%' : '40%';
						}
					});
				})();
			// } else {
			// 	wookmark<?php echo $randonNumber ?>.initItems();
			// 	wookmark<?php echo $randonNumber ?>.layout(true);
			// }
	}
  function feedUpdateFunction(){
    en4.core.runonce.trigger();
    setTimeout(function(){pinboardLayoutFeed_<?php echo $randonNumber ?>();},200);
  }
	en4.core.runonce.add(function() {
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	// scriptJquery(document).click(function(){
	// 	pinboardLayoutFeed_<?php echo $randonNumber ?>();
	// });
	scriptJquery(document).bind("paste", function(e){
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	AttachEventListerSE('click','.tab_layout_activity_feed',function (event) {
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	scriptJquery('#activity-feed').one("DOMSubtreeModified",function(){
		// do something after the div content has changed
	 imageLoadedAll<?php echo $randonNumber ?>();
	});
	</script>
<?php } ?>
<script type="application/javascript">

en4.core.runonce.add(function() {
  if(typeof complitionRequestTrigger == 'function'){
    complitionRequestTrigger();  
  }  
})

scriptJquery('.selectedTabClick').click(function(e){
  var rel = scriptJquery(this).data('rel');
  if(rel != 'all'){
    document.getElementById('compose-'+rel+'-activator').click();  
    if(rel == "photo"){
      document.getElementById('dragandrophandler').click();  
    }
  }  
})
</script>
<?php 
  unset($enabledModuleNames);
  unset($settings);
 ?>
