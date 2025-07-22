<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Event/views/scripts/_adminHeader.tpl';?>
<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('input[type=radio][name=event_enable_rating]:checked').trigger('change');
  });
  
  function showHideRatingSetting(value) {
    if(value == 1) {
      scriptJquery('#event_ratingicon-wrapper').show();
    } else {
      scriptJquery('#event_ratingicon-wrapper').hide();
    }
  }
</script>
