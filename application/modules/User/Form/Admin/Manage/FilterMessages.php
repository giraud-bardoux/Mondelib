<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhoneMessages.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Manage_FilterMessages extends Engine_Form
{
  public function init()
  {
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    $username = new Zend_Form_Element_Text('username');
    $username
      ->setLabel('User Name')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

    
    $message = new Zend_Form_Element_Text('message');
    $message
      ->setLabel('Message')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));
      
    $interval = new Zend_Form_Element_Select('interval');
    $interval
      ->setLabel('Time Duration')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(''=>'','today'=>'Today','sevendays'=>'Last 7 Days','specific'=>'Specific Time'));

    $start = new Engine_Form_Element_CalendarDateTime('starttime');
    $start->setLabel("From");
    $start->setAllowEmpty(true);
    $start->setRequired(false);

    $end = new Engine_Form_Element_CalendarDateTime('endtime');
    $end->setLabel("To");
    $end->setRequired(true);
    $end->setAllowEmpty(false);

    $type = new Zend_Form_Element_Select('type');
    $type
      ->setLabel('Dependent On')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(''=>'','memberlevel'=>'Member Levels','profiletype'=>'Profile Types'));
    
    
    // Element: profile_type
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      $options_array = array();
      foreach( $options as $value )
        $options_array[] = $value['option_id'];
      if( engine_count($options) > 1 ) {
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['required']);
        $profiletype = new Zend_Form_Element_Select('profiletype');
       $profiletype
        ->setLabel('Profile Type')
        ->clearDecorators()
        ->addDecorator('ViewHelper')
        ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
        ->addDecorator('HtmlTag', array('tag' => 'div'))
        ->setMultiOptions($options['options']['multiOptions']);
      } else if( engine_count($options) == 1 ) {
        
      }
    }

    $levelOptions = array();
    $levelOptions[0] = "All member levels";
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if($level->level_id == 5) continue;
      $levelOptions[$level->level_id] = $level->getTitle();
    }

    $memberlevel = new Zend_Form_Element_Select('memberlevel');
    $memberlevel
      ->setLabel('Member Level')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions($levelOptions);

    $submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit
      ->setLabel('Search')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
      ->addDecorator('HtmlTag2', array('tag' => 'div'));

    $this->addElement('Hidden', 'order', array(
      'order' => 10001,
    ));

    $this->addElement('Hidden', 'order_direction', array(
      'order' => 10002,
    ));

    $this->addElement('Hidden', 'user_id', array(
      'order' => 10003,
    ));

    $this->addElements(array(
      $username,
      $message,
      $interval,
      $start,
      $end,
      $type,
      $memberlevel,
      $profiletype,
      $submit,
    ));

    // Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }
}
