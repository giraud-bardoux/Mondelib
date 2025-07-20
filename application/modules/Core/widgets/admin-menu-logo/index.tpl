<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) { ?> 
  <?php $lightLogo = Engine_Api::_()->getApi('settings', 'core')->getSetting("acppro_admin_logo",'') ? Engine_Api::_()->getApi('settings', 'core')->getSetting("acppro_admin_logo",'') : $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/logo.png';  ?>
  <?php $darkLogo = Engine_Api::_()->getApi('settings', 'core')->getSetting("acppro_admin_logocontrast",'') ? Engine_Api::_()->getApi('settings', 'core')->getSetting("acppro_admin_logocontrast",'') : $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/logo-light.png'; ?>
<?php } else { ?>
  <?php $darkLogo = $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/logo-light.png'; ?>
  <?php $lightLogo = $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/logo.png'; ?>
<?php } ?>
<div id='global_header_logo'>
  <a href='<?php echo $this->url(array(), 'admin_default', true) ?>'>
    <?php if(!empty($_COOKIE['adminmode_theme']) && $_COOKIE['adminmode_theme'] == 'dark'):?>
      <?php echo $this->htmlImage($darkLogo, $this->translate('SocialEngine Control Panel')) ?>
    <?php else: ?>
      <?php echo $this->htmlImage($lightLogo, $this->translate('SocialEngine Control Panel')) ?>
    <?php endif; ?>
  </a>
</div>


