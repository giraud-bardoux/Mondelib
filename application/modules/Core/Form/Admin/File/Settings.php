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
      'onchange' => "hideShow(this.value);",
    ));
	$this->core_convertwebp->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Text', 'core_compression_quality', array(
        'label' => 'Image Compression Quality',
        'description' => 'This setting allows you to define the image compression quality (1–99, where 99 means lowest quality and highest compression) for newly uploaded images. Changes to this setting will only affect images uploaded after the change. Previously uploaded images will remain unaffected.',
        'allowEmpty' => false,
        'required' => true,
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core_compression_quality', 75),
        'validators' => array(
            array('Int', true),
            array('Between', true, array(1, 99)), // Ensures the value is between 1 and 99
        ),
    ));


    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
