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
    <?php echo $this->translate("Quick Links") ?>
  </h3>
  <ul class="admin_home_dashboard_links">
    <li>
      <a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'manage', 'action' => 'index'), 'admin_default', true) ?>" class="links_members">
      <?php echo $this->translate("View Members") ?>  (<?php echo $this->userCount ?>)
      </a>
      
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'report', 'action' => 'index'), 'admin_default', true) ?>" class="links_abuse">
      <?php echo $this->translate("View Abuse Reports") ?>  <?php if( $this->reportCount > 0 ): ?>
      (<?php echo $this->reportCount ?>) <?php endif; ?>
      </a>

    </li>
    <?php if($this->viewer()->isSuperAdmin()) { ?>
      <li>
        <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'packages', 'action' => 'index'), 'admin_default', true) ?>" class="links_plugins">
        <?php echo $this->translate("Manage Plugins") ?> (<?php echo $this->pluginCount ?>)
        </a>
      </li>
    <?php } ?>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'content', 'action' => 'index'), 'admin_default', true) ?>" class="links_layout">
      <?php echo $this->translate("Edit Site Layout") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'themes', 'action' => 'index'), 'admin_default', true) ?>" class="links_theme">
      <?php echo $this->translate("Edit Site Theme") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'stats', 'action' => 'index'), 'admin_default', true) ?>" class="links_stats">
      <?php echo $this->translate("View Statistics") ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->url(array('module' => 'announcement', 'controller' => 'manage', 'action' => 'create'), 'admin_default', true) ?>" class="links_announcements">
      <?php echo $this->translate("Post Announcement") ?>
      </a>
    </li>
  </ul>
</div>
