<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: invite.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<div class="signup_form" id="invite_form">
  
  <?php echo $this->form->render($this) ?>

</div>
<script type='text/javascript'>
  //en4.core.runonce.add(function() {
    if(scriptJquery("#global_wrapper").hasClass('signup_subscriptions_plans'))
      scriptJquery("#global_wrapper").removeClass('signup_subscriptions_plans');
  //});
</script>
