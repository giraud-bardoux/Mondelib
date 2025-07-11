<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _editPostComposer.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'); 

$getEmojis = Engine_Api::_()->getDbTable('emojis', 'activity')->getEmojis(array('fetchAll' => 1));
?>
<div class="activity_editpost">
  <div class="activity_post_container clearfix block">
    <div class="activity_editpost_title"><?php echo $this->translate("Edit Post"); ?></div>
    <form data-status="<?php echo $this->action->type == 'status' ? '1' : 0; ?>" method="post"
      class="edit-activity-form" enctype=" application/x-www-form-urlencoded">
      <div class="activity_post_box clearfix">
        <div class="activity_post_box_img" id="activity_post_box_img">
          <?php echo $this->htmlLink('javascript:;', $this->itemPhoto($this->viewer(), 'thumb.icon', $this->viewer()->getTitle()), array()) ?>
        </div>
        <div class="compose-container" id="compose-container">
          <textarea id="edit_activity_body" maxlength="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000); ?>" data-length="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000); ?>" cols="1" rows="1" name="body"
            placeholder="<?php echo $this->escape($this->translate('Post Something...')) ?>"><?php echo htmlspecialchars_decode($this->action->body); ?></textarea>
        </div>
        <input type="hidden" name="userphotoalign" value="<?php echo $this->userphotoalign; ?>">
        <input type="hidden" name="action_id" value="<?php echo $this->action->getIdentity(); ?>">
        
        <div class="activity_post_tags font_color_light">
          <span style="display:<?php echo $this->location || engine_count($this->members) ? 'inline' : 'none'; ?>;"
            id="dash_elem_act_edit">-</span>
          <?php $enablefeeling = Engine_Api::_()->authorization()->isAllowed('activity', null, 'enablefeeling'); ?><span
            style="display:<?php echo $this->feelings ? 'inline' : 'none'; ?>;" id="feeling_elem_actedit">
            <?php if ($this->feeling && $this->feelingIcons_title && $this->feeling_Icons && empty($this->feelings->feeling_custom)) { ?>
              <img class="feeling_icon" title="<?php echo $this->feelingIcons_title; ?>"
                src="<?php echo Engine_Api::_()->storage()->get($this->feeling_Icons, "")->getPhotoUrl(); ?>">
              <?php echo $this->feeling->title; ?> <a href="javascript:;" id="showFeelingContanieredit" class="" <?php if ($enablefeeling) { ?> onclick="showFeelingContanieredit()" <?php } ?>><?php echo $this->feelingIcons_title; ?></a>
            <?php } else if (!empty($this->feelings->feeling_custom)) { ?>
                <img class="feeling_icon" title="<?php echo $this->feelings->feeling_customtext; ?>"
                  src="<?php echo Engine_Api::_()->storage()->get($this->feeling->file_id, "")->getPhotoUrl(); ?>">
              <?php echo $this->feeling->title; ?> <a href="javascript:;" id="showFeelingContanieredit" class="" <?php if ($enablefeeling) { ?> onclick="showFeelingContanieredit()" <?php } ?>><?php echo $this->feelings->feeling_customtext; ?></a>
            <?php } ?></span>

          <span id="tag_friend_cnt_edit" style="display:none;"> with </span> <span
            id="location_elem_act_edit"><?php echo $this->location ? 'at <a href="javascript:;" class="seloc_clk_edit">' . $this->location->venue . '</a>' : ''; ?></span>
        </div>
        <div id="activity-menu" class="activity-menu activity_post_tools">
          <span class="activity-menu-selector" id="activity-menu-selector"></span>
          <?php if (engine_in_array('tagUseActivity', $this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_tag">
              <a href="javascript:;" id="activity_tag_edit" class="activity_tooltip" data-bs-toggle="tooltip" title="<?php echo $this->translate('Tag People'); ?>">&nbsp;</a>
            </span>
          <?php } ?>
          <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) && engine_in_array('locationactivity', $this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_location">
              <a href="javascript:;" id="activity_location_edit" data-bs-toggle="tooltip"  title="<?php echo $this->translate('Check In'); ?>" class="activity_tooltip"><i><svg x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g id="_01_align_center"><path d="M255.104,512.171l-14.871-12.747C219.732,482.258,40.725,327.661,40.725,214.577c0-118.398,95.981-214.379,214.379-214.379   s214.379,95.981,214.379,214.379c0,113.085-179.007,267.682-199.423,284.932L255.104,512.171z M255.104,46.553   c-92.753,0.105-167.918,75.27-168.023,168.023c0,71.042,110.132,184.53,168.023,236.473   c57.892-51.964,168.023-165.517,168.023-236.473C423.022,121.823,347.858,46.659,255.104,46.553z"></path><path d="M255.104,299.555c-46.932,0-84.978-38.046-84.978-84.978s38.046-84.978,84.978-84.978s84.978,38.046,84.978,84.978   S302.037,299.555,255.104,299.555z M255.104,172.087c-23.466,0-42.489,19.023-42.489,42.489s19.023,42.489,42.489,42.489   s42.489-19.023,42.489-42.489S278.571,172.087,255.104,172.087z"></path></g></svg></i></a>
            </span>
          <?php } ?>

          <?php //Feeling work ?>
          <?php if (engine_in_array('feelingssctivity', $this->composerOptions)) { ?>
            <span class="activity_post_tool_i tool_i_feelings" id="activity_feelings_editspan">
              <a href="javascript:;" id="activity_feelings_edit" data-bs-toggle="tooltip" title="<?php echo $this->translate('Feeling/Activity'); ?>" class="activity_tooltip"><i><svg viewBox="0 0 24 24"><path d="M6,12c-.553,0-1-.448-1-1,0-1.892,1.232-4,3-4s3,2.108,3,4c0,.552-.447,1-1,1s-1-.448-1-1c0-1.054-.68-2-1-2s-1,.946-1,2c0,.552-.447,1-1,1Zm7-1c0,.552,.447,1,1,1s1-.448,1-1c0-1.054,.68-2,1-2s1,.946,1,2c0,.552,.447,1,1,1s1-.448,1-1c0-1.892-1.232-4-3-4s-3,2.108-3,4Zm-1,7c3.107,0,5.563-2.162,5.666-2.254,.411-.367,.446-.997,.08-1.409-.367-.412-.998-.449-1.41-.084-.02,.018-2.005,1.748-4.336,1.748s-4.311-1.726-4.336-1.748c-.412-.365-1.041-.33-1.41,.081-.367,.412-.332,1.044,.08,1.412,.103,.092,2.559,2.254,5.666,2.254Zm7.957-11.998c.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077ZM5.25,17c-.966,0-1.75,.862-1.75,1.925,0-1.063-.784-1.925-1.75-1.925s-1.75,.862-1.75,1.925c0,1.514,1.974,3.288,2.957,4.077,.316,.254,.769,.254,1.085,0,.983-.789,2.957-2.562,2.957-4.077,0-1.063-.784-1.925-1.75-1.925ZM22.313,8.038c-.531,.15-.84,.703-.689,1.234,.249,.884,.376,1.801,.376,2.728,0,5.514-4.486,10-10,10-.927,0-1.845-.126-2.728-.376-.535-.149-1.085,.158-1.234,.69-.15,.531,.158,1.084,.689,1.234,1.061,.3,2.161,.452,3.272,.452,6.617,0,12-5.383,12-12,0-1.11-.152-2.211-.452-3.272-.15-.532-.701-.838-1.234-.69ZM1.181,15c.06,0,.121-.005,.182-.017,.543-.1,.902-.621,.803-1.164-.109-.597-.165-1.208-.165-1.819C2,6.486,6.486,2,12,2c.612,0,1.225,.055,1.819,.165,.554,.101,1.064-.26,1.164-.803s-.26-1.064-.803-1.164c-.714-.131-1.447-.198-2.181-.198C5.383,0,0,5.383,0,12c0,.731,.066,1.465,.198,2.181,.089,.482,.509,.819,.982,.819Z"></path></svg></i></a>
            </span>
          <?php } ?>
          <?php //Feeling work ?>

          <?php if(engine_in_array('smilesActivity',$this->composerOptions) && engine_count($getEmojis) > 0) { ?>
            <span class="activity_post_tool_i tool_i_emoji feeling_emoji_comment_select" id="activity_feeling_emojis">
              <a href="javascript:;" id="activity_feeling_emojisa" title="<?php echo $this->translate('Emojis'); ?>" data-bs-toggle="tooltip">&nbsp;</a>
            </span>
          <?php } ?>

          <?php if (engine_in_array('smilesActivity', $this->composerOptions) && 0) { ?>
            <span class="activity_post_tool_i tool_i_emoji">
              <a href="javascript:;" id="activityemoji-edit-a" class="activity_tooltip" data-bs-toggle="tooltip" title="<?php echo $this->translate('Stickers'); ?>">&nbsp;</a>
              <div id="activityemoji_edit" class="comment_emotion_container ">
                <div class="comment_emotion_container_inner clearfix">
                  <div class="comment_emotion_holder">
                    <div class="loading_container" style="height:100%;"></div>
                  </div>
                </div>
              </div>
            </span>
          <?php } ?>
        </div>
      </div>

      <?php if ($this->action->type == 'post_self_buysell') { ?>
        <div id="composer-tray-container-edit">
          <div id="compose-tray-edit" class="compose-tray">
            <div id="compose-buysell-edit-body" class="compose-body">
              <div class="activity_sell_composer">
                <div class="activity_sell_composer_title">
                  <input type="text" id="buysell-title-edit" value="<?php echo $this->item->getTitle(); ?>"
                    placeholder="<?php echo $this->translate('What are you selling?'); ?>" name="buysell-title">
                  <span id="buysell-title-count-edit" class="font_color_light">100</span>
                </div>
                <div class="activity_sell_composer_title">
                  <input type="text" id="buy-url" value="<?php echo $this->item->buy; ?>"
                    placeholder="<?php echo $this->translate('Where to Buy (URL Optional)'); ?>" name="buy-url">
                </div>
                <div class="activity_sell_composer_price">
                  <span class="activity_sell_composer_price_currency">
                    <?php 
                  		$fullySupportedCurrencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));

                      $currentCurrency = Engine_Api::_()->payment()->defaultCurrency();
                      $currentData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);

                      if (engine_count($fullySupportedCurrencies) > 1) {

                        $currencyData = '<div class="dropdown"><a href="javascript:;" id="currency_btn_currency_sell" class="show_icons" type="button" aria-expanded="false">';
                        if(isset($currentData->icon) && !empty($currentData->icon)) {
                          $path = Engine_Api::_()->core()->getFileUrl($currentData->icon);
                          if($path) {
                            $currencyData .= '<i id="currency_icon_sell_edit" class="icon_modal"><img src="'.$path.'" alt="'.$currentCurrency.'" height="18" width="18"></i>';
                          }
                        } else {
                          $currencyData .= '<i id="currency_icon_sell_edit" class="icon_modal" style="display:none;"><img src="" alt="'.$currentCurrency.'" height="18" width="18"></i>';
                        }
                        $currencyData .= '<span id="currency_text_sell_edit">'.$currentCurrency.'</span></a>';
                                  
                        $currencyData .= '<div class="dropdown-menu"><ul id="currency_change_data_sell_edit">';
                          $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
                          foreach ($fullySupportedCurrencies as $currency) {
                            if($currentCurrency == $currency->code)
                              $active ='selected';
                            else
                              $active ='';
                            
                            $currencyData .= '<li class="'.$active.'"><a href="javascript:;" class="dropdown-item" data-rel="'.$currency->code.'" title=""'.$currency->title.'">';
                              if(isset($currency->icon) && !empty($currency->icon)) {
                              $path = Engine_Api::_()->core()->getFileUrl($currency->icon);
                                if($path) {
                                  $currencyData .= '<i class="dropdown_icon"><img src="'.$path.'" alt="img"></i>';
                                }
                              }
                              $currencyData .= '<span>'.$currency->code.'</span></a></li>';
                          }
                        $currencyData .= '</ul></div></div>';
                      } else {
                        $currencyData = Engine_Api::_()->payment()->getCurrentCurrency();
                      }
                    ?>
                    <?php echo $currencyData; ?>
                  </span>
                  <span class="activity_sell_composer_price_input"><input type="text" id="buysell-price-edit"
                      value="<?php echo $this->item->price; ?>" placeholder="<?php echo $this->translate('Add price'); ?>"
                      name="buysell-price" />
                      <input type="hidden" name="buysell-currency"
                      value="<?php echo !empty($this->item) ? $this->item->currency : '' ?>"
                      id="buysell-currency-edit">  
                  </span>
                </div>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
                <div class="activity_sell_composer_location">
                  <i class="font_color_light fas fa-map-marker-alt"></i>
                  <span id="locValuesbuysell-element-edit"></span>
                  <span id="buyselllocal-edit">
                    <input type="text" id="buysell-location-edit"
                      value="<?php echo !empty($this->locationBuySell) ? $this->locationBuySell->venue : ($this->item->location ? $this->item->location : '') ?>"
                      placeholder="<?php echo $this->translate('Add location (optional)'); ?>" name="buysell-location"
                      autocomplete="off">
                      <input type="hidden" name="activitybuyselllat"
                      value="<?php echo !empty($this->locationBuySell) ? $this->locationBuySell->lat : '' ?>"
                      id="activitybuyselllat-edit">
                    <input type="hidden" name="activitybuyselllng"
                      value="<?php echo !empty($this->locationBuySell) ? $this->locationBuySell->lng : '' ?>"
                      id="activitybuyselllng-edit">
                  </span>
                </div>
                <?php } ?>
                <div class="activity_sell_composer_des">
                  <textarea id="buysell-description-edit"
                    placeholder="<?php echo $this->translate('Describe your item (optional)'); ?>"
                    name="buysell-description"><?php echo $this->item->getDescription(); ?></textarea>
                </div>

                <?php if ($this->action->attachment_count) { ?>
                  <div class="activity_sell_composer_images">
                    <?php foreach ($this->action->getAttachments() as $attachment) { ?>
                      <div class="_buyselleditimg"><img src="<?php echo $attachment->item->getPhotoUrl() ?>" alt="" /></div>
                    <?php } ?>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
      <script type="application/javascript">
        //currency change
    		AttachEventListerSE('click','ul#currency_change_data_sell_edit li > a',function(e) {
          console.log('afasd');
    	    scriptJquery('#currency_text_sell_edit').html(scriptJquery(this).attr('data-rel'));
    	    scriptJquery('#currency_icon_sell_edit').attr('src', scriptJquery(this).find('img').attr('src'));
    			scriptJquery('#buysell-currency-edit').val(scriptJquery(this).attr('data-rel'));
    	    if(scriptJquery(this).find('img').length > 0) {
    	      scriptJquery('#currency_icon_sell_edit').show();
    	      scriptJquery('#currency_icon_sell_edit').find('img').attr('src', scriptJquery(this).find('img').attr('src'));
    	    } else {
    	      scriptJquery('#currency_icon_sell_edit').hide();
    	    }
    	  });

        let activity_feedLimit = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000); ?>;
        function ajaxsmoothboxcallback() {
          if(activity_feedLimit){
            scriptJquery(".compose-content-counter-edit").html(activity_feedLimit-scriptJquery("#edit_activity_body").val().length);
          }

          if (scriptJquery('#buysell-location-edit').val()) {
            scriptJquery('#locValuesbuysell-element-edit').html('<span class="tag">' + scriptJquery('#buysell-location-edit').val() + ' <a href="javascript:void(0);" class="buysellloc_remove_act_edit">x</a></span>');
            scriptJquery('#locValuesbuysell-element-edit').show();
            scriptJquery('#buyselllocal-edit').hide();
            document.getElementById('activitybuyselllng-edit').value = scriptJquery('#activitybuyselllng-edit').val();
            document.getElementById('activitybuyselllat-edit').value = scriptJquery('#activitybuyselllat-edit').val();
          }
          if (document.getElementById('buysell-location-edit') && isGoogleKeyEnabled) {
            var input = document.getElementById('buysell-location-edit');
            if(typeof input != 'undefined') {
              var autocomplete = new google.maps.places.Autocomplete(input);
              google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                  return;
                }
                scriptJquery('#locValuesbuysell-element-edit').html('<span class="tag">' + scriptJquery('#buysell-location-edit').val() + ' <a href="javascript:void(0);" class="buysellloc_remove_act_edit">x</a></span>');
                scriptJquery('#locValuesbuysell-element-edit').show();
                scriptJquery('#buyselllocal-edit').hide();
                document.getElementById('activitybuyselllng-edit').value = place.geometry.location.lng();
                document.getElementById('activitybuyselllat-edit').value = place.geometry.location.lat();
              });
            }
            scriptJquery('#buysell-title-edit').trigger('input');
          }
          scriptJquery('#edit_activity_body').hashtags();
          isOnEditField = true;
          <?php if (engine_count($this->mentionData)) { ?>
            mentionsCollectionValEdit = <?php echo json_encode($this->mentionData); ?>;
          <?php } ?>

          EditFieldValue = <?php echo json_encode(htmlspecialchars_decode($this->action->body), JSON_HEX_QUOT | JSON_HEX_TAG); ?>;

          <?php //if (engine_in_array('userTags', $composerOptions)) { ?>
            scriptJquery('textarea#edit_activity_body').mentionsInput({
              onDataRequest: function (mode, query, callback) {
                scriptJquery.getJSON('activity/ajax/friends/query/' + query, function (responseData) {
                  responseData = _.filter(responseData, function (item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
                  callback.call(this, responseData);
                });
              },
              //defaultValue:<?php //echo json_encode($this->action->body,JSON_HEX_QUOT | JSON_HEX_TAG); ?>,
              onCaret: true
            });
          <?php //} ?>
          
          scriptJquery('textarea#edit_activity_body').mentionsInput("update");
          scriptJquery('textarea#edit_activity_body').focus();
          activitytooltip();
        }
        function ajaxsmoothboxcallbackclose() {
          scriptJquery('#toValuesEditChanges').attr('id', 'toValuesEdit');
          scriptJquery('#toValuesEditChanges-element').attr('id', 'toValuesEdit-element');
          scriptJquery('#toValuesEditChanges-wrapper').attr('id', 'toValuesEdit-wrapper');
          scriptJquery('textarea#edit_activity_body').mentionsInput('reset');
            scriptJquery('._feeling_emoji_content').removeClass('from_bottom');
        }
        function ajaxsmoothboxcallbackBefore() {
          scriptJquery('#toValuesEdit').attr('id', 'toValuesEditChanges');
          scriptJquery('#toValuesEdit-element').attr('id', 'toValuesEditChanges-element');
          scriptJquery('#toValuesEdit-wrapper').attr('id', 'toValuesEditChanges-wrapper');
        }
      </script>
      <div class="activity_post_tag_container clearfix activity_post_tag_cnt_edit"
        style="display:<?php echo !engine_count($this->members) ? 'none' : 'none'; ?>;">
        <span class="tag">With</span>
        <div class="activity_post_tags_holder">
          <div id="toValuesEdit-element">
            <?php $tagUserIds = ''; ?>
            <?php foreach ($this->members as $members) {
              $user = Engine_Api::_()->getItem('user', $members['user_id']);
              if (!$user)
                contunue;
              $tagUserIds = $members['user_id'] . ',' . $tagUserIds;
              ?>
              <span id="tospan_<?php echo $user->getIdentity(); ?>" class="tag"><?php echo $user->getTitle(); ?> <a
                  href="javascript:void(0);"
                  onclick="scriptJquery(this).parent().remove();removeFromToValueEdit('<?php echo $user->getIdentity(); ?>', 'toValuesEdit');">x</a></span>
            <?php } ?>
          </div>
          <div class="activity_post_tag_input">
            <input type="text" placeholder="<?php echo $this->translate('Who are you with?'); ?>"
              id="tag_friends_input_edit" />
            <div id="toValuesEdit-wrapper" style="display:none">
              <input type="hidden" id="toValuesEdit" name="tag_friends" value="<?php echo rtrim($tagUserIds, ','); ?>">
            </div>
          </div>
        </div>
      </div>


      <div class="activity_post_tag_container clearfix activity_post_location_container activity_post_location_container_edit" style="display:<?php echo !$this->location ? 'none' : 'none'; ?>;">
        <span class="tag"><?php echo $this->translate("At"); ?></span>
        <div class="activity_post_tags_holder">
          <div id="locValuesEdit-element"></div>
          <div class="activity_post_tag_input">
            <input type="text" placeholder="<?php echo $this->translate('Where are you?'); ?>" name="tag_location"
              id="tag_location_edit" value="<?php echo $this->location ? $this->location->venue : ''; ?>" />
            <input type="hidden" name="activitylng" id="activitylngEdit"
              value="<?php echo !empty($this->location->lat) ? $this->location->lng : '' ?>">
            <input type="hidden" name="activitylat" id="activitylatEdit"
              value="<?php echo !empty($this->location->lng) ? $this->location->lat : '' ?>">
          </div>
        </div>
      </div>

      <?php //Feeling work ?>
      <div id="activity_post_feeling_container_edit"
        class="activity_post_tag_container clearfix activity_post_feeling_container activity_post_feeling_container_edit"
        style="display:<?php echo !$this->feelings ? 'none' : 'none'; ?>;">
        <span id="feelingActTypeedit" class="tag"
          style="display:<?php echo $this->feelings ? 'table-cell' : 'none'; ?>;">
          <?php $feeling = Engine_Api::_()->getItem('activity_feeling', @$this->feelings->feeling_id); ?>
          <?php echo @$feeling->title; ?>
        </span>
        <div class="activity_post_tags_holder">
          <div id="feelingValues-element"></div>
          <div class="activity_post_tag_input">
            <?php $feelingIcons = Engine_Api::_()->getItem('activity_feelingicon', @$this->feelings->feelingicon_id); ?>
            <input autocomplete="off" type="text"
              placeholder="<?php echo $this->translate('Choose Feeling or activity...'); ?>" name="feeling_activityedit"
              id="feeling_activityedit"
              value="<?php if (empty($this->feelings->feeling_custom)) {
                echo $feelingIcons ? $feelingIcons->title : $this->feelingIcons_title;
              } else if ($this->feelings->feeling_custom) {
                echo $this->feelings->feeling_customtext;
              } ?>" />

            <a onclick="feelingactivityremoveactedit();" style="display:block;" href="javascript:void(0);"
              class="feeling_activity_remove_act notclose" id="feeling_activity_remove_actedit"
              title="<?php echo $this->translate('Remove'); ?>">x</a>

            <input type="hidden" name="feelingactivityidedit" id="feelingactivityidedit"
              value="<?php echo !empty($this->feelings->feeling_id) ? $this->feelings->feeling_id : '' ?>">
            <input type="hidden" name="feelingactivitytypeedit" id="feelingactivitytypeedit"
              value="<?php echo !empty($this->feeling->type) ? $this->feeling->type : '' ?>">
            <input type="hidden" name="feelingactivityiconidedit" id="feelingactivityiconidedit"
              value="<?php echo !empty($this->feelings->feelingicon_id) ? $this->feelings->feelingicon_id : '' ?>">
            <input type="hidden" name="feelingactivity_resource_typeedit" id="feelingactivity_resource_typeedit"
              value="<?php echo !empty($this->feelings->resource_type) ? $this->feelings->resource_type : '' ?>">

            <input type="hidden" name="feelingactivity_customedit" id="feelingactivity_customedit"
              value="<?php echo @$this->feelings->feeling_custom ?>" class="resetaftersubmit">
            <input type="hidden" name="feelingactivity_customtextedit" id="feelingactivity_customtextedit"
              value="<?php echo @$this->feelings->feeling_customtext ?>" class="resetaftersubmit">
            <!--<input type="hidden" name="feelingactivity_type" id="feelingactivity_type" value="" class="resetaftersubmit">-->
          </div>
        </div>

        <div class="activity_post_feelingautocompleter_containeredit activity_post_feelings_autosuggest"
          style="display:none;">
          <div class="clearfix custom_scrollbar">
            <ul class="activityfeelingactivity-ul" id="showSearchResultsedit"></ul>
          </div>
        </div>

        <div class="activity_post_feelingcontent_containeredit activity_post_feelings_autosuggest"style="display:none;">
          <div class="clearfix custom_scrollbar">
            <ul id="all_feelings_edit">
              <?php $feelings = Engine_Api::_()->getDbTable('feelings', 'activity')->getFeelings(array('fetchAll' => 1)); ?>
              <?php foreach ($feelings as $feeling): ?>
                <li data-title="<?php echo $feeling->title; ?>" class="activity_feelingactivitytypeedit clearfix"
                  data-rel="<?php echo $feeling->feeling_id; ?>" data-type="<?php echo $feeling->type; ?>">
                  <a href="javascript:void(0);" class="activity_feelingactivitytypea_edit">
                    <img id="activityfeelingactivitytypeimgedit_<?php echo $feeling->feeling_id; ?>" title="<?php echo $feeling->title ?>" src="<?php echo Engine_Api::_()->storage()->get($feeling->file_id, '')->getPhotoUrl(); ?>">
                    <span><?php echo $this->translate($feeling->title); ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <?php //Feeling Work End ?>
      <?php $privacyFeed = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.view.privacy'); ?>
      <div id="compose-menu" class="activity_compose_menu dropdown">
        <input type="hidden" name="privacy" id="privacy_edit" value="<?php echo $this->action->privacy ? $this->action->privacy : "everyone"; ?>">
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000)){ ?>
            <div class="compose-content-counter compose-content-counter-edit" style="display: inline-block;"><?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000);?></div>
          <?php } ?>
        <div class="activity_compose_menu_btns">
          
          <div class="activity_privacy_chooser activity_pulldown_wrapper">
            <a href="javascript:void(0);" class="activity_privacy_btn activity_privacy_btn_edit activity_tooltip btn btn-alt"  type="button" data-bs-toggle="dropdown" aria-expanded="false"><i id="activity_privacy_icon_edit"></i><span id="adv_pri_option_edit"></span><i class="_arrow fa-solid fa-angle-down"></i></a>
            <ul class="adv_privacy_optn_edit dropdown-menu dropdown-option-menu dropdown-menu-end">
              <?php if (engine_in_array('everyone', $privacyFeed)) { ?>
                <li data-src="everyone" class=""><a href="javascript:;" class="dropdown-item"><i class="icon_activity_public"></i><span><?php echo $this->translate('Everyone'); ?></span></a></li>
              <?php } ?>
              <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && engine_in_array('networks', $privacyFeed)) { ?>
                <li data-src="networks"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $this->translate('Friends or Networks'); ?></span></a>

                </li>
              <?php } ?>
              <?php if (engine_in_array('friends', $privacyFeed)) { ?>
                <li data-src="friends"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_friends"></i><span><?php echo $this->translate('Friends Only'); ?></span></a>
                </li>
              <?php } ?>
              <?php if (engine_in_array('onlyme', $privacyFeed)) { ?>
                <li data-src="onlyme"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_me"></i><span><?php echo $this->translate('Only Me'); ?></span></a></li>
              <?php } ?>
              <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && engine_count($this->usernetworks)) { ?>
                <li class="dropdown-divider"></li>
                <?php foreach ($this->usernetworks as $usernetworks) { ?>
                  <li data-src="network_list" class="network activity_network activity_network_edit"
                    data-rel="<?php echo $usernetworks->getIdentity(); ?>"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $usernetworks->getTitle(); ?></span></a></li>
                <?php } ?>
                <li class="multiple mutiselectedit" data-rel="network-multi"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_network"></i><span><?php echo $this->translate('Multiple Networks'); ?></span></a>
                </li>
              <?php } ?>
              <?php if (engine_count($this->userlists)) { ?>
                <li class="dropdown-divider"></li>
                <?php foreach ($this->userlists as $userlists) { ?>
                  <li data-src="members_list" class="lists activity_list activity_list_edit" data-rel="<?php echo $userlists->getIdentity(); ?>"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_lists"></i><span><?php echo $userlists->getTitle(); ?></span></a></li>
                <?php }
                if (engine_count($this->userlists) > 1) { ?>
                  <li class="multiple mutiselectedit" data-rel="lists-multi"><a href="javascript:;" class="dropdown-item"><i class="icon_activity_lists"></i><span><?php echo $this->translate('Multiptle Lists'); ?></span></a>
                  </li>
                <?php }
              } ?>
            </ul>

          </div>
          <button class="btn btn-primary" id="compose-submit" type="submit"><?php echo $this->translate("Save") ?></button>
        </div>
        <?php if (engine_in_array('activitytargetpost', $enable)) { ?>
          <span
            class="composer_targetpost_edit_toggle activity_tooltip <?php echo ($this->targetPost) ? 'composer_targetpost_edit_toggle_active' : '' ?>"
            title="<?php echo $this->translate('Choose Preferred Audience'); ?>" href="javascript:void(0);">

            <?php if ($this->targetPost) { ?>
              <input id="compose-targetpost-edit-form-input" class="compose-form-input" type="checkbox" checked="checked"
                name="post_to_targetpost" style="display: none;">
              <input type="hidden" id="country_name_edit" name="targetpost[country_name]"
                value="<?php echo $this->targetPost->country_name; ?>">
              <input type="hidden" id="city_name_edit" name="targetpost[city_name]"
                value="<?php echo $this->targetPost->city_name; ?>">
              <input type="hidden" id="location_send_edit" name="targetpost[location_send]"
                value="<?php echo $this->targetPost->location_send; ?>">
              <input type="hidden" id="location_city_edit" name="targetpost[location_city]"
                value="<?php echo $this->targetPost->location_city; ?>">
              <input type="hidden" id="location_country_edit" name="targetpost[location_country]"
                value="<?php echo $this->targetPost->location_country; ?>">
              <input type="hidden" id="gender_send_edit" name="targetpost[gender_send]"
                value="<?php echo $this->targetPost->gender_send; ?>">
              <input type="hidden" id="age_min_send_edit" name="targetpost[age_min_send]"
                value="<?php echo $this->targetPost->age_min_send; ?>">
              <input type="hidden" id="age_max_send_edit" name="targetpost[age_max_send]"
                value="<?php echo $this->targetPost->age_max_send; ?>">
              <input type="hidden" id="targetpostlat_edit" name="targetpost[targetpostlat]"
                value="<?php echo $this->targetPost->lat; ?>">
              <input type="hidden" id="targetpostlng_edit" name="targetpost[targetpostlng]"
                value="<?php echo $this->targetPost->lng; ?>">
              <input type="hidden" id="targetpostlatcity_edit" name="targetpost[targetpostlatcity]"
                value="<?php echo $this->targetPost->lat; ?>">
              <input type="hidden" id="targetpostlngcity_edit" name="targetpost[targetpostlngcity]"
                value="<?php echo $this->targetPost->lng; ?>">
            <?php } else { ?>
              <input id="compose-targetpost-edit-form-input" class="compose-form-input" type="checkbox"
                name="post_to_targetpost" style="display: none;">
            <?php } ?>
          </span>
        <?php } ?>
      </div>
    </form>


    <script type="text/javascript">
      var savingtextActivityPost = "<i class='fas fa-circle-notch fa-spin'></i>";
      var savingtextActivityPostOriginal = "<?php echo $this->translate('Save') ?>";
      //set default privacy of logged-in user
      en4.core.runonce.add(function () {
        var privacy = scriptJquery('#privacy_edit').val();
        if (privacy) {
          if (privacy == 'everyone')
            scriptJquery('.adv_privacy_optn_edit >li[data-src="everyone"]').find('a').trigger('click');
          else if (privacy == 'networks')
            scriptJquery('.adv_privacy_optn_edit >li[data-src="networks"]').find('a').trigger('click');
          else if (privacy == 'friends')
            scriptJquery('.adv_privacy_optn_edit >li[data-src="friends"]').find('a').trigger('click');
          else if (privacy == 'onlyme')
            scriptJquery('.adv_privacy_optn_edit >li[data-src="onlyme"]').find('a').trigger('click');
          else if (privacy && privacy.indexOf('network_list_') > -1) {
            var exploidV = privacy.split(',');
            for (i = 0; i < exploidV.length; i++) {
              var id = exploidV[i].replace('network_list_', '');
              scriptJquery('.activity_network_edit[data-rel="' + id + '"]').addClass('active');
            }
            scriptJquery('#adv_pri_option_edit').html('<?php echo $this->translate("Multiple Networks"); ?>');
            scriptJquery('.activity_privacy_btn_edit').attr('title', '<?php echo $this->translate("Multiple Networks"); ?>');;
            scriptJquery('#activity_privacy_icon_edit').removeAttr('class').addClass('activity_network');
          } else if (privacy && privacy.indexOf('member_list_') > -1) {
            var exploidV = privacy.split(',');
            for (i = 0; i < exploidV.length; i++) {
              var id = exploidV[i].replace('member_list_', '');
              scriptJquery('.activity_list_edit[data-rel="' + id + '"]').addClass('active');
            }
            scriptJquery('#adv_pri_option_edit').html('<?php echo $this->translate("Multiple Lists"); ?>');
            scriptJquery('.activity_privacy_btn_edit').attr('title', '<?php echo $this->translate("Multiple Lists"); ?>');;
            scriptJquery('#activity_privacy_icon_edit').removeAttr('class').addClass('activity_list');
          }
        }
        activitytooltip();
      });


      function removeFromToValueEdit(id) {
        id = `${id}` 
        // code to change the values in the hidden field to have updated values
        // when recipients are removed.
        var toValuesEdit = document.getElementById('toValuesEdit').value;
        var toValueArray = toValuesEdit.split(",");
        var toValueIndex = "";

        var checkMulti = id.search(/,/);

        // check if we are removing multiple recipients
        if (checkMulti != -1) {
          var recipientsArray = id.split(",");
          for (var i = 0; i < recipientsArray.length; i++) {
            removeToValueEdit(recipientsArray[i], toValueArray);
          }
        }
        else {
          removeToValueEdit(id, toValueArray);
        }
        document.getElementById('tag_friends_input_edit').disabled = false;
        var firstElem = scriptJquery('#toValuesEdit-element > span').eq(0).text();
        var countElem = scriptJquery('#toValuesEdit-element').children().length;
        var html = '';

        if (!firstElem.trim()) {
          scriptJquery('#tag_friend_cnt_edit').html('');
          scriptJquery('#tag_friend_cnt_edit').hide();
          if (!scriptJquery('#tag_location_edit').val())
            scriptJquery('#dash_elem_act_edit').hide();
          return;
        } else if (countElem == 1) {
          html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
        } else if (countElem > 2) {
          html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
          html = html + ' and <a href="javascript:;" class="tag_clk_edit">' + (countElem - 1) + ' others</a>';
        } else {
          html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
          html = html + ' and <a href="javascript:;" class="tag_clk_edit">' + scriptJquery('#toValuesEdit-element > span').eq(1).text().replace('x', '') + '</a>';
        }
        scriptJquery('#tag_friend_cnt_edit').html('with ' + html);
        scriptJquery('#tag_friend_cnt_edit').show();
        scriptJquery('#dash_elem_act_edit').show();

      }

      function removeToValueEdit(id, toValueArray) {
        for (var i = 0; i < toValueArray.length; i++) {
          if (toValueArray[i] == id) toValueIndex = i;
        }

        toValueArray.splice(toValueIndex, 1);
        document.getElementById('toValuesEdit').value = toValueArray.join();
      }

      en4.core.runonce.add(function () {
        AutocompleterRequestJSON('tag_friends_input_edit', "<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'suggest'), 'default', true) ?>", function(selecteditem) {
          scriptJquery("#tag_friends_input_edit").val("");
          if (scriptJquery('#toValuesEdit').val().split(',').length >= maxRecipients) {
            scriptJquery('#tag_friends_input_edit').prop("disabled", true);
          }
          let totalVal = scriptJquery('#toValuesEdit').val() ? scriptJquery('#toValuesEdit').val().split(',') : [];
          if (totalVal.length > 0 && totalVal.indexOf(selecteditem.id.toString()) > -1) {
            return;
          }
          scriptJquery("#toValuesEdit").val((totalVal.length > 0 ? scriptJquery('#toValuesEdit').val() + "," : "") + selecteditem.id);
          scriptJquery('#toValuesEdit-element').append('<span class="tag" id="tospan_' + selecteditem.title + '_' + selecteditem.id + '">' + selecteditem.title + '<a href="javascript:;" onclick="scriptJquery(this).parent().remove();removeFromToValueEdit(' + selecteditem.id + ')">x</a></span>')
          var firstElem = scriptJquery('#toValuesEdit-element > span').eq(0).text();
          var countElem = scriptJquery('#toValuesEdit-element  > span').children().length;
          var html = '';
          if (countElem == 1) {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
          } else if (countElem > 2) {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
            html = html + ' and <a href="javascript:;"  class="tag_clk_edit">' + (countElem - 1) + ' others</a>';
          } else {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
            html = html + ' and <a href="javascript:;" class="tag_clk_edit">' + scriptJquery('#toValuesEdit-element > span').eq(1).text().replace('x', '') + '</a>';
          }
          scriptJquery('#activity_post_tags').css('display', 'block');
          scriptJquery('#tag_friend_cnt_edit').html('with ' + html);
          scriptJquery('#tag_friend_cnt_edit').show();
          scriptJquery('#dash_elem_act_edit').show();
        });




      });


      //Feeling Work
      scriptJquery(document).click(function (e) {

        if (scriptJquery(e.target).attr('id') != 'activity_feelings_edit' && scriptJquery(e.target).attr('id') != 'feeling_activityedit' && scriptJquery(e.target).attr('class') != 'activity_feelingactivitytypeedit' && scriptJquery(e.target).attr('id') != 'showFeelingContanieredit' && scriptJquery(e.target).attr('id') != 'feelingActTypeedit' && scriptJquery(e.target).attr('class') != 'activity_feelingactivitytypea_edit') {
          if (scriptJquery('#activity_post_feeling_container_edit').css('display') == 'table') {
            scriptJquery('.activity_post_feeling_container_edit').hide();
            scriptJquery('.activity_post_feelingcontent_containeredit').hide();
          }
        } else if (scriptJquery(e.target).attr('id') == 'feelingActTypeedit') {
          scriptJquery('#feelingActTypeedit').html('');
          scriptJquery('#feelingActTypeedit').hide();
          scriptJquery('#feeling_activityedit').attr("placeholder", "Choose Feeling or activity...");

          if (scriptJquery('#feelingactivityidedit').val())
            document.getElementById('feelingactivityidedit').value = '';

          if (scriptJquery('#feelingactivitytypeedit').val())
            document.getElementById('feelingactivitytypeedit').value = '';

          if (scriptJquery('#feeling_activityedit').val())
            document.getElementById('feeling_activityedit').value = '';

          if (scriptJquery('#feelingactivity_customedit').val())
            document.getElementById('feelingactivity_customedit').value = '';

          if (scriptJquery('#feelingactivity_customtextedit').val())
            document.getElementById('feelingactivity_customtextedit').value = '';

          if (scriptJquery('#feelingactivityiconidedit').val())
            document.getElementById('feelingactivityiconidedit').value = '';

          scriptJquery('.activity_post_feelingcontent_containeredit').show();
          scriptJquery('#feeling_elem_actedit').html('');

        }
      });


      //Feeling Autosuggest work
      AttachEventListerSE('keyup', '#feeling_activityedit', function (e) {
        var search_stringEdit = scriptJquery("#feeling_activityedit").val();
        if (search_stringEdit == '') {
          search_stringEdit = 'default';
        }
        var autocompleteFeelingEdit;
        postdataEdit = {
          'text': search_stringEdit,
          'feeling_id': document.getElementById('feelingactivityidedit').value,
          'feeling_type': document.getElementById('feelingactivitytypeedit').value,
          'edit': 1,
        }
        if (autocompleteFeelingEdit) {
          autocompleteFeelingEdit.abort();
        }
        autocompleteFeelingEdit = scriptJquery.post("<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'getfeelingicons'), 'default', true) ?>",postdataEdit,function(data) {
          var parseJson = JSON.parse(data);

          if (parseJson.status == 1 && parseJson.html) {
            scriptJquery('.activity_post_feelingautocompleter_containeredit').show();
            scriptJquery("#showSearchResultsedit").html(parseJson.html);
          } else {
            if (scriptJquery('#feeling_activityedit').val()) {
              scriptJquery('.activity_post_feelingautocompleter_containeredit').show();
              var html = '<li data-title="' + scriptJquery('#feeling_activityedit').val() + '" class="activity_feelingactivitytypeliedit clearfix" data-rel=""><a href="javascript:void(0);" class="activity_feelingactivitytypea"><img class="feeling_icon" title="' + scriptJquery('#feeling_activityedit').val() + '" src="' + scriptJquery('#activityfeelingactivitytypeimgedit_' + scriptJquery('#feelingactivityidedit').val()).attr('src') + '"><span>' + scriptJquery('#feeling_activityedit').val() + '</span></a></li>';
              scriptJquery("#showSearchResultsedit").html(html);
            } else {
              scriptJquery('.activity_post_feelingautocompleter_containeredit').show();
              scriptJquery("#showSearchResultsedit").html(html);
            }
          }
        });
      });
      AttachEventListerSE('keyup', '#feeling_activityedit', function (e) {

        socialShareSearchedit();

        if (!scriptJquery('#feeling_activityedit').val()) {
          if (e.which == 8) {
            scriptJquery('#feelingActTypeedit').html('');
            scriptJquery('#feelingActTypeedit').hide();
            if (scriptJquery('#feelingactivityidedit').val())
              document.getElementById('feelingactivityidedit').value = '';

            document.getElementById('feelingactivity_customedit').value = '';
            document.getElementById('feelingactivity_customtextedit').value = '';

            if (scriptJquery('#feelingactivityidedit').val() == '')
              scriptJquery('.activity_post_feelingcontent_containeredit').show();
          }
        }
      });

      //static search function
      function socialShareSearchedit() {

        // Declare variables
        var socialtitlesearch, socialtitlesearchfilter, allsocialshare_lists, allsocialshare_lists_li, allsocialshare_lists_p, i;

        socialtitlesearch = document.getElementById('feeling_activityedit');
        socialtitlesearchfilter = socialtitlesearch.value.toUpperCase();
        allsocialshare_lists = document.getElementById("all_feelings_edit");
        allsocialshare_lists_li = allsocialshare_lists.getElementsByTagName('li');

        // Loop through all list items, and hide those who don't match the search query
        for (i = 0; i < allsocialshare_lists_li.length; i++) {

          allsocialshare_lists_a = allsocialshare_lists_li[i].getElementsByTagName("a")[0];


          if (allsocialshare_lists_a.innerHTML.toUpperCase().indexOf(socialtitlesearchfilter) > -1) {
            allsocialshare_lists_li[i].style.display = "";
          } else {
            allsocialshare_lists_li[i].style.display = "none";
          }
        }
      }


      //Feeling Work End

    </script>


    <script type="text/javascript">
      en4.core.runonce.add(function () {
        scriptJquery('#edit_activity_body').show();
        tagLocationWorkEdit();
        autosize(scriptJquery('#edit_activity_body'));
        scriptJquery('#edit_activity_body').trigger('keyup');
        <?php if (engine_count($this->members)) { ?>
          var firstElem = scriptJquery('#toValuesEdit-element > span').eq(0).text();
          var countElem = scriptJquery('#toValuesEdit-element').children().length;
          var html = '';

          if (!firstElem.trim()) {
            scriptJquery('#tag_friend_cnt_edit').html('');
            scriptJquery('#tag_friend_cnt_edit').hide();
            if (!scriptJquery('#tag_location_edit').val())
              scriptJquery('#dash_elem_act_edit').hide();
            return;
          } else if (countElem == 1) {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
          } else if (countElem > 2) {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
            html = html + ' and <a href="javascript:;" class="tag_clk_edit">' + (countElem - 1) + ' others</a>';
          } else {
            html = '<a href="javascript:;" class="tag_clk_edit">' + firstElem.replace('x', '') + '</a>';
            html = html + ' and <a href="javascript:;" class="tag_clk_edit">' + scriptJquery('#toValuesEdit-element > span').eq(1).text().replace('x', '') + '</a>';
          }
          scriptJquery('#tag_friend_cnt_edit').html('with ' + html);
          scriptJquery('#tag_friend_cnt_edit').show();
          scriptJquery('#dash_elem_act_edit').show();

        <?php } ?>
        var input = document.getElementById('tag_location_edit');
        if(isGoogleKeyEnabled && typeof input != 'undefined') {
          var autocomplete = new google.maps.places.Autocomplete(input);
          google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place.geometry) {
              return;
            }
            tagLocationWorkEdit();
            document.getElementById('activitylngEdit').value = place.geometry.location.lng();
            document.getElementById('activitylatEdit').value = place.geometry.location.lat();
          });
        }
      });
      function openTargetPostPopupEdit() {
        if (!scriptJquery('#location_send_edit').length)
          scriptJquery('.composer_targetpost_edit_toggle').append('<input type="hidden" id="country_name_edit"  name="targetpost[country_name]" value=""><input type="hidden" id="city_name_edit"  name="targetpost[city_name]" value=""><input type="hidden" id="location_send_edit"  name="targetpost[location_send]" value=""><input type="hidden" id="location_city_edit" name="targetpost[location_city]" value=""><input type="hidden" id="location_country_edit"  name="targetpost[location_country]"value=""><input type="hidden" id="gender_send_edit" name="targetpost[gender_send]" value=""><input type="hidden" id="age_min_send_edit" name="targetpost[age_min_send]" value=""><input type="hidden" id="age_max_send_edit" name="targetpost[age_max_send]" value=""><input type="hidden" id="targetpostlat_edit" name="targetpost[targetpostlat]" value=""><input type="hidden" id="targetpostlng_edit" name="targetpost[targetpostlng]" value=""><input type="hidden" id="targetpostlatcity_edit" name="targetpost[targetpostlatcity]" value=""><input type="hidden" id="targetpostlngcity_edit" name="targetpost[targetpostlngcity]" value="">');

        <?php
        $optionHTML = '';
        for ($i = 14; $i < 99; $i++) {
          $optionHTML = $optionHTML . '<option value="' . $i . '">' . $i . '</option>';
        } ?>
        var htmlOptions  = '<?php echo $optionHTML; ?>';
        msg = "<div class='activity_target_popup  clearfix'><div class='activity_target_post_popup_header'><?php echo $this->translate('Choose Preferred Audience'); ?></div><div class='activity_target_post_popup_cont'><p><?php echo $this->translate('Choose preferred audience for your post.'); ?></p>";
        var memberenable = '<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0); ?>';
        
        if(memberenable == 1){
          msg += "<div class='activity_target_popup_field clearfix'><div class='activity_target_popup_field_label'><?php echo $this->translate('Location'); ?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='Enter one or more countries, states or cities to show your post only to the people in those locations.'></i></div><div class='activity_target_popup_field_element activity_target_popup_field_element_edit'><span><input type='radio' checked='checked' class='selected_coun_val selected_coun_val_edit' name='country_type_sel_edit' value='world'> <?php echo $this->translate('World'); ?></span><span><input type='radio' name='country_type_sel_edit' class='selected_coun_val selected_coun_val_edit' value='country'> <?php echo $this->translate('Country'); ?></span><span><input class='selected_coun_val selected_coun_val_edit' type='radio' name='country_type_sel_edit' value='city'> <?php echo $this->translate('By City'); ?></span><div class='activity_target_popup_field_input'><input type='text' name='country_sel' id='country_sel_edit' placeholder='<?php echo $this->translate("Select Country"); ?>' style='display:none;'><input type='text' name='city_sel' id='city_sel_edit' placeholder='<?php echo $this->translate("Select City"); ?>' style='display:none;'><p class='activity_target_popup_error' style='display:none;' id='location_error_sel_edit'><?php echo $this->translate("Please select value."); ?></p></div></div></div>";  
        }
        msg += "<div class='activity_target_popup_field clearfix'>"+"<div class='activity_target_popup_field_label'><?php echo $this->translate('Gender'); ?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='<?php echo ("Choose to share your post with &quot;All&quot; or specific gender."); ?>'></i></div><div class='activity_target_popup_field_element'><span><input type='radio' checked='checked'  name='gender_type_sel_edit' value='all'> <?php echo $this->translate('All'); ?></span><span><input type='radio' name='gender_type_sel_edit'  value='male'><?php echo $this->translate('Men'); ?></span><span><input type='radio' name='gender_type_sel_edit' value='women'> <?php echo $this->translate('Women'); ?></span></div></div>"+"<div class='activity_target_popup_field'><div class='activity_target_popup_field_label'><?php echo $this->translate('Age'); ?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='<?php echo $this->translate("Select the minimum and maximum age of the people who will find your ad relevant."); ?>'></i></div><div class='activity_target_popup_field_element'><span><select name='age_sel_min' id='age_sel_min_edit'><option value='13'>13</option>"+htmlOptions+"</select> - <select name='age_sel_max' id='age_sel_max_edit'>"+htmlOptions+"<option value='99'>99+</option></select><p class='activity_target_popup_error' style='display:none;' id='age_error_sel'><?php echo $this->translate("Age max field is greater than Age min field."); ?></p></div></div>"+"</div><div class='activity_target_post_popup_btm'><button href=\"javascript:void(0);\" class='savevaluessel notclose'><?php echo $this->translate("Save"); ?></button><button href=\"javascript:void(0);\" class='removevaluessel removevaluessel_edit notclose' style='display:none;'><?php echo $this->translate("Remove"); ?></button><button href=\"javascript:void(0);\" onclick=\"javascript:parent.Smoothbox.close()\" class='secondary notclose'><?php echo $this->translate("Close"); ?></button></div></div>";
        Smoothbox.open(msg);
        //change values
        var location_send = scriptJquery('#location_send_edit');
        var location_city = scriptJquery('#location_city_edit');
        var location_country =scriptJquery(' #location_country_edit');
        var gender_send = scriptJquery('#gender_send_edit');
        var age_min_send = scriptJquery('#age_min_send_edit');
        var age_max_send = scriptJquery('#age_m ax_send_edit');
        if(location_send.val()  == 'country'){
          scriptJquery('#country_sel_edit').show();
           scriptJquery('#city_sel_edit').hide();
        }else if(location_send.val() == 'city'){
          scriptJquery('#country_sel_edit').hide();
          scriptJquery('#city_sel_edit').show();
        }else{
               scriptJquery('#country_sel_edit').hide();
          scriptJquery('#city_sel_edit').hide();
        }
             scriptJquery('input:radio[name="country_type_sel_edit"][value="'+location_send.val()+'"]').attr('checked',true);
        scriptJquery('#country_sel_edit').val(location_country.val());
        scriptJquery('#city_sel_edit').val(location_city.val());
        scriptJquery('input:radio[name="gender_type_sel_edit"][value="'+gender_send.val()+'"]').attr('checked',true);
        scriptJquery('#age_sel_min_edit').val(age_min_send.val());
        scriptJquery('#age_sel_max_edit').val(age_max_send.val());
        if (scriptJquery('#compose-targetpost-edit-form-input').is(':checked'))
          scriptJquery('.removevaluessel_edit').show();
        scriptJquery('#TB_ajaxContent').addClass('activity_target_post_popup_wrapper ');
          activitytooltip();
             if(memberenable)
     makeGoogleMapSelect();
        if(scriptJquery('#location_send_edit').length)
        scriptJquery(".activity_target_popup_field_element_edit input:radio[value='"+scriptJquery('#location_send_edit').val()+"']").attr("checked", true).trigger('click');
     }     
    </script>
  </div>
</div>  
