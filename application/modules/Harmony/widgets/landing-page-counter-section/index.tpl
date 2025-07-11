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
<div class="harmony_landingpage_counter">
  <div class="container">
    <?php if($allParams['title']) { ?> 
      <h3 class="harmony_title"><?php echo $this->translate($allParams['title']); ?></h3>
    <?php } ?>
    <ul class="harmony_landingpage_counter_inner">
      <?php for($i=1;$i<=5;$i++) { ?>
        <?php if(!empty($allParams['icon'.$i]) || !empty($allParams['count' .$i]) || !empty($allParams['headinglink' .$i]) || !empty($allParams['text' .$i]) ) { ?>
          <li>
            <?php if(!empty($allParams['icon'.$i])) { ?>
              <div class="harmony_landingpage_counter_inner_icon">
                <i class="<?php echo $allParams['icon'.$i]; ?>"></i>
              </div>
            <?php } ?>
            <?php if(!empty($allParams['count'.$i])) { ?>
              <h4>
                <span class="count" data-count="<?php echo $this->translate($allParams['count'.$i]); ?>">
                 0
                </span> 
                +
              </h4>
            <?php } ?> 
            <?php if(!empty($allParams['text'.$i])) { ?>
              <p>
                <?php echo $this->translate($allParams['text'.$i]); ?> 
              </p>
            <?php } ?> 
          </li>
        <?php } ?>
      <?php } ?>
    </ul>
    <div class="counter_btn">
      <?php if($allParams['btntextlink']) { ?> 
        <a href="<?php echo $this->translate($allParams['btntextlink']); ?>" class="btn btn-primary">
          <?php echo $this->translate($allParams['btntext']); ?>
        </a>
      <?php } ?>
    </div>
  </div>  
</div>  
<script type="text/javascript">
   var counted = 0;
   scriptJquery(window).scroll(function() {
   var oTop = scriptJquery('.harmony_landingpage_counter_inner').offset().top - window.innerHeight;
    if (counted == 0 && scriptJquery(window).scrollTop() > oTop) {
     scriptJquery('.count').each(function() {
       var $this = scriptJquery(this),
         countTo = $this.attr('data-count');
       scriptJquery({
         countNum: $this.text()
       }).animate({
           countNum: countTo
         },
         {
           duration: 5000,
           easing: 'swing',
           step: function() {
             $this.text(Math.floor(this.countNum));
           },
           complete: function() {
             $this.text(this.countNum);
             //alert('finished');
           }
         });
     });
     counted = 1;
    }
  });
</script>
