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
<div class="harmony_landing_page_banner">
  <div class="container">
    <div class="row harmony_landing_page_banner_inner">
      <div class="col-md-6">
        <div class="harmony_landing_page_banner_left">
          <?php if($allParams['title']) { ?> 
            <h1><?php echo $this->translate($allParams['title']); ?></h1>
          <?php } ?>
            <?php if(!empty($allParams['description'])) { ?>
            <p>
              <?php echo $this->translate($allParams['description']); ?> 
            </p>
          <?php } ?> 
          <?php if(!empty($allParams['btntextlink'])) { ?>
            <a href="<?php echo $allParams['btntextlink']; ?>" class="btn btn-primary">
              <span><?php echo $allParams['btntext']; ?></span>
              <i class="fas fa-chevron-right"></i>
            </a>
          <?php } ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="harmony_landing_page_banner_right" style="height:<?php echo $allParams['height'] ? $allParams['height'] : '450'; ?>px;">
          <?php //if((isset($allParams['photo1'])) || (isset($allParams['photo2']) ) || (isset($allParams['photo3']))) { ?>
          <?php for($i=1;$i<=3;$i++) { ?>
            <?php $photo = Engine_Api::_()->core()->getFileUrl($allParams['photo'.$i]); ?>
              <?php //if($photo) { ?>
                <div class="harmony_landing_page_banner_right_inner <?php echo $i; ?>">
                  <div class="harmony_landing_page_banner_right_img">
                    <img src="<?php echo $photo ? $photo : 'application/modules/Harmony/externals/images/bannerright'.$i.'.png'; ?>" height="1162" width="207" alt="banner img"> 
                  </div>
                  <div class="harmony_landing_page_banner_right_bg" style="background-image: url(<?php echo $photo ? $photo : 'application/modules/Harmony/externals/images/bannerright'.$i.'.png'; ?>);">
                  </div>
                  <div class="harmony_landing_page_banner_right_bg bg_copy" style="background-image: url(<?php echo $photo ? $photo : 'application/modules/Harmony/externals/images/bannerright'.$i.'.png'; ?>);">
                  </div>
                </div>
            <?php //} ?>
          <?php } ?>
          <?php //} ?>
        </div>
      </div>
    </div>
  </div>
</div>
