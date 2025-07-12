<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: custom_themes.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class="harmony_styling_buttons">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'harmony', 'controller' => 'settings', 'action' => 'add'), $this->translate("Add New Custom Theme"), array('class' => 'smoothbox harmony_button add_new_theme fa fa-plus', 'id' => 'custom_themes')); ?>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'harmony', 'controller' => 'settings', 'action' => 'add', 'customtheme_id' => $this->customtheme_id), $this->translate("Edit Custom Theme Name"), array('class' => 'smoothbox harmony_button fa fa-pencil', 'id' => 'edit_custom_themes')); ?>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'harmony', 'controller' => 'settings', 'action' => 'delete', 'customtheme_id' => $this->customtheme_id), $this->translate("Delete Custom Theme"), array('class' => 'smoothbox harmony_button fa fa-close', 'id' => 'delete_custom_themes')); ?>
  <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="<?php echo $this->translate('You can not delete this theme, until its deactivated.'); ?>" class="harmony_button fa fa-close disabled" id="deletedisabled_custom_themes" style="display: none;"><?php echo $this->translate("Delete Custom Theme"); ?></a>
</div>
