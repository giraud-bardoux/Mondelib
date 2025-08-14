<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Form_Admin_Settings_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'perpage', array(
      'label' => 'Polls Per Page',
      'description' => 'How many polls will be shown per page? (Enter a number between 1 and 999)',
      'validators' => array(
        array('Int', true),
        array('LessThan', true, 999),
        new Engine_Validate_AtLeast(1),
      ),
      'value' => 10,
    ));

    $this->addElement('Text', 'maxoptions', array(
      'label' => 'Maximum Options',
      'description' => 'How many possible poll answers do you want to permit?',
      'value' => 15,
      'validators' => array(
        array('Int', true),
        array('LessThan', true, 100),
        new Engine_Validate_AtLeast(2),
      ),
    ));

    $this->addElement('Select', 'canchangevote', array(
      'label' => 'Change Vote?',
      'description' => 'Do you want to permit your members to change their vote?',
      'multiOptions' => array(
        1 => 'Yes, members can change their vote.',
        0 => 'No, members cannot change their vote.',
      ),
      'value' => false,
    ));
    
    $this->addElement('Select', 'poll_allow_unauthorized', array(
        'label' => 'Make unauthorized poll searchable?',
        'description' => 'Do you want to make a unauthorized polls searchable? (If set to no, polls that are not authorized for the current user will not be displayed in the poll search results and widgets.)',
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.allow.unauthorized', 0),
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
    ));
    $this->addElement('Select', 'poll_enable_rating', array(
      'label' => 'Enable Rating',
      'description' => 'Do you want to enable rating for the polls on your website?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.rating', 1),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'onchange' => 'showHideRatingSetting(this.value)', 
    ));
      
    $this->addElement('Text', 'poll_ratingicon', array(
      'label' => 'Font Icon for Rating',
      'description' => 'Enter font icon for rating. You can choose font icon from <a href="https://fontawesome.com/v5/search?m=free&s=solid" target="_blank"> here</a>. Example: fas fa-star',
      'requried' => true,
      'allowEmpty' => false,
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.ratingicon', 'fas fa-star'),
    ));
    $this->poll_ratingicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $this->addElement('Select', 'enable_location', array(
      'label' => 'Enable Location',
      'description' => 'Do you want to enable location for the polls on your website?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.location', 0),
    ));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
