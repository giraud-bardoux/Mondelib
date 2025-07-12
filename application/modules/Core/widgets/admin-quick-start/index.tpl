<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2021 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9905 2021-11-09 $
* @author     John
*/
?>

<div class="admin_home_dashboard_item">
  <h3 class="header_section">
    <?php echo $this->translate("Quick Start") ?>
  </h3>
  <ul class="admin_home_dashboard_links">
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'settings', 'action' => 'general'), 'admin_default', true) ?>" class="links_plugins">
      <?php echo $this->translate("Set Site Title") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'mail', 'controller' => 'settings', 'action' => 'settings'), 'admin_default', true) ?>" class="links_abuse">
      <?php echo $this->translate("Set up Mail") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'signup', 'action' => 'index'), 'admin_default', true) ?>" class="links_layout">
      <?php echo $this->translate("Set up Registration") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'settings', 'action' => 'spam'), 'admin_default', true) ?>" class="links_theme">
      <?php echo $this->translate("Enable Anti-Spam") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'authorization', 'controller' => 'level', 'action' => 'index'), 'admin_default', true) ?>" class="links_stats">
      <?php echo $this->translate("Set up Member Levels") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'iframely', 'action' => 'index'), 'admin_default', true) ?>" class="links_members">
      <?php echo $this->translate("Set Iframely for links") ?>
      </a>
    </li>
  </ul>
</div>
