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
<div class="harmony_landingpage_service container">
  <?php if ($allParams['title']) { ?>
    <h3 class="harmony_title"><?php echo $this->translate($allParams['title']); ?></h3>
  <?php } ?>
  <div class="row">
    <?php for ($i = 1; $i <= 8; $i++) { ?>
      <?php if (!empty($allParams['icon' . $i]) || !empty($allParams['featuresheading' . $i]) || !empty($allParams['headinglink' . $i]) || !empty($allParams['description' . $i])) { ?>
        <div class="col-md-3 harmony_landingpage_service_inner">
          <a <?php if (!empty($allParams['link' . $i])) { ?> href="<?php echo $allParams['link' . $i]; ?>" <?php } else { ?> href="javascript:void(0);" <?php } ?>>
              <?php if (!empty($allParams['icon' . $i])) { ?>
                <div class="harmony_landingpage_service_icon">
                  <i class="<?php echo $allParams['icon' . $i]; ?>"></i>
                </div>
              <?php } ?>
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