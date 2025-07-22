<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');


    $this->addElement('Text', 'album_page', array(
      'label' => 'Albums Per Page',
      'description' => 'How many albums will be shown per page?',
      'value' => 20,
    ));
    
    $this->addElement('Text', 'photo_page', array(
      'label' => 'Photos Per Page',
      'description' => 'How many photos will be shown per page?',
      'value' => 12,
    ));

    $this->addElement('Select', 'album_searchable', array(
      'label' => 'Make default albums searchable?',
      'description' => 'Do you want to make a default album searchable ? (If set to no,'
        . ' albums that get created by default like Blog Photos, Forum Photos, etc will not'
        . ' be displayed in the album search results and widgets, but will still be displayed in'
        . ' the Profile Albums tab on a users profile page.)',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 0,
    ));

    $this->addElement('Select', 'album_defaultsearch', array(
      'label' => 'Default Photo Album Order',
      'description' => 'Choose from the below dropdown the default order of photo album which is shown on the Photo View Page.',
      'multiOptions' => array(
        2 => 'Newest',
        1 => 'Oldest',
        0 => 'Set Order'
      ),
      'value' => 0,
    ));

    $this->addElement('Select', 'album_allow_unauthorized', array(
        'label' => 'Make unauthorized album searchable?',
        'description' => 'Do you want to make a unauthorized albums searchable? (If set to no, albums that are not authorized for the current user will not be displayed in the album search results and widgets.)',
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('album.allow.unauthorized',0),
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
    ));
    $this->addElement('Select', 'album_enable_rating', array(
      'label' => 'Enable Rating',
      'description' => 'Do you want to enable rating for the albums on your website?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'onchange' => 'showHideRatingSetting(this.value)', 
    ));
    
    $this->addElement('Text', 'album_ratingicon', array(
      'label' => 'Font Icon for Rating',
      'description' => 'Enter font icon for rating. You can choose font icon from <a href="https://fontawesome.com/v5/search?m=free&s=solid" target="_blank"> here</a>. Example: fas fa-star',
      'requried' => true,
      'allowEmpty' => false,
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('album.ratingicon', 'fas fa-star'),
    ));
    $this->album_ratingicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Select', 'album_enable_location', array(
      'label' => 'Enable Location',
      'description' => 'Do you want to enable location for the albums on your website?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.location', 0),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
    ));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
