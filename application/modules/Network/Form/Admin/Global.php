<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 * @author     John
 */

class Network_Form_Admin_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Global Settings')
        ->setDescription('These settings affect all members in your community.');

    $this->addElement('Radio', "network_enable", array(
      'label' => 'Enable Networks',
      'description' => 'Do you want to enable networks for your website?',
      'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
      ),
      'value' => $settings->getSetting('network.enable', 1),
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
