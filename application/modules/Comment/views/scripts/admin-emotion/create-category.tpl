<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: create-category.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js'); ?>
<script>
  hashSign = '#';
</script>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
