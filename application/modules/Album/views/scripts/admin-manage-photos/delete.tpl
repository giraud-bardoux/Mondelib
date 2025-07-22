<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Charlotte
 */
?>
<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate("Delete Photo?") ?></h3>
    <p><?php echo $this->translate("Are you sure that you want to delete this photo? It will not be recoverable after being deleted.") ?></p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->album_id?>"/>
      <button type='submit'><?php echo $this->translate("Delete") ?></button>
      <?php echo $this->translate("or") ?>
      <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
      <?php echo $this->translate("cancel") ?></a>
    </p>
  </div>
</form>
