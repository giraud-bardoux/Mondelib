<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Blog.php 10072 2013-07-24 22:38:42Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Model_Blog extends Core_Model_Item_Abstract
{
    // Properties

    protected $_parent_type = 'user';

    //protected $_owner_type = 'user';

    protected $_searchTriggers = array('title', 'body', 'search');

    protected $_parent_is_owner = true;
    public function getAttachmentClass(){
      return " feed_attachments_content_item ";
    }

    // General

    /**
     * Gets an absolute URL to the page to view this item
     *
     * @return string
     */
    public function getHref($params = array())
    {
        $slug = $this->getSlug();

        $params = array_merge(
            array(
                'route' => 'blog_entry_view',
                'reset' => true,
                'user_id' => $this->owner_id,
                'blog_id' => $this->blog_id,
                'slug' => $slug,
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
        // @todo decide how we want to handle multibyte string functions
        $tmpBody = strip_tags($this->body);
        return (Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody);
    }

    public function getKeywords($separator = ' ')
    {
        $keywords = array();
        foreach ($this->tags()->getTagMaps() as $tagmap) {
            $tag = $tagmap->getTag();
            if ($tag === null) {
                continue;
            }
            $keywords[] = $tag->getTitle();
        }

        if (null === $separator) {
            return $keywords;
        }

        return join($separator, $keywords);
    }

    public function getPhotoUrl($type = null)
    {
        if ($this->photo_id) {
            return parent::getPhotoUrl($type);
        }
        return $this->getOwner()->getPhotoUrl($type);
    }


    // Interfaces

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

    /**
     * Gets a proxy object for the tags handler
     *
     * @return Engine_ProxyObject
     **/
    public function tags()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
    }

    public function follow()
    {
        return true;
    }

    public function setPhoto($photo)
    {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
        } elseif (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
        } elseif (is_string($photo) && file_exists($photo)) {
            $file = $photo;
        } else {
            throw new Blog_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
        }

        $name = basename($file);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_type' => 'blog',
            'parent_id' => $this->getIdentity()
        );

        // Save
        $storage = Engine_Api::_()->storage();

        // Resize image (main)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(720, 720)
            ->write($path . '/m_' . $name)
            ->destroy();

        // Resize image (profile)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(400, 400)
            ->write($path . '/p_' . $name)
            ->destroy();

        // Store
        $iMain = $storage->create($path . '/m_' . $name, $params);
        $iProfile = $storage->create($path . '/p_' . $name, $params);

        $iMain->bridge($iProfile, 'thumb.profile');

        // Remove temp files
        @unlink($path . '/p_' . $name);
        @unlink($path . '/m_' . $name);

        // Update row
        $this->modified_date = date('Y-m-d H:i:s');
        $this->photo_id = $iMain->getIdentity();
        $this->save();

        return $this;
    }
    public function getParentItem()
    {
        if (!$this->parent_type || !$this->parent_id || $this->parent_type == "user")
            return false;
        return Engine_Api::_()->getItem($this->parent_type, $this->parent_id) ?? false;
    }

    public function getCategoryItem()
    {
        if (!$this->category_id)
            return false;
        return Engine_Api::_()->getItem('blog_category', $this->category_id);
    }
}
