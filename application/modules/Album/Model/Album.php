<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Album.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Model_Album extends Core_Model_Item_Abstract implements Countable
{
    protected $_parent_type = 'user';

    protected $_owner_type = 'user';

    protected $_parent_is_owner = true;

    public function getAttachmentClass() {
      return " feed_attachments_content_item ";
    }

    public function getHref($params = array())
    {
        $params = array_merge(array(
            'route' => 'album_specific',
            'reset' => true,
            'album_id' => $this->getIdentity(),
            'slug' => $this->getSlug(),
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble($params, $route, $reset);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPhotoUrl($type = null)
    {
        if( empty($this->photo_id) ) {
            $photoTable = Engine_Api::_()->getItemTable('album_photo');
            $photoInfo = $photoTable->select()
                ->from($photoTable, array('photo_id', 'file_id'))
                ->where('album_id = ?', $this->album_id)
                ->where('approved = ?', 1)
                ->order('order ASC')
                ->limit(1)
                ->query()
                ->fetch();
            if( !empty($photoInfo) ) {
                $this->photo_id = $photo_id = $photoInfo['photo_id'];
                $this->save();
                $file_id = $photoInfo['file_id'];
            } else {
                return;
            }
        } else {
            $photoTable = Engine_Api::_()->getItemTable('album_photo');
            $file_id = $photoTable->select()
                ->from($photoTable, 'file_id')
                ->where('approved = ?', 1)
                ->where('photo_id = ?', $this->photo_id)
                ->query()
                ->fetchColumn();
        }

        if( !$file_id ) {
            return;
        }

        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, $type);
        if( !$file ) {
            return;
        }

        return $file->map();
    }

    public function getPhotos($count = 1)
    {
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $select = $photoTable->select()
            ->where('album_id = ?', $this->album_id)
            ->where('approved = ?', 1)
            ->order('order ASC')
            ->limit($count);
        return $photoTable->fetchAll($select);
    }

    public function getFirstPhoto()
    {
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $select = $photoTable->select()
            ->where('album_id = ?', $this->album_id)
            ->where('approved = ?', 1)
            ->order('order ASC')
            ->limit(1);
        return $photoTable->fetchRow($select);
    }

    public function getLastPhoto()
    {
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $select = $photoTable->select()
            ->where('album_id = ?', $this->album_id)
            ->where('approved = ?', 1)
            ->order('order DESC')
            ->limit(1);
        return $photoTable->fetchRow($select);
    }

    public function count() {
    
      $viewer = Engine_Api::_()->user()->getViewer();
      $photoTable = Engine_Api::_()->getItemTable('album_photo');
      $select = $photoTable->select()
              ->from($photoTable, new Zend_Db_Expr('COUNT(photo_id)'))
              ->where('album_id = ?', $this->album_id);
      if(!$viewer->isAdmin() && $viewer->getIdentity() != $this->owner_id)
        $select->where('approved = ?', 1);
      return $select->limit(1)
          ->query()
          ->fetchColumn();
    }

    /**
     * Gets a proxy object for the comment handler
     *
     * @return Engine_ProxyObject
     **/
    public function comments()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
    }


    /**
     * Gets a proxy object for the like handler
     *
     * @return Engine_ProxyObject
     **/
    public function likes()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
    }
    public function follow()
    {
        return true;
    }
    protected function _postDelete()
    {
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $photoSelect = $photoTable->select()
            ->where('album_id = ?', $this->getIdentity())
        ;
        foreach( $photoTable->fetchAll($photoSelect) as $photo ) {
            $photo->skipAlbumDeleteHook = true;
            try {
                $photo->delete();
            } catch( Exception $e ) {
                // Silence
            }
        }

        parent::_postDelete();
    }
}
