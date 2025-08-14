<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<script type="text/javascript">
function updateUploader()
{
  if(scriptJquery('#photo_delete').pop("checked")) {
    scriptJquery('#photo_group-wrapper').show();
  }
  else 
  {
    scriptJquery('#photo_group-wrapper').hide();
  }
}
</script>
<?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->subject())); ?>
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="topic_create global_form_wrap">
      <?php echo $this->form->render($this) ?>
    </div>
  </div>
</div>


