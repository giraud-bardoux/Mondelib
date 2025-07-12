<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Reply.php 9835 2012-11-29 00:35:00Z pamela $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Form_Reply extends Engine_Form
{
  public function init()
  {
    $this
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $user = Engine_Api::_()->user()->getViewer();
    // init body - editor
    $editor = Engine_Api::_()->getDbtable('permissions', 'authorization')
      ->getAllowed('messages', $user->level_id, 'editor');

    if ($editor === 'editor') {

      $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'upload-photo'), 'default', true);

      $editorOptions = array(
        'uploadUrl' => $uploadUrl,
        //'bbcode' => false,
        //'html' => true,
      );

      $this->addElement('TinyMce', 'body', array(
        'disableLoadDefaultDecorators' => true,
        'required' => true,
        'editorOptions' => $editorOptions,
        'allowEmpty' => false,
        'decorators' => array(
            'ViewHelper',
            'Label',
            array('HtmlTag', array('style' => 'display: block;'))),
        'filters' => array(
          //new Engine_Filter_HtmlSpecialChars(),
          new Engine_Filter_Censor(),
        ),
      ));
    } else {
      // init body - plain text
      $this->addElement('Textarea', 'body', array(
        'allowEmpty' => false,
        'required' => true,
        'filters' => array(
          new Engine_Filter_HtmlSpecialChars(),
          new Engine_Filter_Censor(),
          new Engine_Filter_EnableLinks(),
        ),
      ));
    }
    $this->addElement('Hidden', 'fancyalbumuploadfileidsvideo', array(
      'order' => 1000,
    ));
    // init submit
    $this->addElement('Button', 'reply_submit', array(
      'label' => 'Send Reply',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
