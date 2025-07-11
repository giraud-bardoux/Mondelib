<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: EditPost.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_EditPost extends Engine_Form
{
  public function init()
  {

    $this
      ->setMethod('POST')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'activity', 'controller' => 'index', 'action' => 'edit'), 'default', true))
      ->setAttrib('class', 'global_form_activity_edit_post')
    ;

    $this->addElement('Textarea', 'body', array(
      'attribs' => array('rows' => 3),
//      'filters' => array(
//        new Engine_Filter_Censor(),
//        new Engine_Filter_Html(array('AllowedTags' => 'br')),
//      ),
    ));

    $privacy = $networkArray = array();
    $defaultViewPrivacy = array(
      'everyone'  => 'Everyone',
      'networks'  => 'Friends or Networks',
      'friends'   => 'Friends Only',
      'onlyme'    => 'Only Me',
    );
    $viewPrivacyLists = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.view.privacy');
    if (!empty($viewPrivacyLists)) {
      foreach ($viewPrivacyLists as $viewPrivacy) {
        $privacyArray[$viewPrivacy] = $defaultViewPrivacy[$viewPrivacy];
      }
    }
    
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
      $enableNetworkList = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0);
      if ($enableNetworkList) {
        $networkLists = Engine_Api::_()->activity()->getNetworks($enableNetworkList, Engine_Api::_()->user()->getViewer());

        if ((is_array($networkLists) || is_object($networkLists)) && engine_count($networkLists)) {
          foreach ($networkLists as $network) {
            $networkArray["network_" . $network->getIdentity()] = $network->getTitle();
          }
        }
      }
    }
    $translate = Zend_Registry::get('Zend_Translate');

    $privacy = array_merge(
      isset($privacyArray) ? $privacyArray : array(),
      isset($networkArray) ? $networkArray : array(),
      isset($networkArray) && Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) ? array("multi_networks" => $translate->translate("Multiple Networks")) : array()
    );

    $this->addElement('Select', 'networkprivacy', array(
      'label' => 'Privacy',
      'multiOptions' => $privacy,
      'onclick' => "setEditPrivacyValue(this.value,action_id.value);"
    ));

    $this->addElement('hidden', 'action_id');

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Edit Post',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'class' => 'feed-edit-content-cancel',
      'href' => 'javascript:void(0);',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
