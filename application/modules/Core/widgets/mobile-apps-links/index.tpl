<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<ul class="core_mobile_app_link">
  <?php if(!empty($this->androidlink)) { ?>
    <li>
      <a <?php if(empty($this->mobile)) { ?> target="_blank" <?php } ?> href="<?php echo $this->androidlink; ?>">
        <img src="./application/modules/Core/externals/images/android.png" height="39" width="130" alt="Android Image">
      </a>
    </li>
  <?php } ?>
  <?php if(!empty($this->iOSlink)) { ?>
    <li>  
      <a target="_blank" href="<?php echo $this->iOSlink; ?>">
        <img src="./application/modules/Core/externals/images/ios.png" height="39" width="130" alt="Ios Image">
      </a>
    </li>
  <?php } ?>
</ul>
