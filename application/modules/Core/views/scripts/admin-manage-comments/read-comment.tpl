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

<div class="global_form_popup">
  <h3><?php echo $this->translate('Comment') ?></h3>
  <p><?php echo $this->partial('_activitycommentcontent.tpl', 'comment', array('comment' => $this->comment)); ?><?php //echo Engine_Text_Emoji::decode($this->comment->body); ?></p>
  <br/>
  <button type="submit" onclick="parent.Smoothbox.close();return false;" name="close_button" value="Close"><?php echo $this->translate("Close"); ?></button>
</div>
