<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Edit.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_Media_Edit extends Engine_Form
{
  public function init()
  {
    $this
      //->setTitle('Edit Photo')
      ->setMethod('POST')
      ->setAction($_SERVER['REQUEST_URI'])
      ->setAttrib('class', 'global_form_popup')
      ;
      
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));

    // $this->addElement('Text', 'tags', array(
    //   'label'=>'Tags (Keywords)',
    //   'autocomplete' => 'off',
    //   'filters' => array(
    //     'StripTags',
    //     new Engine_Filter_Censor(),
    //   ),
    // ));

    $this->addElement('Textarea', 'description', array(
      'label' => 'Description',
      'rows' => 2,
      'cols' => 120,
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
      ),
    ));

    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
      'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
      'prependText' => ' or ',
      'label' => 'cancel',
      'link' => true,
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addDisplayGroup(array(
      'execute',
      'cancel'
    ), 'buttons');
  }
}
