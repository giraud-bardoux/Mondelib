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
<?php include APPLICATION_PATH .  '/application/modules/Harmony/views/scripts/_adminHeader.tpl';?>

<h2><?php echo $this->translate('Global Settings') ?></h2>
<p><?php echo $this->translate("These settings affect all members in your community.") ?></p>
<?php echo 'More info: <a href="https://community.socialengine.com/blogs/1/144/harmony-theme" target="_blank">See KB article</a>'; ?>
<br />	
<br />
<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">
  function confirmChangeLandingPage(value){
    if((value == 1 || value == 2) && !confirm('Are you sure want to set the default Landing page of this theme as the Landing page of your website. For old landing page you will have to manually make changes in the Landing page from Layout Editor. Backup page of your current landing page will get created with the name "Backup - Landing Page".')) {
      scriptJquery('#harmony_changelanding-0').prop('checked',true);
    } else if(value == 0) {
    } else {
      scriptJquery('#harmony_changelanding-0').removeAttr('checked');
      scriptJquery('#harmony_changelanding-0').prop('checked',false);
    }
  }

  scriptJquery('.core_admin_main_harmony').parent().addClass('active');
</script>
