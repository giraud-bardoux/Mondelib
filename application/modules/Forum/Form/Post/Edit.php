<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Form_Post_Edit extends Engine_Form
{
  public $_error = array(); 
  protected $_post;

  public function setPost($post)
  {
    $this->_post = $post;
 
  }

  public function init()
  {   
    $this->setMethod("POST");
    $this->setTitle('Edit Post');
    $this->setAttrib('name', 'forum_post_edit')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $allowHtml = (bool) $settings->getSetting('forum_html', 0);
    $allowBbcode = (bool) $settings->getSetting('forum_bbcode', 0);

    if( !$allowHtml ) {
      $filter = new Engine_Filter_HtmlSpecialChars();
    } else {
      $filter = new Engine_Filter_Html();
      $filter->setForbiddenTags();
      $allowedTags = array_map('trim', explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'forum', 'commentHtml')));
      $filter->setAllowedTags($allowedTags);
    }

    if( $allowHtml || $allowBbcode ) {
      $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'upload-photo'), 'default', true);

      $editorOptions = array(
        'uploadUrl' => $uploadUrl
      );

      $this->addElement('TinyMce', 'body', array(
        'disableLoadDefaultDecorators' => true,
        'required' => true,
        'editorOptions' => $editorOptions,
        'allowEmpty' => false,
        'decorators' => array('ViewHelper'),
        'filters' => array(
          new Engine_Filter_Censor(),
        ),
      ));
    } else {
      $this->addElement('textarea', 'body', array(
        'required' => true,
        'attribs' => array(
          'rows' => 24,
          'cols' => 80,
          'style' => 'width:553px; max-width:553px; height:158px;'
        ),
        'allowEmpty' => false,
        'filters' => array(
          $filter,
          new Engine_Filter_Censor(),
        ),
      ));
    }
    
    if( !empty($this->_post->file_id) ) {
      $photoDeleteElement = new Engine_Form_Element_Checkbox('photo_delete', array('label'=>'This post has a photo attached. Do you want to delete it?'));
      $photoDeleteElement->setAttrib('onchange', 'updateUploader()');
      $this->addElement($photoDeleteElement);
      $this->addDisplayGroup(array('photo_delete'), 'photo_delete_group');    
    }

    // Photo
    $fileElement = new Engine_Form_Element_File('photo', array(
      'label' => 'Attach a New Photo (optional)',
      'size' => '40'
    ));
    $this->addElement($fileElement);
    $this->addDisplayGroup(array('photo'), 'photo_group');

    if( !empty($this->_post->file_id) ) {
      $this->getDisplayGroup('photo_group')->getDecorator('HtmlTag')->setOption('style', 'display:none;');
    }
    
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $buttonGroup = $this->getDisplayGroup('buttons');
    $buttonGroup->addDecorator('DivDivDivWrapper');
  }
}
