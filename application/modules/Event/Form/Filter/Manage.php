<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Manage.php 9989 2013-03-20 01:13:58Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Form_Filter_Manage extends Engine_Form
{
  public function init()
  {
    $this->clearDecorators()
      ->setMethod('get')
      ->setAttrib('class', 'filters global_form_box ')
      ;

    $this->addElement('Text', 'search_text', array(
      'label' => 'Search:',
    ));

    $this->addElement('Select', 'view', array(
      'label' => 'View:',
      'multiOptions' => array(
        '' => 'All My Events',
        '2' => 'Only Events I Lead',
      ),
      'onchange' => 'this.form.submit();',
    ));

    $this->addElement('Button', 'find', array(
      'type' => 'submit',
      'label' => 'Search',
      'ignore' => true,
      'order' => 10000001,
    ));
  }
}
