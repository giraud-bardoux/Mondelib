<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminEmotionController.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_AdminEmotionController extends Core_Controller_Action_Admin
{

  public function indexAction()
  {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'comment_admin_emotio');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('comment_admin_emotio', array(), 'comment_admin_main_emotionssettingsmain');

    if (engine_count($_POST)) {
      $galleryTable = Engine_Api::_()->getDbtable('emotiongalleries', 'comment');
      $filesTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
      foreach ($_POST as $key => $valueSelectedcategory) {
        $category = Engine_Api::_()->getItem('comment_emotioncategory', $valueSelectedcategory);
        $gallerySelect = $galleryTable->select()->where('category_id =?', $valueSelectedcategory);
        foreach ($galleryTable->fetchAll($gallerySelect) as $gallery) {
          $filesSelect = $filesTable->select()->where('gallery_id =?', $gallery->getIdentity());
          foreach ($filesTable->fetchAll($filesSelect) as $files)
            $files->remove();
          $gallery->remove();
        }
        $category->delete();
        $this->_helper->redirector->gotoRoute(array());
      }
    }

    $page = $this->_getParam('page', 1);
    $this->view->paginator = Engine_Api::_()->getDbTable('emotioncategories', 'comment')->getPaginator();
    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function uploadZipFileAction()
  {

    $id = $this->_getParam('gallery_id', false);
    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');
    $this->view->form = $form = new Comment_Form_Admin_Emotion_Zipupload();
    // Check if post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    $this->view->max_file_upload_in_bytes = $max_file_upload_in_bytes = Engine_Api::_()->core()->convertPHPSizeToBytes(ini_get('upload_max_filesize'));

    if ($this->getRequest()->isPost() && (empty($_FILES['file']['size']) || (int) $_FILES['file']['size'] > (int) $max_file_upload_in_bytes)) {
      $form->file->addError('File was not uploaded and size not more than ' . $upload_max_size);
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    if (!empty($_FILES["file"]["name"])) {
      $file = $_FILES["file"];
      $filename = $file["name"];
      $tmp_name = $file["tmp_name"];
      $type = $file["type"];

      $name = explode(".", $filename);
      $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');

      if (engine_in_array($type, $accepted_types)) { //If it is Zipped/compressed File
        $okay = true;
      }

      $continue = strtolower($name[1]) == 'zip' ? true : false; //Checking the file Extension

      if (!$continue) {
        $form->addError("The file you are trying to upload is not a .zip file. Please try again.");
        return;
      }


      /* here it is really happening */
      $ran = $name[0] . "-" . time() . "-" . rand(1, time());
      $dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/';
      $targetdir = $dir . $ran;
      $targetzip = $dir . $ran . ".zip";

      if (move_uploaded_file($tmp_name, $targetzip)) { //Uploading the Zip File
        /* Extracting Zip File */
        $zip = new ZipArchive();
        $x = $zip->open($targetzip);  // open the zip file to extract
        if ($x === true) {
          $zip->extractTo($targetdir); // place in the directory with same name
          $zip->close();

          @unlink($targetzip); //Deleting the Zipped file
          // Get subdirectories
          chmod($targetdir, 0777);
          $directories = glob($targetdir . '*', GLOB_ONLYDIR);
          if ($directories !== FALSE) {
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            // If we're here, we're done
            $this->view->status = true;
            try {
              foreach ($directories as $directory) {
                $path = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
                foreach ($path as $file) {
                  if (!$file->isFile())
                    continue;
                  $base_name = basename($file->getFilename());
                  if (!($pos = strrpos($base_name, '.')))
                    continue;
                  $extension = strtolower(ltrim(substr($base_name, $pos), '.'));
                  if (!engine_in_array($extension, array('gif', 'jpg', 'jpeg', 'png', 'JPEG', 'JPG', 'PNG', 'GIF')))
                    continue;
                  $this->uploadZipFile($id, $file->getPathname());
                }
              }
              $db->commit();
              $this->rrmdir($targetdir);
            } catch (Exception $e) {
              $db->rollBack();
              throw $e;
            }
          }
        }
      } else {
        $form->addError("There was a problem with the upload. Please try again.");
        return;
      }
    }
    $this->_forward(
      'success',
      'utility',
      'core',
      array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('Zip images uploaded Successfully.')
      )
    );
  }

  private function rrmdir($src)
  {
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
      if (($file != '.') && ($file != '..')) {
        $full = $src . '/' . $file;
        if (is_dir($full)) {
          $this->rrmdir($full);
        } else {
          unlink($full);
        }
      }
    }
    closedir($dir);
    rmdir($src);
  }

  private function uploadZipFile($gallery_id, $file = '')
  {
    $catgeoryTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
    $item = $catgeoryTable->createRow();
    $values['gallery_id'] = $gallery_id;
    $item->setFromArray($values);
    $item->save();
    if (!empty($file)) {
      $file_ext = pathinfo($file);
      $file_ext = $file_ext['extension'];
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $fileUpload = array('name' => basename($file), 'tmp_name' => $file, 'size' => filesize($file), 'error' => 0);
      $storageObject = $storage->createFile(
        $file,
        array(
          'parent_id' => $item->getIdentity(),
          'parent_type' => $item->getType(),
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        )
      );
      // Remove temporary file
      //@unlink($file['tmp_name']);
      $item->photo_id = $storageObject->file_id;
      $item->save();
    }
  }

  public function createCategoryAction()
  {

    $id = $this->_getParam('id', false);
    $this->view->form = $form = new Comment_Form_Admin_Emotion_Categorycreate();
    if ($id) {
      $item = Engine_Api::_()->getItem('comment_emotioncategory', $id);
      $form->populate($item->toArray());
      $form->setTitle('Edit This Category');
      $form->submit->setLabel('Edit');
    }
    // Check if post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $catgeoryTable = Engine_Api::_()->getDbtable('emotioncategories', 'comment');
      $values = $form->getValues();
      unset($values['file']);
      if (empty($id))
        $item = $catgeoryTable->createRow();
      $item->setFromArray($values);
      $item->save();
      if (!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        //echo "<pre>";var_dump($item->getIdentity());die;
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile(
          $form->file,
          array(
            'parent_id' => $item->getIdentity(),
            'parent_type' => $item->getType(),
            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          )
        );
        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward(
      'success',
      'utility',
      'core',
      array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('Category Created Successfully.')
      )
    );
  }

  public function deleteCategoryAction()
  {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Core_Form_Admin_Delete();
    $form->setTitle('Delete Category?');
    $form->setDescription('Are you sure that you want to delete this catgeory? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $galleryTable = Engine_Api::_()->getDbtable('emotiongalleries', 'comment');
      $filesTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
      $category = Engine_Api::_()->getItem('comment_emotioncategory', $id);
      $gallerySelect = $galleryTable->select()->where('category_id =?', $id);
      foreach ($galleryTable->fetchAll($gallerySelect) as $gallery) {
        $filesSelect = $filesTable->select()->where('gallery_id =?', $gallery->getIdentity());
        foreach ($filesTable->fetchAll($filesSelect) as $files)
          $files->delete();
        $gallery->delete();
      }
      $category->delete();
      $this->_forward(
        'success',
        'utility',
        'core',
        array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Category Delete Successfully.')
        )
      );
    }
  }


  //Sponsored Action
  public function enabledAction()
  {
    if (!_ENGINE_ADMIN_NEUTER) {
      $id = $this->_getParam('gallery_id');
      if (!empty($id)) {
        $item = Engine_Api::_()->getItem('comment_emotiongallery', $id);
        $item->enabled = !$item->enabled;
        $item->save();
      }
    }
    $this->_redirect('admin/comment/emotion/gallery');
  }

  public function galleryAction()
  {
    if (engine_count($_POST)) {
      $filesTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
      foreach ($_POST as $key => $gallery_id) {
        $gallery = Engine_Api::_()->getItem('comment_emotiongallery', $gallery_id);
        $filesSelect = $filesTable->select()->where('gallery_id =?', $gallery_id);
        foreach ($filesTable->fetchAll($filesSelect) as $files) {
          $files->delete();
        }
        $gallery->delete();
        $this->_helper->redirector->gotoRoute(array());
      }
    }

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'comment_admin_emotio');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('comment_admin_emotio', array(), 'comment_admin_main_emotiongallery');

    $page = $this->_getParam('page', 1);
    $this->view->paginator = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->getPaginator();
    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function createGalleryAction()
  {
    $id = $this->_getParam('id', false);
    $this->view->form = $form = new Comment_Form_Admin_Emotion_Gallerycreate();
    if ($id) {
      $item = Engine_Api::_()->getItem('comment_emotiongallery', $id);
      $form->populate($item->toArray());
      $form->setTitle('Edit This Pack');
      $form->submit->setLabel('Edit');
    }
    // Check if post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $catgeoryTable = Engine_Api::_()->getDbtable('emotiongalleries', 'comment');
      $values = $form->getValues();
      unset($values['file']);
      if (empty($id))
        $item = $catgeoryTable->createRow();
      $item->setFromArray($values);
      $item->save();
      if (!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile(
          $form->file,
          array(
            'parent_id' => $item->getIdentity(),
            'parent_type' => $item->getType(),
            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          )
        );
        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward(
      'success',
      'utility',
      'core',
      array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('Category Created Successfully.')
      )
    );
  }
  public function deleteGalleryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Core_Form_Admin_Delete();
    $form->setTitle('Delete This Pack');
    $form->setDescription('Are you sure that you want to delete this pack? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $filesTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
      $gallery = Engine_Api::_()->getItem('comment_emotiongallery', $id);
      $filesSelect = $filesTable->select()->where('gallery_id =?', $id);
      foreach ($filesTable->fetchAll($filesSelect) as $files) {
        $files->delete();
      }
      $gallery->delete();
      $this->_forward(
        'success',
        'utility',
        'core',
        array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Category Delete Successfully.')
        )
      );
    }
  }
  public function filesAction()
  {
    if (engine_count($_POST)) {
      foreach ($_POST as $key => $file_id) {
        $file = Engine_Api::_()->getItem('comment_emotionfile', $file_id);
        $file->delete();
      }
      $this->_helper->redirector->gotoRoute(array());
    }
    $this->view->gallery_id = $gallery_id = $this->_getParam('gallery_id', false);
    if (!$gallery_id)
      return $this->_forward('notfound', 'error', 'core');

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'comment_admin_emotio');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('comment_admin_emotio', array(), 'comment_admin_main_emotiongallery');

    $page = $this->_getParam('page', 1);
    $this->view->paginator = Engine_Api::_()->getDbTable('emotionfiles', 'comment')->getPaginator(array('gallery_id' => $gallery_id));
    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function createFileAction()
  {
    $id = $this->_getParam('id', false);
    $gallery_id = $this->_getParam('gallery_id', 0);
    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');
    $this->view->form = $form = new Comment_Form_Admin_Emotion_Filecreate();
    if ($id) {
      $item = Engine_Api::_()->getItem('comment_emotionfile', $id);
      $form->populate($item->toArray());

      //Tags Work
      $tagStr = '';
      foreach ($item->tags()->getTagMaps() as $tagMap) {
        $tag = $tagMap->getTag();
        if (!isset($tag->text))
          continue;
        if ('' !== $tagStr)
          $tagStr .= ', ';
        $tagStr .= $tag->text;
      }
      $form->populate(
        array(
          'tags' => $tagStr,
        )
      );
      $this->view->tagNamePrepared = $tagStr;

      $form->setTitle('Edit This Sticker');
      $form->submit->setLabel('Save Changes');
    }
    // Check if post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    if ($this->getRequest()->isPost() && (empty($_FILES['file']['size']) || (int) $_FILES['file']['size'] > (int) $max_file_upload_in_bytes)) {
      $form->file->addError('File was not uploaded and size not more than ' . $upload_max_size);
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $catgeoryTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
      $values = $form->getValues();
      unset($values['file']);
      if (empty($id))
        $item = $catgeoryTable->createRow();
      $values['gallery_id'] = $gallery_id;
      $item->setFromArray($values);
      $item->save();

      $viewer = Engine_Api::_()->user()->getViewer();
      if (empty($id)) {
        //Add tags work
        $tags = preg_split('/[,]+/', $values['tags']);
        $item->tags()->addTagMaps($viewer, $tags);
      } else {
        // handle tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $item->tags()->setTagMaps($viewer, $tags);
      }

      if (!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile(
          $form->file,
          array(
            'parent_id' => $item->getIdentity(),
            'parent_type' => $item->getType(),
            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          )
        );
        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->photo_id = $storageObject->file_id;
        $item->save();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward(
      'success',
      'utility',
      'core',
      array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('File Created Successfully.')
      )
    );
  }
  public function deleteFileAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Core_Form_Admin_Delete();
    $form->setTitle('Delete This Sticker');
    $form->setDescription('Are you sure that you want to delete this sticker? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $file = Engine_Api::_()->getItem('comment_emotionfile', $id);
      $file->delete();
      $this->_forward(
        'success',
        'utility',
        'core',
        array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('File Delete Successfully.')
        )
      );
    }
  }
}
