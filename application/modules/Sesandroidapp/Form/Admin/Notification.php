<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Notification.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Notification extends Engine_Form
{
  public function init()
  {
    $this
            ->setTitle('Send Push Notifications')
            ->setDescription('Here you can configure the push notification message and send to all subscribers of your choice');
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    
		$cri = array('all'=>'All Subscribed User','memberlevel'=>'Member Level','network'=>'Network','user'=>'Particular User');    
    $levels = Engine_Api::_()->getDbTable('levels','authorization')->getLevelsAssoc();
    if(!engine_count($levels))
      unset($cri['memberlevel']);
      
     $table = Engine_Api::_()->getItemTable('network');
     $select = $table->select()
      ->where('assignment = ?', 0)
      ->order('title ASC');
    $networks = $table->fetchAll($select);
    
    if(!engine_count($networks))
      unset($cri['network']);
    else{
      foreach($networks as $network)
        $networkArr[$network->getIdentity()] = $network->getTitle();
    }
    $this->addElement('Select', "criteria", array(
      'label' => 'Choose Subscribers',
      'description' => 'Choose from below the subscribers to which you want to send this push notification message.',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions'=>$cri,
      'onchange'=>'criteriaOpen(this.value);',
      'value' => '',
		));
   if(engine_count($levels)){
    $this->addElement('Select', "memberlevel", array(
      'label' => 'Choose Member Level',
      'description' => 'Choose Member Level from below',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions'=>$levels,
      'value' => '',
		));
   }
   if(engine_count($networks) > 0) {
    $this->addElement('Select', "network", array(
      'label' => 'Choose Network',
      'description' => 'Choose Network from below',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions'=>$networkArr,
      'value' => '',
		));
   }
   
   $this->addElement('Text', 'to',array(
        'label'=>'Send To',
        'autocomplete'=>'off'));

    Engine_Form::addDefaultDecorators($this->to);
    $this->addElement('Hidden', 'token_id', array(
      'required' => false,
      'allowEmpty' => true,
      'order' => 5,
    ));
    // Init to Values
    $this->addElement('Hidden', 'toValues', array(
      'label' => 'Send To',
      'required' => false,
      'allowEmpty' => true,
      'order' => 4,
      'validators' => array(
        'NotEmpty'
      ),
      'filters' => array(
        'HtmlEntities'
      ),
    ));
    Engine_Form::addDefaultDecorators($this->toValues);
   
		$this->addElement('Text', "title", array(
      'label' => 'Push Notification Title',
      'description' => 'Enter the title of this push notification.',
      'allowEmpty' => false,
      'required' => true,
      'value' => '',
		));
    
    $this->addElement('Textarea', "description", array(
		'label' => 'Push Notification Message',
		'description' => 'Enter the message of this push notification.',
		'allowEmpty' => true,
		'required' => false,
		'value' => '',
		));
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Sent To',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onClick' => 'parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
