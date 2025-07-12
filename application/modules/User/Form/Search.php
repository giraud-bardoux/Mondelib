<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Search.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Search extends Fields_Form_Search
{
  public function init()
  {
    // Add custom elements
    $this->getMemberTypeElement();
    $this->getDisplayNameElement();
    $this->getAdditionalOptionsElement();


    parent::init();

    $this->loadDefaultDecorators();
    $this->setAttribs(array('class' => 'global_form_box field_search_criteria'));

    $this->setMethod('get');
    $this->getDecorator('HtmlTag')->setOption('class', 'browsemembers_criteria');
  }

  public function getMemberTypeElement()
  {
    $multiOptions = array('' => ' ');
    $profileTypeFields = Engine_Api::_()->fields()->getFieldsObjectsByAlias($this->_fieldType, 'profile_type');
    if( engine_count($profileTypeFields) !== 1 || !isset($profileTypeFields['profile_type']) ) return;
    $profileTypeField = $profileTypeFields['profile_type'];
    
    $options = $profileTypeField->getOptions(array('profiletypeshow' => 1));

    if( engine_count($options) <= 1 ) {
      if( engine_count($options) == 1 ) {
        $this->_topLevelId = $profileTypeField->field_id;
        $this->_topLevelValue = $options[0]->option_id;
      }
      return;
    }

    foreach( $options as $option ) {
      $multiOptions[$option->option_id] = $option->label;
    }

    $this->addElement('Select', 'profile_type', array(
      'label' => 'Member Type',
      'order' => -1000001,
      'class' =>
        'field_toggle' . ' ' .
        'parent_' . 0 . ' ' .
        'option_' . 0 . ' ' .
        'field_'  . $profileTypeField->field_id  . ' ',
      'onchange' => 'changeFields(scriptJquery(this));',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
      'multiOptions' => $multiOptions,
    ));
    return $this->profile_type;
  }

  public function getDisplayNameElement()
  {
    $this->addElement('Text', 'displayname', array(
      'label' => 'Name',
      'order' => -1000000,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
      //'onkeypress' => 'return submitEnter(event)',
    ));
    
    
    // Element: Gender
    // $this->addElement('Select', 'gender', array(
    //   'label' => 'Gender',
    //   'multiOptions' => array(
    //     '' => '',
    //     'male' => 'Male',
    //     'female' => 'Female',
    //     'other' => 'Other',
    //   ),
    //   'decorators' => array(
    //     'ViewHelper',
    //     array('Label', array('tag' => 'span')),
    //     array('HtmlTag', array('tag' => 'li'))
    //   ),
    // ));
    
    $enablesigupfields = (array) json_decode(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language","location"]'));
    
    if(isset($enablesigupfields) && engine_in_array('location', $enablesigupfields) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
      
      $cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
      //Location
      $this->addElement('Text', 'location', array(
        'label' => 'Location',
        'filters' => array(
          new Engine_Filter_Censor(),
          new Engine_Filter_HtmlSpecialChars(),
        ),
        'value' => !empty($cookiedata['location']) ? $cookiedata['location'] : '',
      ));
      
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
        $this->addElement('Hidden', 'lat', array('order' => 3000, 'value' => !empty($cookiedata['lat']) ? $cookiedata['lat'] : ''));
        $this->addElement('Hidden', 'lng', array('order' => 3001, 'value' => !empty($cookiedata['lng']) ? $cookiedata['lng'] : ''));
        
        $this->addElement('Select', 'miles', array(
          'label' => !empty(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.search.type', 1)) ? 'Miles' : 'Kilometer',
          'allowEmpty' => true,
          'required' => false,
          'multiOptions' => array('0' => '', '1' => '1', '5' => '5', '10' => '10', '20' => '20', '50' => '50', '100' => '100', '200' => '200', '500' => '500', '1000' => '1000'),
          'value' => 1000,
          'registerInArrayValidator' => false,
        ));
      }
    }
    //return $this->displayname;
  }

  public function getAdditionalOptionsElement()
  {
    $subform = new Zend_Form_SubForm(array(
      'name' => 'extra',
      'order' => 1000000,
      'decorators' => array(
        'FormElements',
      )
    ));
    Engine_Form::enableForm($subform);

    $subform->addElement('Checkbox', 'has_photo', array(
      'label' => 'Only Members With Photos',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $subform->addElement('Checkbox', 'is_online', array(
      'label' => 'Only Online Members',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $subform->addElement('Button', 'done', array(
      'label' => 'Search',
      'type' => 'submit',
      'ignore' => true,
    ));

    $this->addSubForm($subform, $subform->getName());

    return $this;
  }
}
