<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Subscribersearch.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Subscribersearch extends Engine_Form {
  public function init() {
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;
    $this
            ->setTitle('App OAuth Tokens')
            ->setDescription("")
            ->setMethod('GET');;
		
		$this->addElement('Text', "user", array(
      'label' => 'User Title',
      'description' => "",
      'allowEmpty' => true,
      'required' => false,
      'value' => '',
		));
		
      $this->addElement('Text', 'email', array(
          'label' => 'Email',
          'description' => '',
          'value' => '',
      ));    
      $this->addElement('Select', 'revoked', array(
          'label' => 'Revoked',
          'description' => '',
          'multiOptions'=>array(''=>'','0'=>'No','1'=>'Yes'),
      ));     
      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Search',
          'type' => 'submit',
          'ignore' => true
      ));
  }
}
