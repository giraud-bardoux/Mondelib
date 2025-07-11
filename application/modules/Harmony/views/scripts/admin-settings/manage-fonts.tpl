<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manage-fonts.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Harmony/views/scripts/_adminHeader.tpl';?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script>
  en4.core.runonce.add(function() {
    usegooglefont('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('harmony.googlefonts', 0);?>');
  });
  
  function usegooglefont(value) {
    if(value == 1) {
      if(document.getElementById('harmony_bodygrp'))
        document.getElementById('harmony_bodygrp').style.display = 'none';
      if(document.getElementById('harmony_headinggrp'))
        document.getElementById('harmony_headinggrp').style.display = 'none';
      if(document.getElementById('harmony_mainmenugrp'))
        document.getElementById('harmony_mainmenugrp').style.display = 'none';
      if(document.getElementById('harmony_tabgrp'))
        document.getElementById('harmony_tabgrp').style.display = 'none';
      if(document.getElementById('harmony_googlebodygrp'))
        document.getElementById('harmony_googlebodygrp').style.display = 'block';
      if(document.getElementById('harmony_googleheadinggrp'))
        document.getElementById('harmony_googleheadinggrp').style.display = 'block';
      if(document.getElementById('harmony_googlemainmenugrp'))
        document.getElementById('harmony_googlemainmenugrp').style.display = 'block';
      if(document.getElementById('harmony_googletabgrp'))
        document.getElementById('harmony_googletabgrp').style.display = 'block';
    } else {
      if(document.getElementById('harmony_bodygrp'))
        document.getElementById('harmony_bodygrp').style.display = 'block';
      if(document.getElementById('harmony_headinggrp'))
        document.getElementById('harmony_headinggrp').style.display = 'block';
      if(document.getElementById('harmony_mainmenugrp'))
        document.getElementById('harmony_mainmenugrp').style.display = 'block';
      if(document.getElementById('harmony_tabgrp'))
        document.getElementById('harmony_tabgrp').style.display = 'block';
      if(document.getElementById('harmony_googlebodygrp'))
        document.getElementById('harmony_googlebodygrp').style.display = 'none';
      if(document.getElementById('harmony_googleheadinggrp'))
        document.getElementById('harmony_googleheadinggrp').style.display = 'none';
      if(document.getElementById('harmony_googlemainmenugrp'))
        document.getElementById('harmony_googlemainmenugrp').style.display = 'none';
      if(document.getElementById('harmony_googletabgrp'))
        document.getElementById('harmony_googletabgrp').style.display = 'none';
    }
  }
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_harmony').parent().addClass('active');
</script>
