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
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<ul class="navigation">
  <?php foreach( $this->navigation as $link ): ?>
    <?php
      $explodedString = explode(' ', $link->class);
      $menuName = end($explodedString); 
      $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($menuName); 
      $menuSubArray = $subMenus->toArray();
    ?>
    <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
      <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>">
        <span><?php echo $this->translate($link->getlabel()) ?></span>
      </a>
      <?php if($menuName != 'core_admin_main_plugins' && engine_count($menuSubArray) > 0): ?>
        <ul class="main_menu_submenu">
          <?php foreach( $subMenus as $subMenu): ?>
            <?php
              $subMenuString = explode(' ', $subMenu->class);
              $subMenName = end($subMenuString);
              if($subMenName == 'core_admin_main_manage_packages' && !$this->viewer()->isSuperAdmin()) continue;

              if($subMenName == 'user_admin_phone_messages' && !$settings->getSetting('otpsms.signup.phonenumber', 0)) continue;
            ?>
            <li>
              <a href="<?php echo $subMenu->getHref(); ?>" class="<?php if ($subMenu->getHref() == $_SERVER['REQUEST_URI']): ?> active <?php endif; ?> <?php echo $subMenu->getClass(); ?>"><span><?php echo $this->translate($subMenu->getLabel()); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<script  type="text/javascript">
// Header Spacing
en4.core.runonce.add(function() {
  var height = scriptJquery(".global_header_top").height();
  if(document.getElementById("global_header")) {
    scriptJquery(".global_header_left").css("top", height+"px");
  }
}); 
</script>
