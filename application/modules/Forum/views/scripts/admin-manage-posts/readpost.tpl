<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: readpost.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<div class="details_popup">
  <div class="details_popup_header">
    <?php echo $this->translate("View Post"); ?>
  </div>
  <div class="details_popup_content"> 
    <?php echo $this->forumPost->body; ?>
  </div>
</div>