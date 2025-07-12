<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
* @author     Jung
*/
?>
<?php $imageArray = array('1' => 'elpis-login-signup.png', '2' => 'elpis-post-content.png', '3' => 'elpis-responsive.png', '4' => 'elpis-flexible.png'); ?>
<?php $allParams = $this->allParams; ?>
<div class="core_landingpage_features container no-padding">
  <div class="row justify-content-lg-center">
    <?php for($i=1;$i<=4;$i++) { ?>
      <?php if(!empty($allParams['fe'.$i.'img']) || !empty($allParams['fe'.$i.'heading']) || !empty($allParams['fe'.$i.'description'])) { ?>
        <div class="col-lg-3 col-md-6 col-sm-6">
          <article>
          <?php $image = !empty($allParams['fe'.$i.'img']) ? Engine_Api::_()->core()->getFileUrl($allParams['fe'.$i.'img']) : 'application/modules/Core/externals/images/feature-icons/'.$imageArray[$i]; ?>
          <img src="<?php echo $image; ?>" />
          <?php if(!empty($allParams['fe'.$i.'heading'])) { ?>
            <h3><?php echo $this->translate($allParams['fe'.$i.'heading']); ?></h3>
          <?php } ?>
          <?php if(!empty($allParams['fe'.$i.'description'])) { ?>
            <p><?php echo $this->translate($allParams['fe'.$i.'description']); ?></p>
          <?php } ?>
          </article>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>
