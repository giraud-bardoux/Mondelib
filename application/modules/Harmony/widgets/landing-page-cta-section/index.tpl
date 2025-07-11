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
  <div class="harmony_landingpage_cta_section">
    <div class="harmony_landingpage_cta_left">
      <?php if($allParams['title']) { ?> 
        <h3><?php echo $this->translate($allParams['title']); ?></h3>
      <?php } ?>
      <?php if(!empty($allParams['description'])) { ?>
        <p>
          <?php echo $this->translate($allParams['description']); ?> 
        </p>
      <?php } ?> 
    </div>
    <div class="harmony_landingpage_cta_right">
      <?php if(!empty($allParams['btntextlink'])) { ?>
        <a href="<?php echo $allParams['btntextlink']; ?>" class="_btn">
          <span><?php echo $allParams['btntext']; ?></span>
          <i class="fas fa-angle-double-right"></i>
        </a>
      <?php } ?>
    </div>
  </div>    
</div>  
