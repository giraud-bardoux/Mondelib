<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Package_Global extends Engine_Form
{
  public function init()
  {

    $description = $this->getTranslator()->translate(
          'These settings affect all members in your community. <br>');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      $moreinfo = $this->getTranslator()->translate(
          'More Info: <a href="%1$s" target="_blank"> KB Article</a>');
    } else {
      $moreinfo = $this->getTranslator()->translate(
          '');
    }

    $description = vsprintf($description.$moreinfo, array(
      'https://community.socialengine.com/blogs/597/75/billing-settings',
    ));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->setTitle('Global Settings')
      ->setDescription($description);

    // Element: benefit
    $this->addElement('Radio', 'benefit', array(
      'label' => 'Initial Subscription Status',
      'description' => 'Do you want to enable subscriptions immediately after '
          . 'payment, before the payment passes the gateways\' fraud checks? '
          . 'This may take anywhere from 20 minutes to 4 days, depending on '
          . 'the circumstances and the gateway.',
      'multiOptions' => array(
        'all' => 'Enable subscriptions immediately.',
        'some' => 'Enable if member has an existing successful transaction, wait if this is their first.',
        'none' => 'Wait until the gateway signals that the payment has completed successfully.',
      ),
      'value' => $settings->getSetting('payment.benefit'),
    ));

    $this->addElement('Dummy', 'paymentsettings', array(
      'content' => '<div class="form-wrapper-heading"><span>Membership Subscription Membership Table Settings</span></div>',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));
    $this->addElement('Text', 'table_title', array(
      'label' => 'Membership Table Title',
      'description' => 'Enter the title of the membership subscription table on your website.',
      'value' => $settings->getSetting('payment.table.title',"Subscription Plans"),
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
    ));
    
    $this->addElement('Textarea', 'table_description', array(
      'label' => 'Membership Table Description',
      'description' => 'Enter the description of the membership subscription table on your website.',
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
      'value'=> $settings->getSetting('payment.table.description',"Please choose a subscription plan from the options below.")
    ));
    
    $this->addElement('Select', 'footer_enable', array(
      'label' => 'Enable Footer Note',
      'description' => 'Do you want to show a note in the Footer of the membership pricing table?',
      'multiOptions' => array('1'=>'Yes','0'=>'No'),
      'onchange' => 'showFooterNote(this.value)',
      'value'=> $settings->getSetting('payment.footer.enable',1),
    ));

    $this->addElement('Textarea', 'footer_note', array(
      'label' => 'Footer Note Text',
      'description' => 'Enter the text for the note which will be displayed in the footer of the membership pricing table.',
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
      'value'=> $settings->getSetting('payment.footer.note',"")
    ));

    //Template Settings
    $this->addElement('Text', 'body_container_clr', array(
      'label' => 'Membership Table Container Background Color',
      'class' => 'SEcolor',
      'value'=> $settings->getSetting('payment.body.container.clr',"")
    ));
    
    $this->addElement('Text', 'header_bgclr', array(
      'label' => 'Membership Table Header Background Color',
      'class' => 'SEcolor',
      'value'=> $settings->getSetting('payment.header.bgclr',"")
    ));
    
    $this->addElement('Text', 'header_txtclr', array(
      'label' => 'Membership Table Header Font Color',
      'class' => 'SEcolor',
      'value'=> $settings->getSetting('payment.header.txtclr',"")
    ));
    
    $this->addElement('Select', 'overlap', array(
      'label' => 'Overlap Container Over Header',
      'description'=>'Do you want to overlap the membership table container over the table header?',
      'multiOptions'=>array('1' => 'Yes','0' => 'No'),
      'value'=> $settings->getSetting('payment.overlap', 1),
    ));

    
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
    ));
  }
}
