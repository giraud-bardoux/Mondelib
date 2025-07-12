<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */
?>
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.newsupdates')) { ?>
  <?php $url = $this->url(array(), 'core_rss_news'); ?>
  <div id="admin-rss-news" class="admin_home_dashboard">
    <span class="admin_rss_news_spinner"><i class="fas fa-spinner fa-spin fa-lg"></i></span>
  </div>
  <script>
    en4.core.runonce.add(function () {
      var r = scriptJquery.ajax({
        url: '<?php echo $url; ?>',
          method: 'post',
          success: function (res) {
            scriptJquery('#admin-rss-news').html(res);
          }
      });
    });
  </script>
<?php } else { ?>
  <div id="admin-rss-news" class="admin_home_dashboard">
  <div class="tip">
    <span><?php echo $this->translate("Currently, News & Updates are disabled."); ?></span>
  </div>
  </div>
<?php } ?>
