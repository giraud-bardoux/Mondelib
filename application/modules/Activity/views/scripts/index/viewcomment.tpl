<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: viewcomment.tpl 2024-10-28 00:00:00Z 
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

<?php if (!isset($this->form))
  return; ?>
<?php echo $this->translate("Comment:") ?>
<?php echo $this->form->render($this) ?>

<script type="text/javascript">
  //<![CDATA[
  document.getElementsByTagName('form')[0].style.display = 'block';
  //]]>
</script>