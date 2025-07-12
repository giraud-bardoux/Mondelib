<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Share.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_Share extends Engine_Form
{
  public function init()
  {
  
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type');
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
    $action_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('action_id');
    if(isset($action_id)) {
      $item = Engine_Api::_()->getItem('activity_action', $action_id);
    } else {
      $item = Engine_Api::_()->getItem($type, $id);
    }

    $this->setTitle('Share')
      //->setDescription('Share this by re-posting it with your own message.')
      ->setMethod('POST')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

      $this->addElement('dummy', 'socialshare', array(
        'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/Activity/views/scripts/_socialShare.tpl',
          'class' => 'form element',
          'item' => isset($item) ? $item : '',
        )))
      ));
      
    $this->addElement('Textarea', 'body', array(
      'label' => "Share this by re-posting it with your own message",
      //'required' => true,
      //'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_EnableLinks(),
        new Engine_Filter_Censor(),
      ),
    ));
    
    // Buttons
    $buttons = array();

    $translate = Zend_Registry::get('Zend_Translate');

    // Twitter
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
    if( 'publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable &&
        $twitterTable->getApi() &&
        $twitterTable->isConnected() ) {
      $this->addElement('Dummy', 'post_to_twitter', array(
        'content' => '
          <span href="javascript:void(0);" class="composer_twitter_toggle" onclick="toggleTwitterShareCheckbox();">
            <span class="composer_twitter_tooltip">
              ' . $translate->translate('Publish this on X') . '
            </span>
            <input type="checkbox" name="post_to_twitter" value="1" style="display:none;">
          </span>',
      ));
      $this->getElement('post_to_twitter')->clearDecorators();
      $buttons[] = 'post_to_twitter';
    }


    $this->addElement('Button', 'submit', array(
      'label' => 'Share',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
    $buttons[] = 'submit';

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $buttons[] = 'cancel';


    $this->addDisplayGroup($buttons, 'buttons');
    $button_group = $this->getDisplayGroup('buttons');

  }
}
