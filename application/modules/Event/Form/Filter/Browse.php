<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Browse.php 9829 2012-11-27 01:13:07Z richard $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Form_Filter_Browse extends Engine_Form
{
  public function init()
  {
    $this->clearDecorators()
      ->setMethod('get')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setAttrib('class', 'filters global_form_box ')
      ;
    
    $this->addElement('Text', 'search_text', array(	
      'label' => 'Search Events:',	
    ));
    
    $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
    if (engine_count($categories) > 0) {
      $categories = array('0' => 'All Categories') + $categories;
      $this->addElement('Select', 'category_id', array(
        'label' => 'Category',
        'multiOptions' => $categories,
        'onchange' => "showSubCategory(this.value);",
      ));
      $this->addElement('Select', 'subcat_id', array(
        'label' => "2nd-level Category",
        'allowEmpty' => true,
        'required' => false,
        'multiOptions' => array('0' => ''),
        'registerInArrayValidator' => false,
        'onchange' => "showSubSubCategory(this.value);"
      ));
      $this->addElement('Select', 'subsubcat_id', array(
        'label' => "3rd-level Category",
        'allowEmpty' => true,
        'registerInArrayValidator' => false,
        'required' => false,
        'multiOptions' => array('0' => '')
      ));
    }
      
    $cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
    //Location
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
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
    

//     $this->addElement('Select', 'view', array(
//       'label' => 'View:',
//       'multiOptions' => array(
//         '' => 'Everyone\'s Events',
//         '1' => 'Only My Friends\' Events',
//       ),
//       'decorators' => array(
//         'ViewHelper',
//         array('HtmlTag', array('tag' => 'dd')),
//         array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
//       ),
//       'onchange' => 'this.form.submit();',
//     ));

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $param = $request->getParams();
    $filter = !empty($param['filter']) ? $param['filter'] : 'future';

    $orderOptions['starttime'] = 'Start Time';
    if( $filter == 'past') {
			$orderOptions['endtime'] = 'Recently Ended';
    }
		$orderOptions = array_merge($orderOptions, array(
			'modified_date' => 'Recently Updated',
			'view_count' => 'Most Viewed',
			'like_count' => 'Most Liked',
			'comment_count' => 'Most Commented',
			'member_count' => 'Most Popular',
			'atoz' => 'A to Z',
			'ztoa' => 'Z to A',
		));
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('event.enable.rating', 1)) {
      $orderOptions['rating'] = 'Highest Rated';
    }

    $this->addElement('Select', 'order', array(
      'label' => 'List By:',
      'multiOptions' => $orderOptions,
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
