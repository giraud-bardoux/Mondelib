<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: upload-cover-photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<div class="global_form_popup">
  <div id="form_photo_cover" <?php if ($this->status): ?>class="is_hidden"<?php endif; ?>>
    <?php echo $this->form->setAttrib('class', 'upload_photo_popup_form')->render($this) ?>
  </div>
  <div id="cover_photo_loading" <?php if (!$this->status): ?>class="is_hidden"<?php endif; ?>>
    <div class="cover_photo_loader"></div>
  </div>
</div>

<?php if ($this->status): ?>
  <script type="text/javascript">
  <?php if ($this->photoType == 'cover'): ?>
     parent.document.coverPhoto.getCoverPhoto(1);
  <?php else: ?>
     parent.document.mainPhoto.getMainPhoto();
  <?php endif; ?>
  </script>
<?php else: ?>
<script type="text/javascript">
  function uploadPhoto() {
    scriptJquery('#cover_photo_form').submit();
    scriptJquery('#form_photo_cover').addClass('is_hidden');
    scriptJquery('#cover_photo_loading').removeClass('is_hidden');
  }
</script>
<?php endif; ?>
