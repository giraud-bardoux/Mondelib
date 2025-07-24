<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_PhotoController extends Core_Controller_Action_Standard
{
  public function init()
  {
    // Vérifier que l'utilisateur est connecté
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }
  }

  public function blurAction()
  {
    // Récupérer l'ID de la photo
    $photo_id = $this->_getParam('photo_id');
    if (!$photo_id) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    // Récupérer la photo
    $photo = Engine_Api::_()->getItem('album_photo', $photo_id);
    if (!$photo) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    // Vérifier les permissions
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$photo->authorization()->isAllowed($viewer, 'view')) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    // Passer la photo à la vue
    $this->view->photo = $photo;
    $this->view->album = $photo->getAlbum();

    // Créer le formulaire
    $this->view->form = $form = new Photoblur_Form_Blur();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $values = $form->getValues();
    $blur_level = $values['blur_level'];

    // Traiter le floutage
    $db = Engine_Api::_()->getDbtable('blurs', 'photoblur')->getAdapter();
    $db->beginTransaction();

    try {
      // Récupérer le fichier original
      $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      if (!$file) {
        throw new Exception('Fichier photo introuvable');
      }

      // Créer une copie floutée
      $blurredFile = $this->_processBlur($file, $blur_level);

      // Sauvegarder dans la base de données
      $blurTable = Engine_Api::_()->getDbtable('blurs', 'photoblur');
      $blur = $blurTable->createRow();
      $blur->photo_id = $photo->photo_id;
      $blur->user_id = $viewer->getIdentity();
      $blur->original_file_id = $photo->file_id;
      $blur->blurred_file_id = $blurredFile->file_id;
      $blur->blur_level = $blur_level;
      $blur->creation_date = date('Y-m-d H:i:s');
      $blur->modified_date = date('Y-m-d H:i:s');
      $blur->save();

      $db->commit();

      // Rediriger vers la page de téléchargement
      return $this->_helper->redirector->gotoRoute(array(
        'action' => 'download',
        'photo_id' => $photo_id,
        'blur_id' => $blur->blur_id
      ), 'photoblur_photo', true);

    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function downloadAction()
  {
    $photo_id = $this->_getParam('photo_id');
    $blur_id = $this->_getParam('blur_id');

    // Récupérer l'enregistrement de floutage
    $blur = Engine_Api::_()->getItem('photoblur_blur', $blur_id);
    if (!$blur || $blur->photo_id != $photo_id) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    // Vérifier que c'est bien l'utilisateur qui a créé le floutage
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($blur->user_id != $viewer->getIdentity()) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    // Récupérer le fichier flouté
    $file = Engine_Api::_()->getItem('storage_file', $blur->blurred_file_id);
    if (!$file) {
      return $this->_helper->redirector->gotoRoute(array(), 'album_general', true);
    }

    $this->view->blur = $blur;
    $this->view->file = $file;
    $this->view->photo = Engine_Api::_()->getItem('album_photo', $photo_id);
  }

  protected function _processBlur($originalFile, $blurLevel)
  {
    // Récupérer le chemin du fichier
    $filePath = $originalFile->storage_path;
    
    // Créer une image temporaire
    $tmpFile = APPLICATION_PATH . '/temporary/blur_' . time() . '_' . basename($filePath);
    
    // Charger l'image
    $image = imagecreatefromstring(file_get_contents($filePath));
    if (!$image) {
      throw new Exception('Impossible de charger l\'image');
    }

    // Appliquer le flou gaussien plusieurs fois selon le niveau
    for ($i = 0; $i < $blurLevel; $i++) {
      imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
    }

    // Sauvegarder l'image floutée
    $imageType = exif_imagetype($filePath);
    switch ($imageType) {
      case IMAGETYPE_JPEG:
        imagejpeg($image, $tmpFile, 90);
        break;
      case IMAGETYPE_PNG:
        imagepng($image, $tmpFile, 9);
        break;
      case IMAGETYPE_GIF:
        imagegif($image, $tmpFile);
        break;
      default:
        imagejpeg($image, $tmpFile, 90);
    }

    imagedestroy($image);

    // Créer un nouvel objet de stockage
    $storage = Engine_Api::_()->storage();
    $params = array(
      'parent_type' => 'photoblur_blur',
      'parent_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      'name' => 'blurred_' . basename($filePath),
    );

    try {
      $storedFile = $storage->create($tmpFile, $params);
    } catch (Exception $e) {
      if (file_exists($tmpFile)) {
        @unlink($tmpFile);
      }
      throw $e;
    }

    // Supprimer le fichier temporaire
    @unlink($tmpFile);

    return $storedFile;
  }
}