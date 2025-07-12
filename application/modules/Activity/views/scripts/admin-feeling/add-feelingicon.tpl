<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: add-feelingicon.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
  function changemodule(modulename) {
    var type = '<?php echo $this->type ?>';
    var feeling_id = '<?php echo $this->feeling_id ?>';
    window.location.href="<?php echo $this->url(array('module'=>'activity','controller'=>'feeling', 'action'=>'add-feelingicon'),'admin_default',true)?>/module_name/"+modulename + "/type/" +type+"/feeling_id/"+feeling_id;
  }
</script>
