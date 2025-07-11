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
<?php if($this->bgimage): ?>
  <?php $photo = Engine_Api::_()->core()->getFileUrl($this->bgimage); ?>
  <style>
  .layout_core_page_background_image{
    display:none !important;
  }
  #global_footer{
    margin-top:0px !important;
    padding-top:0 !important;
  }
  #global_wrapper{
    background-image:url(<?php echo $photo?>);
    background-size:cover;
    background-position:center;
    background-attachment:fixed;
  }
  .layout_page_footer{
    margin-top:0;
  }
  </style>
<?php endif; ?>
