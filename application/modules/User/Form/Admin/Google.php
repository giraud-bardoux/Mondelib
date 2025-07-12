<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Google.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Google extends Engine_Form {

  public function init() {
  
    $description = $this->getTranslator()->translate('Here, you can integrate SocialEngine to Google for allowing users to login into your website using their Google accounts. To do so, create an Application through the ');
    
    $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">Google Cloud Console</a> page. Please use the following Callback URL: %2$s <br />');
    
    $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%3$s" target="_blank">KB Article</a>');
    
    $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://console.cloud.google.com', 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'google'), 'default', true), 'https://socialnetworking.solutions/guidelines-social-login-google-api-key/'));
    
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->setTitle('Google Integration')
            ->setDescription($description);

    $this->addElement('Text', 'clientid', array(
      'label' => 'Google Client ID',
      'description' => '',
      'filters' => array(
        'StringTrim',
      ),
    ));

    $this->addElement('Text', 'clientsecret', array(
      'label' => 'Google Client Secret',
      'filters' => array(
        'StringTrim',
      ),
    ));

    $this->addElement('Radio', 'enable', array(
      'label' => 'Enable',
      'description' => '',
      'multiOptions' => array(
        'login' => 'Yes',
        'none'  => 'No',
      ),
      'value' => 'none'
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

  }
}
