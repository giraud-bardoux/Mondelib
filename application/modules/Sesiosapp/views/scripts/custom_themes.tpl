<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: custom_themes.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<div class="sesiosapp_styling_buttons">
<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesiosapp', 'controller' => 'admin-theme', 'action' => 'add-custom-theme'), $this->translate("Add New Custom Theme"), array('class' => 'smoothbox theme_button', 'id' => 'custom_themes')); ?>
<?php //if($this->customtheme_id): ?>
	<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesiosapp', 'controller' => 'admin-theme', 'action' => 'add-custom-theme'), $this->translate("Edit Custom Theme Name"), array('class' => 'seschangeThemeName theme_button sesiosapp_icon_edit', 'id' => 'edit_custom_themes')); ?>
	<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesiosapp', 'controller' => 'admin-theme', 'action' => 'delete-custom-theme'), $this->translate("Delete Custom Theme"), array('class' => 'theme_button', 'id' => 'delete_custom_themes')); ?>
	<a href="javascript:void(0);" class="theme_button disabled" id="deletedisabled_custom_themes" style="display: none;"><?php echo $this->translate("Delete Custom Theme"); ?></a>
<?php //endif; ?>
</div>