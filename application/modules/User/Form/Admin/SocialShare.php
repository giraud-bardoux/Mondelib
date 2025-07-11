<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SocialShare.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_SocialShare extends Engine_Form {

  public function init() {
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setTitle('Social Share Settings')
        ->setDescription('These settings affect all members in your community.')
        ->setAttrib('enctype', 'multipart/form-data')
        ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
        ->setMethod("POST");

    $this->addElement('Radio', 'core_socialshare_enable', array(
      'label' => 'Enable Outside Sharing',
      'description' => 'Do you want to enable your users to share various content, activity feeds from your website to other social networking sites? If Yes, then users will see the options to share content, activity feeds to other social networking sites.',
      'multiOptions' => array(
        '1' => "Yes",
        '0' => "No",
      ),
      'onchange' => "showHide(this.value);",
      'value' => $settings->getSetting('core.socialshare.enable', 1),
    ));

    $this->addElement('MultiCheckbox', 'core_socialashare_allow', array(
      'label' => 'Choose Social Networking Services',
      'description' => "Choose from below the social networking services that you want to enable for outside sharing on your website.",
      'multiOptions' => array(
        'facebook' => "Facebook",
        'twitter' => "X",
        'pinterest' => "Pinterest",
        'linkedin' => "Linkedin",
        'whatsapp' => "WhatsApp",
        'gmail' => "Gmail",
        'tumblr' => "Tumblr",
        'skype' => "Skype",
        'flipboard' => "Flipboard",
        'vk' => "VK",
      ),
      'value' => json_decode($settings->getSetting('core.socialashare.allow', '["facebook","twitter","pinterest","linkedin","gmail","tumblr","flipboard","skype","vk","whatsapp"]')),
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

  }
}
