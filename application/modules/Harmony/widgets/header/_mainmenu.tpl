<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _mainmenu.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $countMenu = 0; ?>
<ul class="harmony_main_menu_nav navigation " id="navigation_menu">
  <?php foreach( $this->navigation as $link ): ?>
    <?php if( $countMenu < $this->menuCount ): ?>
      <?php 
        $explodedString = explode(' ', $link->class);
        $menuName = end($explodedString); 
        $moduleName = str_replace('core_main_', '', $menuName);
        if(strpos($moduleName, 'custom_') !== 0){
          $moduleName = $moduleName.'_main';
        }
      ?>
      <?php $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName); 
        $menuSubArray = $subMenus->toArray();
      ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
          <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
            <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
          <span><?php echo $this->translate($link->getlabel()) ?></span>
          <?php if(count($menuSubArray) > 0 && $this->submenu): ?>
            <i class="fa fa-angle-down"></i>
          <?php endif; ?>
        </a>
      </li>
    <?php else:?>
      <?php break;?>
    <?php endif;?>
    <?php $countMenu++;?>
  <?php endforeach; ?>
  <?php if (count($this->navigation) > $this->menuCount):?>
    <?php $countMenu = 0; ?>
    <li class="more_tab">
      <a href="javascript:void(0);" class="menu_core_main menu_core_main_more">
        <i class="fi fi-rr-square-plus"></i>
        <span><?php echo $this->translate("More") ?></span>
      </a>
      <ul class="main_menu_submenu">
        <?php foreach( $this->navigation as  $link ): ?>
          <?php if ($countMenu >= $this->menuCount): ?>
            <?php 
              $explodedString = explode(' ', $link->class);
              $menuName = end($explodedString); 
              $moduleName = str_replace('core_main_', '', $menuName);
              if(strpos($moduleName, 'custom_') !== 0){
                $moduleName = $moduleName.'_main';
              }
            ?>
            <?php 
              $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName);
              $menuSubArray = $subMenus->toArray();
            ?>
            <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
              <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                <span><?php echo $this->translate($link->getlabel()) ?></span>
              </a>
            </li>
          <?php endif;?>
          <?php $countMenu++;?>
        <?php endforeach; ?>
      </ul>
    </li>
  <?php endif;?>
</ul>

<script>
  scriptJquery(function() {
    scriptJquery(".menu_core_main_more *").on("click", function(e) {
      scriptJquery(".main_menu_submenu").toggleClass("showmenu");
    });
    AttachEventListerSE("click", function(e) {
      if (scriptJquery(e.target).is(".main_menu_submenu, .menu_core_main_more  *") === false) {
        scriptJquery(".main_menu_submenu").removeClass("showmenu");
      }
    });
  });
</script>
