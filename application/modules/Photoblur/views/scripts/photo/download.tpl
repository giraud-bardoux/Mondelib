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
    <?php echo $this->translate('Photo floutée avec succès'); ?>
  </h2>
</div>

<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="photoblur_success">
      <div class="success_message">
        <i class="fa fa-check-circle" style="color: #5cb85c; font-size: 48px;"></i>
        <h3><?php echo $this->translate('Votre photo a été floutée avec succès !'); ?></h3>
        <p><?php echo $this->translate('Niveau de flou appliqué : %s', $this->blur->blur_level); ?></p>
      </div>
      
      <div class="photo_comparison">
        <div class="photo_column">
          <h4><?php echo $this->translate('Photo originale'); ?></h4>
          <div class="photo_container">
            <?php echo $this->itemPhoto($this->photo, 'thumb.normal'); ?>
          </div>
        </div>
        
        <div class="photo_column">
          <h4><?php echo $this->translate('Photo floutée'); ?></h4>
          <div class="photo_container">
            <img src="<?php echo $this->file->map(); ?>" alt="Photo floutée" style="max-width: 100%; height: auto;" />
          </div>
        </div>
      </div>
      
      <div class="download_section">
        <a href="<?php echo $this->file->map(); ?>" download="blurred_<?php echo $this->photo->getTitle(); ?>.jpg" class="button">
          <i class="fa fa-download"></i> <?php echo $this->translate('Télécharger la photo floutée'); ?>
        </a>
        
        <div class="action_links">
          <a href="<?php echo $this->photo->getHref(); ?>">
            <?php echo $this->translate('Retour à la photo originale'); ?>
          </a>
          <span class="separator">|</span>
          <a href="<?php echo $this->url(array('action' => 'blur', 'photo_id' => $this->photo->photo_id), 'photoblur_photo', true); ?>">
            <?php echo $this->translate('Flouter à nouveau avec un niveau différent'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style type="text/css">
.photoblur_success {
  text-align: center;
  padding: 30px;
}

.success_message {
  margin-bottom: 40px;
}

.success_message h3 {
  color: #5cb85c;
  margin: 20px 0 10px 0;
}

.photo_comparison {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin: 40px 0;
  flex-wrap: wrap;
}

.photo_column {
  flex: 1;
  max-width: 400px;
}

.photo_column h4 {
  margin-bottom: 15px;
  font-weight: bold;
}

.photo_container {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.photo_container img {
  display: block;
  margin: 0 auto;
}

.download_section {
  margin-top: 40px;
}

.download_section .button {
  display: inline-block;
  background: #5cb85c;
  color: white;
  padding: 12px 30px;
  border-radius: 5px;
  text-decoration: none;
  font-size: 16px;
  margin-bottom: 20px;
  transition: background 0.3s;
}

.download_section .button:hover {
  background: #4cae4c;
}

.action_links {
  margin-top: 20px;
  font-size: 14px;
}

.action_links .separator {
  margin: 0 10px;
  color: #ccc;
}

@media (max-width: 768px) {
  .photo_comparison {
    flex-direction: column;
    align-items: center;
  }
  
  .photo_column {
    width: 100%;
    max-width: 300px;
  }
}
</style>