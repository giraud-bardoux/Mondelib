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
    <?php if($this->param == 'approve') { ?>
      <h3><?php echo $this->translate("Approve Photos of this Album?") ?></h3>
      <p>
        <?php echo $this->translate("Are you sure that you want to approve all photos of this album?") ?>
      </p>
      <br />
      <p>
        <input type="hidden" name="confirm" value="<?php echo $this->album_id?>"/>
        <button type='submit' name='album'><?php echo $this->translate("Album Only") ?></button>
        <button type='submit' name="photo"><?php echo $this->translate("Approve Photos") ?></button>
        <?php echo $this->translate("or") ?>
        <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
        <?php echo $this->translate("Cancel") ?></a>
      </p>
    <?php } else { ?>
      <h3><?php echo $this->translate("Unapprove Photos of this Album?") ?></h3>
      <p>
        <?php echo $this->translate("Are you sure that you want to Unapprove all photos of this album?") ?>
      </p>
      <br />
      <p>
        <input type="hidden" name="confirm" value="<?php echo $this->album_id?>"/>
        <button type='submit' name='album'><?php echo $this->translate("Album Only") ?></button>
        <button type='submit' name="photo"><?php echo $this->translate("Unapprove Photos") ?></button>
        <?php echo $this->translate("or") ?>
        <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
        <?php echo $this->translate("Cancel") ?></a>
      </p>
    <?php } ?>
  </div>
</form>
