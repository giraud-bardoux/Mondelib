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

<div class="harmony_landingpage_why_choose">
  <div class="harmony_landingpage_why_choose_inner">
    <?php //if(!empty($allParams['leftphoto'])) { ?>
      <div class="harmony_landingpage_why_choose_left" style="background-image:url(<?php echo $allParams['leftphoto'] ? Engine_Api::_()->core()->getFileUrl($allParams['leftphoto']) : 'application/modules/Harmony/externals/images/whychoose/whychoosebg.jpg'; ?>);">
      </div> 
    <?php //} ?>
    <div class="harmony_landingpage_why_choose_right">
      <?php if($allParams['title']) { ?> 
        <h3 class="harmony_title"><?php echo $this->translate($allParams['title']); ?></h3>
      <?php } ?>
      <?php for($i=1;$i<=3;$i++) { ?>
        <?php if(!empty($allParams['featuresheading' .$i]) || !empty($allParams['headinglink' .$i]) || !empty($allParams['description' .$i]) ) { ?>
          <div class="harmony_landingpage_features_item">
            <?php //if(!empty($allParams['photo'.$i])) { ?>
              <div class="harmony_landingpage_features_item_icon">
                <img src="<?php echo $allParams['photo'.$i] ? Engine_Api::_()->core()->getFileUrl($allParams['photo'.$i]) : 'application/modules/Harmony/externals/images/whychoose/'.$i.'.png'; ?>" height="36" width="36" alt="img"/>
              </div>
            <?php //} ?>
            <div class="harmony_landingpage_features_item_cont">
              <?php if(!empty($allParams['featuresheading'.$i])) { ?>
                <h4>
                  <?php echo $this->translate($allParams['featuresheading'.$i]); ?> 
                </h4>
              <?php } ?> 
              <?php if(!empty($allParams['description'.$i])) { ?>
                <p>
                  <?php echo $this->translate($allParams['description'.$i]); ?> 
                </p>
              <?php } ?> 
          </div>  
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>  
</div>  
