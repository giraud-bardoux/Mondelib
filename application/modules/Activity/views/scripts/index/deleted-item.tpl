<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: deleted-item.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<script type="text/javascript">
  parent.document.getElementById('activity-item-<?php echo $this->action_id ?>').destroy();
  setTimeout(function () {
    parent.Smoothbox.close();
  }, <?php echo ($this->smoothboxClose === true ? 1000 : $this->smoothboxClose); ?>);
</script>


<div class="global_form_popup_message">
  <?php echo $this->message ?>
</div>