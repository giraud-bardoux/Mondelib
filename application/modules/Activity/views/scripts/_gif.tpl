<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _gif.tpl 2024-10-28 00:00:00Z 
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
$giphyApi = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.giphyapi', '');
$limit = 15;
?>
<?php if (!$this->edit) { ?>
  <!-- Sickers Search Box -->
  <?php if (empty($giphyApi)) { ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("Gify API key not set!"); ?>
      </span>
    </div>
  <?php } else { ?>
    <div class="comment_emotion_search_container clearfix gif_content">
      <div class="comment_emotion_search_bar">
        <div class="comment_emotion_search_input">
          <input type="text" id="emessages_gif_search" onkeyup="displaygiflist(this.value);"
            placeholder="<?php echo $this->translate("Search GIF"); ?>" autocomplete="off">
        </div>
      </div>

      <div class="comment_emotion_search_content_gif clearfix main_search_category_srn custom_scrollbar">
        <ul class="" id="gify_append"></ul>
      </div>

      <div style="display:none;position:relative;height:255px;" class="main_search_cnt_srn_gif" id="main_search_cnt_srn_gif">
        <div class="activitygifsearch loading_container" style="height:100%;"></div>
      </div>
    </div>
  <?php } ?>

  <?php if (!$this->edit) { ?>
    <script type="application/javascript">
      displaygifseach();
      function displaygifseach() {
        scriptJquery("#gify_append").empty();
        scriptJquery.ajax({
          url: 'https://api.giphy.com/v1/gifs/trending?api_key=<?php echo $giphyApi; ?>&limit=<?php echo $limit; ?>&rating=G',
          method: "GET",
          enctype: 'multipart/form-data',
          data: {},
          success: function (html) {
            if (html['data'].length > 1) {
              scriptJquery("#gify_append").empty();
              for (var i = 0; i < html['data'].length; i++) {
                if (typeof (html['data'][i]['images']['downsized']['url']) != "undefined" && (html['data'][i]['images']['downsized']['url']) != null)
                  scriptJquery("#gify_append").append(
                    '<li rel="' + html['data'][i]['images']['downsized']['url'] + '">' +
                    '<a href="javascript:;" class="_activitygif_gif">' +
                    '<img src="' + html['data'][i]['images']['downsized']['url'] + '" alt="">' +
                    '</a>' +
                    '</li>');
              }
            }
          }
        });
      }
      function displaygiflist(text) {
        if (text.length <= 0) { displaygifseach(); return false; }
        scriptJquery("#main_search_cnt_srn_gif").show();
        scriptJquery(".main_search_category_srn").hide();
        scriptJquery("#gify_append").empty();
        scriptJquery.ajax({
          url: 'https://api.giphy.com/v1/gifs/search?api_key=<?php echo $giphyApi; ?>&q='+text+'&limit=<?php echo $limit; ?>&offset=0&rating=G&lang=en',
          method: "GET",
          enctype: 'multipart/form-data',
          data: {},
          success: function (html) {
            if (html['data'].length > 1) {
              for (var i = 0; i < html['data'].length; i++) {
                if (typeof (html['data'][i]['images']['downsized']['url']) != "undefined" && (html['data'][i]['images']['downsized']['url']) != null)
                  scriptJquery("#gify_append").append(
                    '<li rel="' + html['data'][i]['images']['downsized']['url'] + '">' +
                    '<a href="javascript:;" class="_activitygif_gif">' +
                    '<img src="' + html['data'][i]['images']['downsized']['url'] + '" alt="">' +
                    '</a>' +
                    '</li>');
              }
              scriptJquery("#main_search_cnt_srn_gif").hide();
              scriptJquery(".main_search_category_srn").show();
            }
          }
        });
      }
    </script>
  <?php } ?>

  <?php if (!$this->edit && 0) { ?>
  <?php } ?>
<?php } ?>