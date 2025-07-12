<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _editPost.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php
$composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
if (!empty($composerOptions))
  $hashtagEnabled = engine_in_array("hashtags", $composerOptions);
?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    en4.activity.postLength = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength', 1000); ?>;
    en4.activity.bindEditFeed(<?php echo $this->action->getIdentity() ?>, {
      lang: {
        'Post Something...': '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
      },
      allowEmptyWithoutAttachment: <?php echo !empty($this->action->attachment_count) ? 1 : 0 ?>,
      hashtagEnabled: '<?php echo @$hashtagEnabled ?>',
    });
  });
</script>
<?php foreach ($this->composePartials as $partial): ?>
  <?php if (false !== strpos($partial[0], '_composeTag') && is_array($composerOptions) && !engine_in_array('userTags', $composerOptions)) {
    continue;
  } ?>
  <?php
  echo $this->partial($partial[0], $partial[1], array(
    "edit" => 1,
    'forEdit' => $this->action->getIdentity(),
    'action' => $this->action
  )
  )
    ?>
<?php endforeach; ?>

<span
  class="feed_item_body_edit_content <?php echo (empty($this->action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated') ?>"
  style="display:none;">
  <?php echo $this->content; ?>
  <?php echo $this->form->render($this) ?>
</span>