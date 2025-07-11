<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Photo.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Edit_Photo extends Engine_Form
{
  public function init()
  {
    $translate = Zend_Registry::get('Zend_Translate');
    
    $this->setTitle("Edit My Photo");
    $this
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('name', 'EditPhoto');

    $this->addElement('Image', 'current', array(
      'label' => 'Current Photo',
      'ignore' => true,
      'decorators' => array(array('ViewScript', array(
        'viewScript' => '_formEditImage.tpl',
        //'viewScript' => '_formImageCrop.tpl',
        'class'      => 'form element',
        'testing' => 'testing'
      )))
    ));
    Engine_Form::addDefaultDecorators($this->current);
    
    $this->addElement('File', 'Filedata', array(
      'label' => 'Choose New Photo',
      'multiFile' => 1,
      'validators' => array(
        array('Count', false, 1),
        // array('Size', false, 612000),
        array('Extension', false, 'jpg,png,gif,jpeg,webp'),
      ),
      'accept' => 'image/*',
      'data-function' => 'uploadSignupPhoto',
    ));

    $this->addElement('Hidden', 'coordinates', array(
      'filters' => array(
        'HtmlEntities',
      )
    ));
    
    //Avatars
    $avatars = Engine_Api::_()->getDbtable('avatars', 'user')->getAvatars(array('enabled' => 1, 'fetchAll' => 1));
    if(engine_count($avatars) > 0) { 
      $user = Engine_Api::_()->user()->getViewer();
      $label = $translate->translate("Choose Avatar");
      if(!empty($user->avatar_id)) {
        $label = $translate->translate("Edit Avatar");
      }
      $this->addElement('Dummy', 'avatar', array(
        'content' => '<div class="editavatar_element"><p class="_sep">or</p> <p class="editavatar_link"><a class="ajaxsmoothbox" href="'.Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'edit', 'id' => $user->getIdentity(), 'action' => 'choose-avatar'), 'user_extended', true).'"><i class="fas fa-user-circle"></i><span>'.$label.'</span></a></p></div>'
      ));
    }

    $this->addElement('Button', 'done', array(
      'label' => 'Save Photo',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addElement('Cancel', 'remove', array(
      'label' => 'remove photo',
      'link' => true,
      'prependText' => ' or ',
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'action' => 'remove-photo',
      )),
      'onclick' => null,
      'class' => 'smoothbox',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addDisplayGroup(array('done', 'remove'), 'buttons');
    
  }
}
