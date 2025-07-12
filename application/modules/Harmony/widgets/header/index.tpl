<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<header class="harmony_header">
	<div class="container harmony_header_inner">
    	<div class="header_left">
        <?php if(engine_in_array('logo',$this->options)){ ?>
          <div class="header_logo">
            <?php echo $this->content()->renderWidget("core.menu-logo",array("logo"=>$this->logo)); ?>
          </div>
          <?php if($this->accessibility_option){ ?>
             <div class="header_logo_contrast">
               <?php echo $this->content()->renderWidget("core.menu-logo",array("logo"=>$this->logocontrast)); ?> 
            </div>
          <?php } ?>    
        <?php } ?>

        <?php if(engine_in_array('search',$this->options)){ ?>
          <div class="header_search">
            <?php echo $this->content()->renderWidget("core.search-mini"); ?>
          </div>
        <?php } ?>
      </div>
      <div class="header_right">
        <?php if(engine_in_array('mainMenu',$this->options)){ ?>
          <div class="harmony_main_menu">
            <?php include APPLICATION_PATH . '/application/modules/Harmony/widgets/header/_mainmenu.tpl'; ?>
            <?php include APPLICATION_PATH . '/application/modules/Harmony/widgets/header/_mainmenumobile.tpl'; ?>
          </div>
        <?php } ?>
        <?php if(engine_in_array('miniMenu',$this->options)){ ?>
          <div class="header_menu_mini">
            <?php echo $this->content()->renderWidget("core.menu-mini"); ?>
          </div>
        <?php } ?> 
      </div>
   </div> 
 </header>
<script type="text/javascript">
  AttachEventListerSE("click",'.harmony_main_menu .navigation li a',function(){
      scriptJquery(this).closest("ul").find("li").removeClass('active');
      scriptJquery(this).closest("li").addClass('active');
    })
   // Header Spacing
   en4.core.runonce.add(function() {
      var height = scriptJquery(".layout_page_header").height();
      if(document.getElementById("global_wrapper")) {
         scriptJquery("#global_wrapper").css("margin-top", height+"px");
      }
   }); 
</script>
<script type="text/javascript">
  setTimeout(function () {
      scriptJquery('.layout_core_search_mini form input').on('focus blur', function (e) {
          scriptJquery(this).parents('#global_search_form').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
      }).trigger('blur');
  }, 2000);
  </script>
