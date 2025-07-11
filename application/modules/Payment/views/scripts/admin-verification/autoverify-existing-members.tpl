<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: update-member-level.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div style="margin:20px 10px 0 10px;" >
  <form method="post" class="global_form" enctype="application/x-www-form-urlencoded">
    <h3><?php echo $this->translate("Verify Existing Users"); ?></h3>
    <p class="form-description">
      <?php echo $this->translate("Do you want to verify existing users of this member level?");?>
    </p>
    <div class="form-elements">
      <div id="buttons-wrapper" class="form-wrapper">
        <fieldset id="fieldset-buttons">
          <button type="submit" id="submit" name="submit"><?php echo $this->translate("Yes");?></button>
          <?php echo $this->translate("or");?>
          <a onclick="window.parent.location.reload();" href="javascript:void(0);" type="button" id="cancel" name="cancel">
              <?php echo $this->translate("No");?>
          </a>
        </fieldset>
      </div>
      <input type="hidden" id="level_id" value="<?php echo $this->level_id;?>" name="level_id">
    </div>
  </form>
</div>
