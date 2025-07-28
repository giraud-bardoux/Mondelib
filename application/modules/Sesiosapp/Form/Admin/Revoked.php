<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Revoked.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Form_Admin_Revoked extends Engine_Form {
  public function init() {
    
    $this
            ->setTitle('Revoke Token')
            ->setDescription("Are you sure want to revoke token of this user. After revoking token this token user will not be able to access the app.")
            ->setMethod('POST');;
		
		$this->addElement('Hidden', 'id', array());
    $this->addElement('Button', 'submit', array(
      'label' => 'Revoke',
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
