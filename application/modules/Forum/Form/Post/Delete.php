<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Delete.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Form_Post_Delete extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Delete Post')
      ->setDescription('Are you sure you want to delete this post?');

    $this->addElement('Hash', 'token');

    $this->addElement('Button', 'submit', array(
      'label' => 'Delete Post',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'link' => true,
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper',
      ),
      'onclick' => 'parent.Smoothbox.close();'
    ));

    $this->addDisplayGroup(array(
      'submit',
      'cancel'
    ), 'buttons', array(
      'decorators' => array(
        'FormElements'
      )
    ));

    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))->setMethod('POST');
  }
}
