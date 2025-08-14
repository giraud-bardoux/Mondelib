<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: WidgetController.php
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Form_Search extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setMethod('GET')
      ;
    
    $this->addElement('Text', 'search', array(
      'label' => 'Search Blogs',
    ));
    
    $orderby = array(
      'creation_date' => 'Most Recent',
      'modified_date' => 'Recently Updated',
      'view_count' => 'Most Viewed',
      'like_count' => 'Most Liked',
      'comment_count' => 'Most Commented',
      'atoz' => 'A to Z',
      'ztoa' => 'Z to A',
    );
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.rating', 1)) {
      $orderby['rating'] = 'Highest Rated';
    }
    $this->addElement('Select', 'orderby', array(
      'label' => 'Browse By',
      'multiOptions' => $orderby,
      // 'onchange' => 'this.form.submit();',
    ));

    $this->addElement('Select', 'draft', array(
      'label' => 'Show',
      'multiOptions' => array(
        '' => 'All Entries',
        '0' => 'Only Published Entries',
        '1' => 'Only Drafts',
      ),
      // 'onchange' => 'this.form.submit();',
    ));

    $categories = Engine_Api::_()->getDbtable('categories', 'blog')->getCategoriesAssoc();
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

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.location', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
      
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

    $this->addElement('Button', 'find', array(
      'type' => 'submit',
      'label' => 'Search',
      'ignore' => true,
      'order' => 10000001,
    ));

    $this->addElement('Hidden', 'page', array(
      'order' => 100
    ));

    $this->addElement('Hidden', 'tag', array(
      'order' => 101
    ));

    $this->addElement('Hidden', 'start_date', array(
      'order' => 102
    ));

    $this->addElement('Hidden', 'end_date', array(
      'order' => 103
    ));
  }
}
