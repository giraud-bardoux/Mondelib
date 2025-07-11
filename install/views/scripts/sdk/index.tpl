<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<div class="sdk">
  <h3><?php echo $this->translate("SocialEngine SDK")?></h3>
  <p><?php echo $this->translate("The SocialEngine PHP SDK allows you to create packages for distribution.")?></p>
  <p><?php echo $this->translate("More info: ")?><a href="https://community.socialengine.com/blogs/597/99/socialengine-sdk" target="_blank"><?php echo $this->translate("See KB article")?></a>.</p>

  <ul class="sdk_inner">
    <li>
      <a class="buttonlink sdk_packages_add" href="<?php echo $this->url(array('action' => 'create')) ?>">
        <?php echo $this->translate("Create a Package")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Sets up bare-bones modules, widgets, and more for your local development
        environment.")?>
      </p>
    </li>
    <li>
      <a class="buttonlink sdk_packages_build" href="<?php echo $this->url(array('action' => 'build')) ?>">
        <?php echo $this->translate("Build Packages")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Turns your packages into files that are installable and ready for
        distribution.")?>
      </p>
    </li>
    <li>
      <a class="buttonlink sdk_packages_manage" href="<?php echo $this->url(array('action' => 'manage')) ?>">
        <?php echo $this->translate("Manage Package Files")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Download package files you've built, combine packages, or delete ones you
        don't want.")?>
      </p>
    </li>
  </ul>
</div>
