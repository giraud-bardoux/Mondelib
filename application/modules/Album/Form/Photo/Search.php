<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Search.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Form_Photo_Search extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    parent::init();

    $this->addElement('Text', 'search', array(
      'label' => 'Search Photos:'
    ));

		$orderby = array(
			'creation_date' => 'Most Recent',
			'modified_date' => 'Recently Updated',
			'view_count' => 'Most Viewed',
			'like_count' => 'Most Liked',
			'comment_count' => 'Most Commented',
			'atoz' => 'A to Z',
			'ztoa' => 'Z to A',
		);

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1)) {
      $orderby['rating'] = 'Highest Rated';
    }
    
    $this->addElement('Select', 'sort', array(
      'label' => 'Browse By:',
      'multiOptions' => $orderby,
    ));

    $this->addElement('Hidden', 'tag', array(
    ));

    $this->addElement('Button', 'find', array(
      'type' => 'submit',
      'label' => 'Search',
      'ignore' => true,
      'order' => 10000001,
    ));
  }
}
