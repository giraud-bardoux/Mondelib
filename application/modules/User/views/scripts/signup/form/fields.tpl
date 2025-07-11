<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: fields.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array(
    'topLevelId' => $this->form->getTopLevelId(),
    'topLevelValue' => $this->form->getTopLevelValue(),
  ));
?>
<div class="signup_form user_signup_details">
  <?php echo $this->form->render($this) ?>
</div>
<script type='text/javascript'>
  //en4.core.runonce.add(function() {
    if(scriptJquery("#global_wrapper").hasClass('signup_subscriptions_plans'))
      scriptJquery("#global_wrapper").removeClass('signup_subscriptions_plans');
  //});

  setTimeout(function() {
    changeFields(null, null, true);
  }, 200);
</script>
