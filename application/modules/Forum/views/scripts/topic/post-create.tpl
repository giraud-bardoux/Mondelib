<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: post-create.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>
<script type="text/javascript">
function showUploader()
{
  scriptJquery('#photo').show();
  scriptJquery('#photo-label').hide();
}
</script>
<div class="layout_middle">
  <div class="generic_layout_container">
    <h2>
    <?php echo $this->htmlLink(array('route'=>'forum_general'), $this->translate("Forums"));?>
      &#187; <?php echo $this->htmlLink(array('route'=>'forum_forum', 'forum_id'=>$this->forum->getIdentity()), $this->translate($this->forum->getTitle()));?>
      &#187; <?php echo $this->htmlLink(array('route'=>'forum_topic', 'topic_id'=>$this->topic->getIdentity()), $this->topic->getTitle());?>
      &#187 <?php echo $this->translate('Post Reply');?>
    </h2>
    <?php echo $this->form->render($this) ?>
 </div>
</div>


<script type="text/javascript">
  scriptJquery('.core_main_forum').parent().addClass('active');
</script>
