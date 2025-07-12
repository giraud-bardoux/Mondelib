<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Templates.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Mail_Templates extends Engine_Form
{
  public function init()
  {
		
		$template = Zend_Controller_Front::getInstance()->getRequest()->getParam('template', 1);
		
    // Set form attributes
    $description = $this->getTranslator()->translate(
      'Various notification emails are sent to your members as they interact with the community.'
      . ' Use this form to customize the content of these emails. Any changes you make here will'
      . ' only be saved after you click the "Save Changes" button at the bottom of the form. <br>');
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
    $moreinfo = $this->getTranslator()->translate(
    'More Info: <a href="%1$s" target="_blank"> KB Article</a>');
    } else {
    $moreinfo = $this->getTranslator()->translate(
    '');
    }
    $description = vsprintf($description.$moreinfo, array(
      'https://community.socialengine.com/blogs/597/34/mail-templates',
    ));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this
      ->setTitle('Mail Templates')
      ->setDescription($description)
      ;

    // Element: language
    $this->addElement('Select', 'language', array(
      'label' => 'Language Pack',
      'description' => 'Your community has more than one language pack installed. Please select the language pack you want to edit right now.',
      'onchange' => 'javascript:setEmailLanguage(this.value);',
    ));

    // Languages
    $localeMultiOptions = Engine_Api::_()->getApi('languages', 'core')->getLanguages();

    $this->language->setMultiOptions($localeMultiOptions);

    $member_levels = array();
    $public_level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $row ) {
      if($public_level->level_id != $row->level_id) {
        $member_count = $row->getMembershipCount();
        if( null !== ($translate = $this->getTranslator()) ) {
          $title = $translate->translate($row->title);
        } else {
          $title = $row->title;
        }
        $member_levels[$row->level_id] = $title . ' (' . $member_count . ')';
      }
    }
    $this->addElement('Multiselect', 'member_level', array(
      'label' => 'Member Levels',
      'description' => 'Hold down the CTRL key to select or de-select specific Member Levels.',
      'multiOptions' => $member_levels,
      'value'=>array_keys($member_levels),
    ));
    $this->member_level->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    // Element: template_id
    $this->addElement('Select', 'template', array(
      'label' => 'Choose Message',
      'onchange' => 'javascript:fetchEmailTemplate(this.value);',
      'ignore' => true
    ));
    $this->template->getDecorator("Description")->setOption("placement", "append");

    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    $select = Engine_Api::_()->getDbtable('MailTemplates', 'core')
        ->select()
        ->where('module IN(?)', $enabledModuleNames);
    foreach( Engine_Api::_()->getDbtable('MailTemplates', 'core')->fetchAll($select) as $mailTemplate ) {
      $title = $translate->_(strtoupper("_email_" . $mailTemplate->type . "_title"));
      $this->template->addMultiOption($mailTemplate->mailtemplate_id, $title);
    }

    // Element: subject
    $this->addElement('Text', 'subject', array(
      'label' => 'Subject',
      'style' => 'min-width:400px;',
    ));

    // Element: body
    $editorOptions = array(
      'html' => (bool) true,
      'mode' => "exact",
      'forced_root_block' => false,
      'force_p_newlines' => false,
      'elements' => 'bodyhtml',
    );

    $this->addElement('TinyMce', 'bodyhtml', array(
      'label' => 'Message Body',
      'editorOptions' => $editorOptions,
    ));
    
    if(!in_array($template, array(1,2,3,4))) {
			$this->addElement('Select', 'default', array(
				'label' => 'Enable',
				'description' => "Do you want to enable this email type? If you choose No then mail will not sent to member of your website.",
				'multiOptions' => array(
					1 => "Yes",
					0 => "No",
				),
			));
    }
    
    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
