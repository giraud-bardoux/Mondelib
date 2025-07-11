<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Comment.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_Comment extends Engine_Form
{
  public function init()
  {
    $this->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->setAttrib('class', 'global_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'activity',
          'controller' => 'index',
          'action' => 'comment',
        ), 'default'));
        
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->addElement('Textarea', 'body', array(
      'rows' => 1,
      'decorators' => array(
        'ViewHelper'
      ),
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
    ));

    // if( Engine_Api::_()->getApi('settings', 'core')->core_spam_signup ) {
    //   $this->addElement('captcha', 'captcha', Engine_Api::_()->core()->getCaptchaOptions());
    // }
    
    $this->addElement('Hidden', 'show_all_comments', array(
        'value' => Zend_Controller_Front::getInstance()->getRequest()->getParam('show_comments'),
    ));

    $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'ignore' => true,
      'label' => 'Post Comment',
      'decorators' => array(
        'ViewHelper',
      )
    ));
    
    $this->addElement('Hidden', 'action_id', array(
      'order' => 990,
      'filters' => array(
        'Int'
      ),
    ));

    $this->addElement('Hidden', 'return_url', array(
      'order' => 991,
      'value' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array())
    ));    
    
 }

  public function setActionIdentity($action_id)
  {
    $this->setAttrib('id', 'activity-comment-form-'.$action_id)
      ->setAttrib('class', 'activity-comment-form')
      ->setAttrib('style', 'display: none;');
      
    $this->action_id
      ->setValue($action_id)
      ->setAttrib('id', 'activity-comment-id-'.$action_id);
    $this->submit //->getDecorator('HtmlTag')
      ->setAttrib('id', 'activity-comment-submit-'.$action_id)
      ;

    $this->body
      ->setAttrib('id', 'activity-comment-body-'.$action_id)
      ;
      //->setAttrib('onfocus', "document.getElementById('activity-comment-submit-".$action_id."').style.display = 'block';")
      //->setAttrib('onblur', "if( this.value == '' ) { document.getElementById('activity-comment-form-".$action_id."').style.display = 'none'; }");

    return $this;
  }

  public function renderFor($action_id)
  {
    return $this->setActionIdentity($action_id)->render();
  }
}
