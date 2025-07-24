<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Flouter une photo'); ?>
  </h2>
</div>

<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="photoblur_preview">
      <h3><?php echo $this->translate('Photo originale'); ?></h3>
      <div class="photo_preview" style="text-align: center; margin: 20px 0;">
        <?php echo $this->htmlLink($this->photo->getHref(), $this->itemPhoto($this->photo, 'thumb.normal')); ?>
      </div>
      
      <div class="photo_info">
        <p>
          <strong><?php echo $this->translate('Album:'); ?></strong>
          <?php echo $this->htmlLink($this->album->getHref(), $this->album->getTitle()); ?>
        </p>
        <p>
          <strong><?php echo $this->translate('Propriétaire:'); ?></strong>
          <?php echo $this->htmlLink($this->photo->getOwner()->getHref(), $this->photo->getOwner()->getTitle()); ?>
        </p>
      </div>
    </div>
    
    <div class="photoblur_form">
      <?php echo $this->form->render($this); ?>
    </div>
  </div>
</div>

<style type="text/css">
.photoblur_preview {
  background: #f5f5f5;
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 5px;
}

.photoblur_preview h3 {
  margin-bottom: 15px;
  text-align: center;
}

.photo_info {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #ddd;
}

.photo_info p {
  margin: 5px 0;
}

.photoblur_form {
  background: #fff;
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 5px;
}
</style>

<script type="text/javascript">
// Définir l'URL d'annulation
document.addEventListener('DOMContentLoaded', function() {
  var cancelButton = document.getElementById('cancel');
  if (cancelButton) {
    cancelButton.href = '<?php echo $this->photo->getHref(); ?>';
  }
});
</script>