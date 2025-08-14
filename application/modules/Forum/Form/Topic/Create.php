<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 10266 2014-06-06 23:01:42Z lucas $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Form_Topic_Create extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->setMethod("POST");
    $this->setTitle('Post Topic');
    $this->setAttrib('name', 'forum_post_create');
    $this->setAttrib('class', 'global_form form_submit_ajax');

    $this->addElement('Text', 'title', array(
      'label' => 'Topic Title',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
      ),
      'validators' => array(
        array('StringLength', true, array(1, 255)),
      ),
    ));
    $viewer = Engine_Api::_()->user()->getViewer();

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

//       if( $allowHtml ) {
//         $editorOptions = array_merge($editorOptions, array('html' => 1));
//       } else {
//         $editorOptions = array_merge($editorOptions, array('html' => 0, 'bbcode' => 1));
//       }

      $this->addElement('TinyMce', 'body', array(
        'label' => 'Topic Description',
        'disableLoadDefaultDecorators' => true,
        'editorOptions' => $editorOptions,
        'required' => true,
        'allowEmpty' => false,
        'decorators' => array('ViewHelper'),
        'filters' => array(
          //$filter,
          new Engine_Filter_Censor(),
        ),
      ));
    } else {
      $this->addElement('textarea', 'body', array(
        'required' => true,
        'attribs' => array('rows' => 24, 'cols' => 80, 'style' => 'width:553px; max-width:553px;height:158px;'),
        'allowEmpty' => false,
        'filters' => array(
          $filter,
          new Engine_Filter_Censor(),
        ),
      ));
    }

    $this->addElement('Checkbox', 'watch', array(
      'label' => 'Send me notifications when other members reply to this topic.',
      'value' => '1',
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Post Topic',
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
