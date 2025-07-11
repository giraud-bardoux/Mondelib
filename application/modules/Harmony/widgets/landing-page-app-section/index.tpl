<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<?php $allParams = $this->allParams; ?>
<div class="container">
  <div class="harmony_landingpage_app_section">
    <div class="harmony_landingpage_app_left">
      <?php if($allParams['title']) { ?> 
        <h3><?php echo $this->translate($allParams['title']); ?></h3>
      <?php } ?>
      <?php if(!empty($allParams['description'])) { ?>
        <p><?php echo $this->translate($allParams['description']); ?></p>
      <?php } ?> 
      <?php if(!empty($allParams['androidapplink']) || !empty($allParams['iosapplink'])) { ?>
        <div class="harmony_landingpage_app_links">
          <a href="<?php echo $allParams['androidapplink']; ?>" target="_blank"><img src="application/modules/Core/externals/images/android.png" height="44" width="152" alt="Android App"></a>
          <a href="<?php echo $allParams['iosapplink']; ?>"  target="_blank"><img src="application/modules/Core/externals/images/ios.png" height="44" width="152" alt="iOS App"></a>
        </div>
      <?php } ?>
    </div>
    <?php //if(!empty($allParams['apprightimage'])) { ?>
      <div class="harmony_landingpage_app_right">
        <img src="<?php echo $allParams['apprightimage'] ? Engine_Api::_()->core()->getFileUrl($allParams['apprightimage']) : 'application/modules/Harmony/externals/images/mobile-app.png'; ?>" height="467" width="500" alt="Mobile App" />
      </div>
    <?php //} ?>
  </div>
</div>  
