<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Membersearch.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Form_Membersearch extends Fields_Form_Search {
public function setMemberType($title) {
    $this->_memberType = $title;
    return $this;
  }

  public function getMemberType() {
    return $this->_memberType;
  }
  
  public function init() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $identity = $view->identity;

      $this->getMemberTypeElement($identity);    


      $this->addElement('Text', 'search_text', array(
          'label' => 'Search Members/Keyword:',
          'order' => -999999,
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li', 'class' => ''))
          ),
      ));

    parent::init();
    $this->addElement('Checkbox', 'has_photo', array(
          'label' => 'Only Members With Photos',
          'order' => '9998',
          'decorators' => array(
              'ViewHelper',
              array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
              array('HtmlTag', array('tag' => 'li', 'class' => 'only_member member_photo'))
          ),
      ));
    

      $this->addElement('Checkbox', 'is_online', array(
          'label' => 'Only Online Members',
          'order' => '9997',
          'decorators' => array(
              'ViewHelper',
              array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
              array('HtmlTag', array('tag' => 'li', 'class' => 'only_member online_member'))
          ),
      ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Search',
        'type' => 'submit',
        'order' => '9999',
        'decorators' => array(
            'ViewHelper',
            array('Label', array('tag' => 'span')),
            array('HtmlTag', array('tag' => 'li', 'class' => 'submit_button'))
        ),
    ));
  }

  public function getMemberTypeElement() {

    $multiOptions = array('' => ' ');
    $profileTypeFields = Engine_Api::_()->fields()->getFieldsObjectsByAlias($this->_fieldType, 'profile_type');
    if (engine_count($profileTypeFields) !== 1 || !isset($profileTypeFields['profile_type']))
      return;
    $profileTypeField = $profileTypeFields['profile_type'];

    $options = $profileTypeField->getOptions();
    
    foreach ($options as $option) {
      $multiOptions[$option->option_id] = $option->label;
    }
    $classForHide = $this->getMemberType() == 'hide' ? $hideClass : '';
    $this->addElement('Select', 'profile_type', array(
        'label' => 'Member Type:',
        'order' => -1000001,
        'class' =>
        'field_toggle' . ' ' .
        'parent_' . 0 . ' ' .
        'option_' . 0 . ' ' .
        'field_' . $profileTypeField->field_id . ' ' . $classForHide . ' ',
        'onchange' => 'changeFields($(this));',
        'decorators' => array(
            'ViewHelper',
            array('Label', array('tag' => 'span')),
            array('HtmlTag', array('tag' => 'li'))
        ),
        'multiOptions' => $multiOptions,
    ));
    return $this->profile_type;
  }

}
