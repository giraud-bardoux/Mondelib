<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Album_Form_Admin_ManagePhotos_Filter extends Engine_Form {

  public function init() {
  
		parent::init();
		
    $this->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    $titlename = new Zend_Form_Element_Text('title');
    $titlename
      ->setLabel('Title')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

		$owner_name = new Zend_Form_Element_Text('owner_name');
    $owner_name
      ->setLabel('Owner')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));


		$date = new Zend_Form_Element_Text('creation_date');
    $date->setLabel('Date')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));
      
    
//     $allModules = Engine_Api::_()->getDbTable('modules', 'core')->getAllModules();
//     unset($allModules['album']);
//     $module = new Zend_Form_Element_Select('module');
//     $module->setLabel('Module Name')
//       ->clearDecorators()
//       ->addDecorator('ViewHelper')
//       ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
//       ->addDecorator('HtmlTag', array('tag' => 'div'))
//       ->setMultiOptions($allModules)
//       ->setValue('');
    

    $submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit->setLabel('Search')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
      ->addDecorator('HtmlTag2', array('tag' => 'div'));
		
		$arrayItem = array();
		$arrayItem = !empty($titlename)?	array_merge($arrayItem,array($titlename)) : '';
		$arrayItem = !empty($owner_name) ?	array_merge($arrayItem,array($owner_name)) : $arrayItem;
		$arrayItem = !empty($date)?	array_merge($arrayItem,array($date)) : $arrayItem;
		//$arrayItem = !empty($module)?	array_merge($arrayItem,array($module)) : $arrayItem;

		$arrayItem = !empty($submit)?	array_merge($arrayItem,array($submit)) : '';
    $this->addElements($arrayItem);
  }
}
