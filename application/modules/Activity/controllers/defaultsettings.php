<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: defaultsettings.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

//Category Icon for Comments
$select = Engine_Api::_()->getDbTable('emotioncategories', 'comment')->select()->order('category_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotioncategories', 'comment')->fetchAll($select);
foreach($paginator as $result) {
	$title = lcfirst($result->title);
  if($title == 'in Love') {
    $title = 'inlove';
  }
  if($title == 'in love') {
    $title = 'inlove';
  }
	$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "category" . DIRECTORY_SEPARATOR;
	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->category_id,
				'parent_type' => "comment_category",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_comment_emotioncategories', array('file_id' => $photoFile->file_id), array('category_id = ?' => $result->category_id));
		}
	}
}

//Emotions Gallery image for Comments
$emotiongalleriesselect = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->select()->order('gallery_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->fetchAll($emotiongalleriesselect);
foreach($paginator as $result) {
	$title = strtolower($result->title);
  if($title == 'lazy life line') {
    $title = 'lazylifeline';
  } else if($title == 'tom and jerry') {
    $title = 'tomandjerry';
  }
	$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "stickers" . DIRECTORY_SEPARATOR . "galleryimages" . DIRECTORY_SEPARATOR;
	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->gallery_id,
				'parent_type' => "comment_gallery",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_comment_emotiongalleries', array('file_id' => $photoFile->file_id), array('gallery_id = ?' => $result->gallery_id));
		}
	}
}

//Upload emotion Files in Gallery
$emotionfilesTable = Engine_Api::_()->getDbtable('emotionfiles', 'comment');
$emotiongalleriesselect = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->select()->order('gallery_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->fetchAll($emotiongalleriesselect);

foreach($paginator as $result) {

  $title = $result->title;
  if($title == 'Meep') {
    $title == 'Meep';
  } elseif($title == 'Minions') {
    $title = 'minions';
  } elseif($title == 'Lazy Life Line') {
    $title = 'LazyLifeLine';
  } elseif($title == 'Waddles') {
    $title = 'waddles';
  } elseif($title == 'Panda') {
    $title = 'panda';
  } elseif($title == 'Tom And Jerry') {
    $title = 'tomandjerry';
  }

  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "stickers" . DIRECTORY_SEPARATOR . $title . DIRECTORY_SEPARATOR;

  for($i= 1;$i<=40;$i++) {
    if (is_file($PathFile . $i . '.png')) {
      $item = $emotionfilesTable->createRow();
      $values['gallery_id'] = $result->gallery_id;
      $item->setFromArray($values);
      $item->save();
      $pngFile = $PathFile . $i . '.png';
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $storageObject = $storage->createFile($pngFile, array(
        'parent_id' => $item->getIdentity(),
        'parent_type' => $item->getType(),
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      ));
      // Remove temporary file
      //@unlink($file['tmp_name']);
      $item->photo_id = $storageObject->file_id;
      $item->save();
    }
  }
}

//Upload Reactions
$reactionsTable = Engine_Api::_()->getDbTable('reactions', 'comment');
$emotiongalleriesselect = $reactionsTable->select()->order('reaction_id ASC');
$paginator = $reactionsTable->fetchAll($emotiongalleriesselect);

foreach($paginator as $result) {

  $title = $result->title;
  if($title == 'Like') {
    $title = 'icon-like';
  } elseif($title == 'Love') {
    $title = 'icon-love';
  } elseif($title == 'Sad') {
    $title = 'icon-sad';
  } elseif($title == 'Wow') {
    $title = 'icon-wow';
  } elseif($title == 'Haha') {
    $title = 'icon-haha';
  } elseif($title == 'Angry') {
    $title = 'icon-angery';
  }

  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;

	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->reaction_id,
				'parent_type' => "comment_reaction",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_comment_reactions', array('file_id' => $photoFile->file_id), array('reaction_id = ?' => $result->reaction_id));
		}
	}
}

// Upload Backgrounds
Engine_Api::_()->activity()->uploadBackgrounds();

Engine_Api::_()->activity()->uploadFeelingsMainIconsActivity();

//Feeling Work
Engine_Api::_()->activity()->uploadFeelingsActivity();

//Upgrade Work for Reactions
Engine_Api::_()->activity()->uploadReactions();

//Emoji work
$paginator = Engine_Api::_()->getDbtable('emojis', 'activity' )->getPaginator(); 
$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Activity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "emojis" . DIRECTORY_SEPARATOR . "category". DIRECTORY_SEPARATOR;
foreach($paginator as $emoji) {
  $file = $PathFile . $emoji->emoji_id . '.png';
  if(!empty($file)) {
    $file_ext = pathinfo($file);
    $file_ext = $file_ext['extension'];

    $storage = Engine_Api::_()->getItemTable('storage_file');
    $storageObject = $storage->createFile(@$file, array(
      'parent_id' => $emoji->getIdentity(),
      'parent_type' => $emoji->getType(),
      'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
    ));
    // Remove temporary file
    //@unlink($file['tmp_name']);
    $emoji->file_id = $storageObject->file_id;
    $emoji->save();
  }
}