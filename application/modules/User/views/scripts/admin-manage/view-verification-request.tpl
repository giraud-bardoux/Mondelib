<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: stats.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>

<div class="global_form_popup admin_member_stats">
  <h3><?php echo $this->translate('Message') ?></h3>
  <p><?php echo $this->verificationrequest->message; ?></p>
  <br/>
  <button type="submit" onclick="parent.Smoothbox.close();return false;" name="close_button" value="Close"><?php echo $this->translate("Close"); ?></button>
</div>
