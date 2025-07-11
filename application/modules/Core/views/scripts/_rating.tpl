<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _rating.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php 
  $module = $this->module; 
  $param = $this->param; 
  $viewer_id = $this->viewer()->getIdentity();
  $item = $this->item;
  $notificationType = $this->notificationType;

  $ratingTable = Engine_Api::_()->getDbTable('ratings', 'core');
  $settings = Engine_Api::_()->getApi('settings', 'core');

  $rating_count = $ratingTable->ratingCount(array('resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()));

  $rating_text = $this->translate(array('%s rating', '%s ratings', $rating_count),$this->locale()->toNumber($rating_count));
  
  $rated = $ratingTable->checkRated(array('resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()));

  $this->headTranslate(array('you already rated', 'please login to rate', 'click to rate','Thanks for rating!'));

  $ratingIcon = $settings->getSetting($module.'.ratingicon', 'fas fa-star');
?>
<?php if($settings->getSetting($module.'.enable.rating', 1)) { ?>
  <script type="text/javascript">
    var modulename = '<?php echo $module; ?>';
    var notificationType = '<?php echo $notificationType; ?>';
    var pre_rate = <?php echo $item->rating;?>;
    var rated = '<?php echo $rated;?>';
    var resource_type = '<?php echo $item->getType();?>';
    var resource_id = <?php echo $item->getIdentity();?>;
    var total_votes = <?php echo $rating_count;?>;
    var viewer = <?php echo $viewer_id;?>;
    new_text = '';
    var rating_text = "<?php echo $rating_text ?>";
    var ratingIcon = "<?php echo $ratingIcon ? $ratingIcon : 'fas fa-star'; ?>";
  </script>

  <?php if($param == 'create') { ?>
    <div class="rating rating_star_big" onmouseout="rating_out();">
      <span id="rate_1" class="rating_star_big_generic <?php echo $ratingIcon; ?>" <?php if (!$rated && $viewer_id):?> onclick="rate(1);"<?php endif; ?> onmouseover="rating_over(1);"></span>
      <span id="rate_2" class="rating_star_big_generic <?php echo $ratingIcon; ?>" <?php if (!$rated && $viewer_id):?> onclick="rate(2);"<?php endif; ?> onmouseover="rating_over(2);"></span>
      <span id="rate_3" class="rating_star_big_generic <?php echo $ratingIcon; ?>" <?php if (!$rated && $viewer_id):?> onclick="rate(3);"<?php endif; ?> onmouseover="rating_over(3);"></span>
      <span id="rate_4" class="rating_star_big_generic <?php echo $ratingIcon; ?>" <?php if (!$rated && $viewer_id):?> onclick="rate(4);"<?php endif; ?> onmouseover="rating_over(4);"></span>
      <span id="rate_5" class="rating_star_big_generic <?php echo $ratingIcon; ?>" <?php if (!$rated && $viewer_id):?> onclick="rate(5);"<?php endif; ?> onmouseover="rating_over(5);"></span>
      <span id="rating_text" class="rating_text"><?php echo $this->translate('click to rate');?></span>
    </div>
  <?php } else if($param == 'show') { ?>
    <span class="star_rating_wrapper rating_star_show">
      <?php for( $x=1; $x <= $item->rating; $x++ ): ?>
        <span class="rating_star_generic rating_star <?php echo $ratingIcon; ?>"></span>
      <?php endfor; ?>
      <?php if( (round($item->rating) - $item->rating) > 0): ?>
        <span class="rating_star_generic rating_star_half <?php echo $ratingIcon; ?>"></span>
      <?php endif; ?>
      <?php for( $x=5; $x > round($item->rating); $x-- ): ?>
        <span class="rating_star_generic rating_star_empty <?php echo $ratingIcon; ?>"></span>
      <?php endfor; ?>
    </span>
  <?php } ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      set_rating();
    });
  </script>
<?php } ?>
