<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: delete.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>

<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate("Delete This Custom Theme?") ?></h3>
    <p><?php echo $this->translate("Are you sure that you want to delete this custom theme? It will not be recoverable after being deleted.") ?></p>
    <br />
    <p>
      <input type="hidden" name="confirm" />
      <button type='submit'><?php echo $this->translate("Delete") ?></button>
      <?php echo $this->translate(" or ") ?>
      <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate("Cancel") ?></a>
    </p>
  </div>
</form>
