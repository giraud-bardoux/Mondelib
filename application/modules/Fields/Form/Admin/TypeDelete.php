<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Type.php 9772 2016-12-28 22:25:06Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Admin_TypeDelete extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Delete Profile Type')
      ->setDescription('Are you sure you want to delete the Profile Type?')
      ->setAttrib('class', 'global_form_popup')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setMethod('POST');

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $moduleName = $request->getModuleName();
    $actionName = $request->getActionName();
    $controllerName = $request->getControllerName();

    if($moduleName == 'user' && $controllerName == 'admin-fields' && $actionName == 'type-delete') {
      $option_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('option_id', 0);

      // Element: profile_type
      $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
      if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
        $profileTypeField = $topStructure[0]->getChild();
        $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
        $options = $profileTypeField->getElementParams('user');

        unset($options['options']['order']);
        unset($options['options']['multiOptions']['']);
        if($options['type'] == 'ProfileType') {
          unset($options['options']['multiOptions']['5']);
          unset($options['options']['multiOptions']['9']);
        }

        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        if($option_id)
        unset($options['options']['multiOptions'][$option_id]);
        
        if(engine_count($options['options']['multiOptions']) > 1) {
          $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
            'label' => '',
            'description' => "Choose a new Profile Type for users to associate with. If you choose 'None' users can select their own profile type when editing their profiles. When you confirm the deletion of this profile type, all associated data will be permanently removed and cannot be recovered.",
            'required' => false,
            'allowEmpty' => true,
          )));
        }
      }
    }

    $this->addElement('Hash', 'token');

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Delete Profile Type',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

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
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $this->getDisplayGroup('buttons');
  }
}
