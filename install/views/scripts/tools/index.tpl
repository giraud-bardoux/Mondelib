<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>
<div class="other_tools_main">
<h3><?php echo $this->translate("Other Tools")?></h3>
  <ul class="other_tools">
    <li>
      <a class="buttonlink other_tools_check" href="<?php echo $this->url(array('action' => 'sanity')) ?>">
        <?php echo $this->translate("Requirement and Dependency Check")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Double check the requirements and dependencies of your installed packages.")?>
      </p>
    </li>
    
    <li>
      <a class="buttonlink other_tools_search" href="<?php echo $this->url(array('action' => 'compare')) ?>">
        <?php echo $this->translate("Search for Modified Files")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Lists files that have been modified since installation. You can view a
        side-by-side diff of the files if you upload the original package.")?>
      </p>
    </li>

    <li>
      <a class="buttonlink other_tools_php" href="<?php echo $this->url(array('action' => 'php')) ?>">
        <?php echo $this->translate("PHP Info")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Displays the results of the phpinfo() function.")?>
      </p>
    </li>

    <li>
      <a class="buttonlink other_tools_log" href="<?php echo $this->url(array('action' => 'log')) ?>">
        <?php echo $this->translate("Log Browser")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Allows viewing of error logs.")?>
      </p>
    </li>

    <?php if( $this->hasAdminer ): ?>
    <li>
      <a class="buttonlink" href="<?php echo $this->url(array('action' => 'adminer')) ?>/">
        <?php echo $this->translate("Adminer")?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate("Adminer is a MySQL database management utility, similar to phpMyAdmin.")?>
      </p>
    </li>
    <?php endif; ?>
  </ul>
</div>