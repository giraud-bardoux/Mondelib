<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Seo.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_Filter extends Engine_Form {

  public function init() {
  
    $this->clearDecorators()
            ->addDecorator('FormElements')
            ->addDecorator('Form')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this->setAttribs(array(
      'id' => 'filter_form',
      'class' => 'global_form_box',
    ))->setMethod('GET');

    $displayname = new Zend_Form_Element_Text('displayname');
    $displayname->setLabel('Page Name')
              ->clearDecorators()
              ->addDecorator('ViewHelper')
              ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
              ->addDecorator('HtmlTag', array('tag' => 'div'));

    $title = new Zend_Form_Element_Text('title');
    $title->setLabel('Meta Title')
          ->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
          ->addDecorator('HtmlTag', array('tag' => 'div'));

    $description = new Zend_Form_Element_Text('description');
    $description->setLabel('Meta Description')
          ->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
          ->addDecorator('HtmlTag', array('tag' => 'div'));

    $submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit->setLabel('Search')
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

    $this->addElement('Hidden', 'page_id', array(
        'order' => 10003,
    ));
    $this->addElements(array(
      $displayname,
      $title,
      $description,
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
