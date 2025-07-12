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

<script type="text/javascript">
  en4.core.runonce.add(function() {
    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'utility', 'action' => 'advertisement'), 'default', true) ?>';
    var processClick = window.processClick = function(adcampaign_id, ad_id) {
      (scriptJquery.ajax({
        dataType: 'json',
        method : 'post',
        url : url,
        data : {
          format : 'json',
          adcampaign_id : adcampaign_id,
          ad_id : ad_id
        }
      }));
    }
  });
</script>
<div class="core_ad_campaingn core_ad_campaingn_<?php echo $this->identity; ?>">
  <?php foreach($this->ads as $ad): ?>
    <div onclick="javascript:processClick(<?php echo $this->campaign->adcampaign_id.", ".$ad->ad_id;?>)">
      <?php echo $ad->html_code; ?>
    </div>
  <?php endforeach; ?>
</div>
<script type="text/javascript">
owlJqueryObject(".core_ad_campaingn_<?php echo $this->identity; ?>").owlCarousel({
  nav:true,
  dots:true,
  margin:0,
  autoHeight:true,
  loop:false,
  items:1,
  <?php 
  $orientation = ($this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr');
  if($orientation == 'rtl') { ?>
    rtl:true,
  <?php  }?>
});
owlJqueryObject(".owl-prev").html('<i class="fa fa-angle-left"></i>');
owlJqueryObject(".owl-next").html('<i class="fa fa-angle-right"></i>');
</script>

