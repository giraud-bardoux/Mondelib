<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: comment.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
/**
 * This view script is only visible when using captcha on the comment form.
 */
?>
<p><?php echo $this->message ?></p>
<script type="text/javascript">
//<![CDATA[
parent.en4.activity.viewComments(<?php echo $this->action_id ?>);
parent.Smoothbox.close();
//]]>
</script>
