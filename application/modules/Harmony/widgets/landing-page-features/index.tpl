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
<div class="harmony_landingpage_features container">
  <?php if ($allParams['title']) { ?>
    <h3 class="harmony_title"><?php echo $this->translate($allParams['title']); ?></h3>
  <?php } ?>
  <div class="row">
    <div class="col-md-5">
      <div class="harmony_landingpage_features_left">
        <img src="<?php echo Engine_Api::_()->core()->getFileUrl($allParams['leftphoto']) ? Engine_Api::_()->core()->getFileUrl($allParams['leftphoto']) : 'application/modules/Harmony/externals/images/features/featuresleft.png'; ?>" height="477" width="515" alt="Features Image" />
      </div>
    </div>
    <div class="col-md-7">
      <div class="row">
        <?php for ($i = 1; $i <= 4; $i++) { ?>
          <?php if (!empty($allParams['featuresheading' . $i]) || !empty($allParams['headinglink' . $i]) || !empty($allParams['description' . $i])) { ?>
            <div class="col-md-6 harmony_landingpage_features_inner">
              <a <?php if (!empty($allParams['link' . $i])) { ?> href="<?php echo $allParams['link' . $i]; ?>" <?php } else { ?> href="javascript:void(0);" <?php } ?>>
                  <figure>
                    <img src="<?php echo $allParams['photo' . $i] ? Engine_Api::_()->core()->getFileUrl($allParams['photo' . $i]) : 'application/modules/Harmony/externals/images/features/' . $i . '.png'; ?>" height="32" width="32" alt="Features Icon" />
                  </figure>
                  <?php if (!empty($allParams['featuresheading' . $i])) { ?>
                    <h4>
                      <?php echo $this->translate($allParams['featuresheading' . $i]); ?>
                    </h4>
                  <?php } ?>
                  <?php if (!empty($allParams['description' . $i])) { ?>
                    <p>
                      <?php echo $this->translate($allParams['description' . $i]); ?>
                    </p>
                  <?php } ?>
              </a>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>