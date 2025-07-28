<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Report.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Form_Report extends Engine_Form
{
  public function init()
  {
    
    $this->addElement('Select', 'category', array(
      'label' => 'Type',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        '' => '(select)',
        'spam' => 'Spam',
        'abuse' => 'Abuse',
        'inappropriate' => 'Inappropriate Content',
        'licensed' => 'Licensed Material',
        'other' => 'Other',
      ),
    ));
    
    $this->addElement('Hidden', 'subject');
    $this->addElement('Textarea', 'des', array(
      'label' => 'Description',
      'required' => true,
      'allowEmpty' => false,
    ));
    
    

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Submit Report',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

   

  }
}