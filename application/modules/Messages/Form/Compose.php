<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Compose.php 10246 2014-05-30 21:34:20Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Form_Compose extends Engine_Form
{
  public function init()
  {
    $to = Zend_Controller_Front::getInstance()->getRequest()->getParam('to', 0);
    $multi = Zend_Controller_Front::getInstance()->getRequest()->getParam('multi', 'user');
    $format = Zend_Controller_Front::getInstance()->getRequest()->getParam('format');
    
    $this->setTitle('Compose Message');
    if(empty($to))
    $this->setDescription('Create your new message with the form below. Your message can be addressed to up to 10 recipients.')
       ->setAttrib('id', 'messages_compose');
    $user = Engine_Api::_()->user()->getViewer();
    $userLevel = $user->level_id;

    if(empty($to)) {
      // init to
      $this->addElement('Text', 'to', array(
          'label'=>'Send To',
          'placeholder' => 'Start typing...',
          'required' => true,
          'allowEmpty' => false,
          'autocomplete'=>'off'));

      Engine_Form::addDefaultDecorators($this->to);

      // Init to Values
      $this->addElement('Hidden', 'toValues', array(
        'label' => 'Send To',
  //       'required' => true,
  //       'allowEmpty' => false,
        'validators' => array(
          'NotEmpty'
        ),
        'filters' => array(
          'HtmlEntities'
        ),
      ));
      Engine_Form::addDefaultDecorators($this->toValues);
    } else {
      $user = Engine_Api::_()->getItem($multi, $to);
      $this->addElement('Dummy', 'to', array(
          'content'=>'<div id="to-wrapper" class="form-wrapper"><div id="to-label" class="form-label"><label for="title" class="optional">Send To</label></div></div><div class="tag tag_'.$multi.'" data-value="1">'.$user->getTitle().'</div>',
      ));
      $this->addElement('Hidden', 'toValues', array(
        'value' => $multi.'_'.$to,
      ));
    }
    // init title
    $this->addElement('Text', 'title', array(
      'label' => 'Subject',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));

    // init body - editor
    $editor = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('messages', $userLevel, 'editor');

    if( $editor == 'editor' ) {
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
        'label' => 'Message',
        'required' => true,
        'allowEmpty' => false,
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
    
    if(!empty($to)) {
      // Buttons
      $this->addElement('Button', 'submit', array(
        'label' => 'Send Message',
        'type' => 'submit',
        'class' => 'compose_submit',
        'ignore' => true,
        'decorators' => array(
          'ViewHelper'
        )
      ));
      if($format == 'smoothbox') {
        $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'link' => true,
          'prependText' => ' or ',
          'href' => '',
          'ignore' => true,
          'onclick' => 'parent.Smoothbox.close();',
          'decorators' => array(
            'ViewHelper'
          )
        ));
        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $this->getDisplayGroup('buttons');
      } else if(!empty($multi) && $format != 'smoothbox') {
        $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'link' => true,
          'prependText' => ' or ',
          'href' => $user->getHref(),
          'ignore' => true,
          'decorators' => array(
            'ViewHelper'
          )
        ));
        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $this->getDisplayGroup('buttons');
      }
    } else {
      // init submit
      $this->addElement('Button', 'submit', array(
        'label' => 'Send Message',
        'type' => 'submit',
        'class' => 'compose_submit',
        'ignore' => true
      ));
    }
  }
}
