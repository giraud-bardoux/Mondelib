<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _admin_breadcrumb.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php 
  $parentMenu = $this->parentMenu;
  $getParentMenu = Engine_Api::_()->getApi('menus', 'core')->getMenuItem(array('name' => $parentMenu));
  $parentMenuParams = $getParentMenu->params;
  
  $parentMenuItemName = $this->parentMenuItemName;
  $getParentMenuItem = Engine_Api::_()->getApi('menus', 'core')->getMenuItem(array('name' => $parentMenuItemName));
  $parentParams = $getParentMenuItem->params;
  
  $childMenuItemName = $this->childMenuItemName;
  $getChildMenuItem = Engine_Api::_()->getApi('menus', 'core')->getMenuItem(array('name' => $childMenuItemName));
  $childParams = $getChildMenuItem->params;
  
  $lastMenuItemName = $this->lastMenuItemName;
?>
<nav class="breadcrumb_nav" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="<?php echo $this->url(array(), 'admin_default', true); ?>">
        <?php echo $this->translate("Home"); ?>
      </a>
    </li>
    <?php if($parentMenu) { ?>
      <li class="breadcrumb-item active" aria-current="page">
        <?php echo $this->translate($getParentMenu->label); ?>
      </li>
    <?php } ?>
    <?php if(!empty($parentMenuItemName)) { ?>
      <?php if($getParentMenuItem) { ?>
        <li class="breadcrumb-item">
          <a href="<?php echo $this->url(array('module' => $parentParams['module'], 'controller' => $parentParams['controller'], 'action' => @$parentParams['action']), $parentParams['route'], true); ?>"><?php echo $this->translate($getParentMenuItem->label); ?></a>
        </li>
      <?php } else { ?>
        <li class="breadcrumb-item active" aria-current="page">
          <?php echo $this->translate($getParentMenuItem->label); ?>
        </li>
      <?php } ?>
    <?php } ?>
    <?php if(!empty($childMenuItemName)) { ?>
      <li class="breadcrumb-item active" aria-current="page">
        <?php echo $this->translate($getChildMenuItem->label); ?>
      </li>
    <?php } ?>
    <?php if(!empty($lastMenuItemName)) { ?>
      <li class="breadcrumb-item active" aria-current="page">
        <?php echo $this->translate($lastMenuItemName); ?>
      </li>
    <?php } ?>
  </ol>
</nav>
