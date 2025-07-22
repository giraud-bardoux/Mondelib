<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: upload.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>

<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="breadcrumb_wrap">
      <div class="event_breadcrumb">
        <p><?php echo $this->event->__toString() ?> <?php echo $this->translate('&#187; Photos');?></p>
      </div>
    </div>
   </div>
   <div class="generic_layout_container">
    <div class="upload_form global_form_wrap">
      <?php echo $this->form->render($this) ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  scriptJquery('.core_main_event').parent().addClass('active');
  en4.core.runonce.add(function () {
    new Uploader('#upload_file', {
      uploadLinkClass : 'buttonlink icon_photos_new',
      removePhotoURL : "<?php echo $this->url(array('controller' => 'photo','action' => 'remove-photo'), 'event_extended', true); ?>",
      uploadLinkTitle : '<?php echo $this->translate("Add Photos");?>',
      uploadLinkDesc : '<?php echo $this->translate("Click \"Add Photos\" to select one or more photos from your computer."
        . " After you have selected the photos, they will begin to upload right away. "
        . "When your upload is finished, click the button below your photo list to save them to your album.");?>'
    });
  });
</script>
