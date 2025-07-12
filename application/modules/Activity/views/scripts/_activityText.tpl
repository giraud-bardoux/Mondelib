<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activityText.tpl 2024-10-28 00:00:00Z 
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
if (empty($this->enabledModuleNames)) {
  $this->enabledModuleNames = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
}
?>
<?php

$settings = Engine_Api::_()->getApi('settings', 'core');
$pintotop = 1;
if (empty($this->actions)) {
  $actions = array();
} else {
  $actions = $this->actions;
}
$attachmentShowCount = 9;
?>

<?php

if (!$this->getUpdate && ($this->ulInclude)):
  $date = '';
  ?>
  <div class="activity_feed  clearfix">
    <ul class='feed clearfix  <?php echo $this->feeddesign == 2 ? "pinfeed prelative" : ""; ?>' id="activity-feed">
    <?php endif ?>
    <?php
    //google key
    $googleKey = $settings->getSetting('core.mapApiKey', '');
    $languageTranslate = 'en';
    $islanguageTranslate = 1;
    if ($this->isMemberHomePage) {
      $adsEnable = $settings->getSetting('activity.adsenable', 0);
      $peopleymkEnable = $settings->getSetting('activity.peopleymk', 1);
      $adsRepeat = $settings->getSetting('activity.adsrepeatenable', 0);
      $pymkrepeatenable = $settings->getSetting('activity.pymkrepeatenable', 0);
      $adsRepeatTime = $settings->getSetting('activity.adsrepeattimes', 15);
      $peopleymkrepeattimes = $settings->getSetting('activity.peopleymkrepeattimes', 5);
      $contentCount = $this->contentCount;
    }

    if (defined('SESCOMMUNITYADS')) {
      $communityAdsEnable = $settings->getSetting('sescommunityads_advertisement_enable', 1);
      $communityAdsDisplay = $settings->getSetting('sescommunityads_advertisement_display', 3);
      $communityAdsDisplayFeed = $settings->getSetting('sescommunityads_advertisement_displayfeed', 1);
      if (!$this->isMemberHomePage && !$communityAdsDisplayFeed)
        $communityAdsEnable = 0;
      $communityAdsDisplayAds = $settings->getSetting('sescommunityads_advertisement_displayads', 5);
    }

    foreach ($actions as $action): //(goes to the end of the file)
      //communityads
      if (@$communityAdsEnable && ($contentCount && $contentCount % $communityAdsDisplayAds == 0)) { ?>
        <li class="clearfix activity_community_ads activity_pinfeed_hidden _photo<?php echo $this->userphotoalign; ?>">
          <?php
          $valueAds['communityAdsDisplay'] = $communityAdsDisplay;
          $valueAds['communityadsIds'] = $this->communityadsIds;
          include ('application/modules/Sescommunityads/views/scripts/_activityAds.tpl');
          ?>
        </li>
        <?php
      }


      //google ads code start here
      if ($this->isMemberHomePage && $adsEnable && ($contentCount && $contentCount % $adsRepeatTime == 0) && ($adsRepeat || (!$adsRepeat && $contentCount / $adsRepeatTime == 1))) {
        ?>
        <?php 
        $adcampaignid = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adcampaignid','0');
        if( $adcampaignid) {
        $campaign = Engine_Api::_()->getItem('core_adcampaign', $adcampaignid);
        if($campaign->isActive()) {
        ?>
        <li class="clearfix activity_pinfeed_hidden activity_ads_camp">
          <div class="block">
            <?php
              $content = $this->content()->renderWidget('activity.ad-campaign');
              echo preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content)
            ?>
          </div>
          <script type="application/javascript">
            en4.core.runonce.add(function () {
              var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'utility', 'action' => 'advertisement'), 'default', true) ?>';
              var processClick = window.processClick = function (adcampaign_id, ad_id) {
                (scriptJquery.ajax({
                  'format': 'json',
                  'url': url,
                  'data': {
                    'format': 'json',
                    'adcampaign_id': adcampaign_id,
                    'ad_id': ad_id
                  }
                }))
              }
            });
          </script>
        </li>
       
        <?php } }
      }

      //google ads code end here
      try { // prevents a bad feed item from destroying the entire page
        // Moved to controller, but the items are kept in memory, so it should not hurt to double-check
        if (!$action->getTypeInfo()->enabled)
          continue;
        if (!$action->getSubject() || !$action->getSubject()->getIdentity())
          continue;
        if (!$action->getObject() || !$action->getObject()->getIdentity())
          continue;

        ob_start();
        ?>
        <?php if ($this->isOnThisDayPage) { ?>
          <?php if ($date != $action->date) { ?>
            <li class="onthisday">
              <?php
              $date1 = date_create(date('Y-m-d', strtotime($action->date)));
              $date2 = date_create(date('Y-m-d'));
              $date_diff = date_diff($date1, $date2);
              if ($date_diff == 1)
                $year = 'YEAR';
              else
                $year = 'YEARS';
              echo $date_diff->y . " " . $year . " AGO TODAY";
              ?>
            </li>
          <?php } ?>
          <?php $date = $action->date; ?>
        <?php } ?>
        <?php include ('application/modules/Activity/views/scripts/_activity.tpl'); ?>

        <?php
        @$contentCount++;
        ob_end_flush();
      } catch (Exception $e) {
        ob_end_clean();
        if (APPLICATION_ENV === 'development') {
          echo $e->__toString();
        }
      }
      ;
    endforeach;
    ?>

    <?php if (!$this->getUpdate && ($this->ulInclude)): ?>
    </ul>
  </div>
<?php endif ?>