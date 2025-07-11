<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: delete-media.tpl 2024-10-28 00:00:00Z 
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
      if(parent.scriptJquery('#photo_next').length)
        parent.document.getElementById('photo_next').click();
      else if(parent.scriptJquery('#photo_prev').length)
        parent.document.getElementById('photo_prev').click();
      else {
        if(parent.scriptJquery('.media_lightbox_close').length == 0) {
          parent.loadAjaxContentApp(en4.core.baseUrl, false);
          parent.Smoothbox.close();
          return;
        }
        parent.parent.scriptJquery('.activity_filter_tabs').find('li.active').find('a').trigger('click');
        parent.parent.Smoothbox.close();
        return;
      }
      parent.parent.scriptJquery('.activity_filter_tabs').find('li.active').find('a').trigger('click');
      parent.Smoothbox.close();
    });
  </script>
  <?php return; ?>
<?php } ?>
<?php echo $this->form->render($this) ?>