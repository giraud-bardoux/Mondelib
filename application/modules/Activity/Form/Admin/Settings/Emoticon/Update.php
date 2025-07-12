<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Update.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_Admin_Settings_Emoticon_Update extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Add emoticon')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('id', 'emoticon_update_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Text', 'name', array(
      'label' => 'Emoticon Name',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '25'))
      )
    ));

    $this->addElement('Text', 'symbol', array(
      'label' => 'Emoticon Symbol',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '25'))
      )
    ));

    $this->addElement('File', 'Filedata', array(
      'label' => 'Choose a emoticon',
      'destination' => APPLICATION_PATH . '/application/modules/Activity/externals/emoticons/images',
      'accept' => 'image/*',
      'validators' => array(
        array('Extension', false, 'jpg,png,gif,jpeg,webp'),
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
