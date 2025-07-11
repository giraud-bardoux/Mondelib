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
<?php if(empty($this->bgphoto)) { ?>
  <?php $bgphoto = 'application/modules/Core/externals/images/parallax-bg.jpg'; ?>
<?php } else { ?>
  <?php $bgphoto = Engine_Api::_()->core()->getFileUrl($this->bgphoto); ?>
<?php } ?>
<div class="core_parallax" style="height:<?php echo $this->height; ?>px;">
  <div class="core_parallax_inner" style="height:<?php echo $this->height; ?>px;background-image:url(<?php echo $bgphoto; ?>);">
    <?php if($this->heading) { ?>
    <div class="container no-padding">
      <div class="row justify-content-lg-center">
        <div class="core_parallax_caption"> <?php echo $this->translate($this->heading); ?> </div>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
