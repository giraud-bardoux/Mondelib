<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: SongController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Music_SongController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    // Get viewer info
    $this->view->viewer     = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id  = Engine_Api::_()->user()->getViewer()->getIdentity();
    // Get subject
    if (
      null !== ($song_id = $this->_getParam('song_id')) &&
      null !== ($song = Engine_Api::_()->getItem('music_playlist_song', $song_id)) &&
      $song instanceof Music_Model_PlaylistSong
    ) {
      Engine_Api::_()->core()->setSubject($song);
    }
  }
  public function renameAction()
  {
    // Check subject
    if (!Engine_Api::_()->core()->hasSubject('music_playlist_song')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $translate->_('Not a valid song'), 'result' => array()));
    }
    // Check method
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => Zend_Registry::get('Zend_Translate')->_('Invalid request method'), 'result' => array()));
    }
    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();
    // Check song/playlist
    if (!$song || !$playlist) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid playlist'), 'result' => array()));
    }
    // Check auth
    if (!Engine_Api::_()->authorization()->isAllowed($playlist, null, 'edit')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Not allowed to edit this playlist'), 'result' => array()));
    }
    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $song->setTitle($this->_getParam('title'));
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Your changes have been saved.')));
  }
  public function deleteAction()
  {
    // Check subject
    if (!Engine_Api::_()->core()->hasSubject('music_playlist_song')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Not a valid song'), 'result' => array()));
    }
    // Check method
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
    }
    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();
    // Check song/playlist
    if (!$song || !$playlist) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid playlist'), 'result' => array()));
    }
    // Check auth
    if (!Engine_Api::_()->authorization()->isAllowed($playlist, null, 'edit')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Not allowed to edit this playlist'), 'result' => array()));
    }
    // Get file
    $file = Engine_Api::_()->getItem('storage_file', $song->file_id);
    if (!$file) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid playlist'), 'result' => array()));
    }
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $song->deleteUnused();
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Your changes have been saved.')));
  }
  public function tallyAction()
  {
    // Check subject
    if (!Engine_Api::_()->core()->hasSubject('music_playlist_song')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Not a valid song'), 'result' => array()));
    }
    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();
    // Check song
    if (!$song || !$playlist) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid song_id'), 'result' => array()));
    }
    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $song->play_count++;
      $song->save();
      $playlist->play_count++;
      $playlist->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
    }
    $song->toArray();
    $song->playCountLanguagified();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Your changes have been saved.')));
  }
  public function appendAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'create')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    if (!$this->_helper->requireSubject('music_playlist_song')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    // Set song
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $viewer = Engine_Api::_()->user()->getViewer();
    // Get form
    $form = new Music_Form_Song_Append();
    // Populate form
    $songTable = $song->getTable();
    $playlistTable = Engine_Api::_()->getDbTable('playlists', 'music');
    $playlists = $playlistTable->select()
      ->from($playlistTable, array('playlist_id', 'title'))
      ->where('owner_type = ?', 'user')
      ->where('owner_id = ?', $viewer->getIdentity())
      ->query()
      ->fetchAll();
    foreach ($playlists as $playlist) {
      if ($playlist['playlist_id'] != $song->playlist_id) {
        $form->playlist_id->addMultiOption($playlist['playlist_id'], html_entity_decode($playlist['title']));
      }
    }
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    // Check method/data
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => Zend_Registry::get('Zend_Translate')->_('Invalid request method'), 'result' => array()));
    }
    // Check if valid
    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }
    // Get values
    $values = $form->getValues();
    if (empty($values['playlist_id']) && empty($values['title'])) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please enter a title or select a playlist.'), 'result' => array()));
    }
    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      // Existing playlist
      if (!empty($values['playlist_id'])) {
        $playlist = Engine_Api::_()->getItem('music_playlist', $values['playlist_id']);
        // already exists in playlist
        $alreadyExists = $songTable->select()
          ->from($songTable, 'song_id')
          ->where('playlist_id = ?', $playlist->getIdentity())
          ->where('file_id = ?', $song->file_id)
          ->limit(1)
          ->query()
          ->fetchColumn();
        if ($alreadyExists) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This playlist already has this song.'), 'result' => array()));
        }
      }
      // New playlist
      else {
        $playlist = $playlistTable->createRow();
        $playlist->title = trim($values['title']);
        $playlist->owner_type = 'user';
        $playlist->owner_id = $viewer->getIdentity();
        $playlist->search = 1;
        $playlist->save();
        // Add action and attachments
        $auth = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($playlist, 'registered', 'comment', true);
        foreach (array('everyone', 'registered', 'member') as $role) {
          $auth->setAllowed($playlist, $role, 'view', true);
        }
        // Only create activity feed item if "search" is checked
        if ($playlist->search) {
          $activity = Engine_Api::_()->getDbTable('actions', 'activity');
          $action = $activity->addActivity(
            Engine_Api::_()->user()->getViewer(),
            $playlist,
            'music_playlist_new'
          );
          if ($action) {
            $activity->attachActivity($action, $playlist);
          }
        }
      }
      // Add song
      $playlist->addSong($song->file_id);
      // Response
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Your changes have been saved.')));
    } catch (Music_Model_Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
    } catch (Exception $e) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
      $db->rollback();
    }
  }
  public function uploadAction()
  {
    // only members can upload music
    if (!$this->_helper->requireUser()->checkRequire()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Max file size limit exceeded or session expired.'), 'result' => array()));
    }
    // Check method
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
    }
    // Check file
    if (empty($_FILES['file'])) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('No file'), 'result' => array()));
    }
    // Process
    $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    try {
      $song = Engine_Api::_()->getApi('core', 'music')->createSong($_FILES['file']);
      $song->getIdentity();
      $song->getHref();
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Your changes have been saved.')));
    } catch (Music_Model_Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
      $db->rollback();
    } catch (Exception $e) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate($e->getMessage()), 'result' => array()));
      $db->rollback();
    }
  }
}
