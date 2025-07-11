<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Settings.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Form_Admin_File_Settings extends Engine_Form {

  public function init() {

    $this->setTitle('Global Settings')
          ->setDescription('These settings affect all members in your community.');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Select', "core_convertwebp", array(
      'label' => 'Convert Photos to .webp Extension',
      'description' => 'Do you want to convert all photos uploaded on your site into .webp extension? If you choose Yes, then all the photos upload from user panel and admin panel will be converted to .webp extension. Note: If you choose Yes, then make sure "imagewebp" extension is enabled on your server. <br /> Note: This setting will only work with newly uploaded images. Existing uploaded images will not be converted into .webp extension.',
      'allowEmpty' => true,
      'required' => false,
      'multiOptions'=> array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.convertwebp', 1),
    ));
	$this->core_convertwebp->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
