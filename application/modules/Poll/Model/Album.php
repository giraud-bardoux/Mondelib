<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Album.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Model_Album extends Core_Model_Item_Collection
{
  protected $_parent_type = 'poll';

  protected $_owner_type = 'poll';

  protected $_children_types = array('poll_photo');

  protected $_collectible_type = 'poll_photo';

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'poll_profile',
      'reset' => true,
      'id' => $this->getPoll()->getIdentity(),
      //'album_id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getPoll()
  {
    return $this->getOwner();
    //return Engine_Api::_()->getItem('poll', $this->poll_id);
  }

  public function getAuthorizationItem()
  {
    return $this->getParent('poll');
  }

  protected function _delete()
  {
    // Delete all child posts
    $photoTable = Engine_Api::_()->getItemTable('poll_photo');
    $photoSelect = $photoTable->select()->where('album_id = ?', $this->getIdentity());
    foreach( $photoTable->fetchAll($photoSelect) as $pollPhoto ) {
      $pollPhoto->delete();
    }

    parent::_delete();
  }
}
