<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     Jung
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Forum/views/scripts/_adminHeader.tpl';?>
<script type="text/javascript">
  var fetchLevelSettings =function(level_id){
    window.location.href= en4.core.baseUrl+'admin/forum/level/index/id/'+level_id;
  }
</script>
<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this) ?>
  </div>
</div>
