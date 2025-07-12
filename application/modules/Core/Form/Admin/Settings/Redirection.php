<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Redirection.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Settings_Redirection extends Engine_Form {

  public function init() {
  
    $description = $this->getTranslator()->translate('These settings affect your entire community and all your members. Using these settings, you can choose to change the default redirection of users after selected actions. <br>');

    $moreinfo = $this->getTranslator()->translate('More Info: <a href="%1$s" target="_blank"> KB Article</a>');

    $description = vsprintf($description.$moreinfo, array('https://community.socialengine.com/blogs/597/44/redirection-setting'));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);


    $this->setTitle('Redirection Settings');
    $this->setDescription($description);

    $this->addElement('Radio', 'core_after_login', array(
      'label' => 'User Redirection After Login',
      'description' => 'Select the page where you want the users to be redirected after they log in to your site.',
      'multiOptions' => array(
        '4' => "Member Home Page",
        '3' => "Member Profile Page",
        '2' => "Edit Profile Page",
        '1' => "Other Page",
      ),
      'onchange' => 'hideShowLogin(this.value)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4),
    ));
    
    $this->addElement('Text', 'core_loginurl', array(
      'label' => 'Other Page URL',
      'description' => 'Enter URL of the page. (Note: Please make sure you are entering the URL from this website only.)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', ''),
    ));
    
    $this->addElement('Radio', 'core_after_signup', array(
      'label' => 'User Redirection After Signup',
      'description' => 'Select the page where you want the users to be redirected after they signup on your site.',
      'multiOptions' => array(
        '4' => "Member Home Page",
        '3' => "Member Profile Page",
        '2' => "Edit Profile Page",
        '1' => "Other Page",
      ),
      'onchange' => 'hideShowSignup(this.value)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.signup', 4),
    ));
    
    $this->addElement('Text', 'core_signupurl', array(
      'label' => 'Other Page URL',
      'description' => 'Enter URL of the page. (Note: Please make sure you are entering the URL from this website only.)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.signupurl', ''),
    ));
    
    $this->addElement('Radio', 'core_after_logout', array(
      'label' => 'User Redirection After Logout',
      'description' => 'Select the page where you want the users to be redirected after they logout from your site.',
      'multiOptions' => array(
        '3' => "Landing Page",
        '2' => "Sign-in Page",
        '1' => "Other Page",
      ),
      'onchange' => 'hideShowLogout(this.value)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.logout', 3),
    ));
    
    $this->addElement('Text', 'core_logouturl', array(
      'label' => 'Other Page URL',
      'description' => 'Enter URL of the page. (Note: Please make sure you are entering the URL from this website only.)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.logouturl', ''),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
