<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _mainmenumobile.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class="mobile_navigation">
   <a href="javascript:void(0)" class="mobile_menu">
      <i class="fas fa-bars"></i>
   </a>
   <a href="javascript:void(0)" class="mobile_menu mobile_menu_close">
      <i class="fas fa-times"></i>
   </a>
  <ul class="mobile_navigation_menu navigation">
    <?php foreach( $this->navigation as $link ): ?>
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
    <?php endforeach; ?>
  </ul>
</div>  

<script type="text/javascript">
   // Main Menu Toggle
    en4.core.runonce.add(function() {
      scriptJquery(".mobile_navigation .mobile_menu").click(function () {
        if(scriptJquery(".mobile_navigation .mobile_navigation_menu").hasClass('header-nav-open')){
          scriptJquery(".mobile_navigation .mobile_navigation_menu").removeClass('header-nav-open')
        }
        else{
          scriptJquery(".mobile_navigation .mobile_navigation_menu").addClass('header-nav-open');
        }
      });
    });
    scriptJquery(document).click(function(event) {
      if (!scriptJquery(event.target).closest(".mobile_navigation .mobile_navigation_menu,.mobile_navigation .mobile_menu").length) {
        scriptJquery("html").find(".mobile_navigation .mobile_navigation_menu").removeClass("header-nav-open");
         scriptJquery("html").find("body").removeClass("header_body_open");
      }
    });
    en4.core.runonce.add(function() {
      scriptJquery(".mobile_navigation .mobile_menu").click(function () {
        if(scriptJquery("body").hasClass('header_body_open')){
          scriptJquery("body").removeClass('header_body_open')
        }else{
          scriptJquery("body").addClass('header_body_open');
        }
      });
    });    
</script>    