<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: requireuser.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php if( $this->form ): ?>
  <div class="required_login_form"><?php echo $this->form->render($this); ?></div>
 <?php include APPLICATION_PATH .  '/application/modules/User/views/scripts/auth/_loginByOtp.tpl';?>
<?php else: ?>
  <?php echo $this->translate('Please sign in to continue.'); ?>
<?php endif; ?>
