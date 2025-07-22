<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9802 2012-10-20 16:56:13Z pamela $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'event_page', array(
      'label' => 'Events Per Page',
      'description' => 'How many events will be shown per page?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('event.page', 12),
    ));

    $this->addElement('Select', 'event_allow_unauthorized', array(
        'label' => 'Make unauthorized events searchable?',
        'description' => 'Do you want to make a unauthorized events searchable? (If set to no, events that are not authorized for the current user will not be displayed in the event search results and widgets.)',
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized',0),
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
    ));
    $this->addElement('Select', 'event_enable_rating', array(
      'label' => 'Enable Rating',
      'description' => 'Do you want to enable rating for the events on your website?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('event.enable.rating', 1),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'onchange' => 'showHideRatingSetting(this.value)', 
    ));
    
    $this->addElement('Text', 'event_ratingicon', array(
      'label' => 'Font Icon for Rating',
      'description' => 'Enter font icon for rating. You can choose font icon from <a href="https://fontawesome.com/v5/search?m=free&s=solid" target="_blank"> here</a>. Example: fas fa-star',
      'requried' => true,
      'allowEmpty' => false,
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('event.ratingicon', 'fas fa-star'),
    ));
    $this->event_ratingicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
