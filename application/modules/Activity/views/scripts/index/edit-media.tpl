<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: edit-media.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php if($this->success) { ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      parent.scriptJquery('.photo_view_info_title').html('<?php echo $this->title; ?>');
      parent.scriptJquery('.photo_view_info_caption').html('<?php echo $this->description; ?>');
      parent.Smoothbox.close();
    });
  </script>
  <?php return; ?>
<?php } ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#tags').selectize({
      maxItems: 10,
      valueField: 'label',
      labelField: 'label',
      searchField: 'label',
      create: true,
      load: function(query, callback) {
        if (!query.length) return callback();
        scriptJquery.ajax({
          url: '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>',
          data: { value: query },
          success: function (transformed) {
            callback(transformed);
          },
          error: function () {
            callback([]);
          }
        });
      }
    });
  });
</script>
<?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->subject)); ?>
<?php echo $this->form->render($this); ?>
