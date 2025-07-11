<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Editors.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Settings_Editor extends Authorization_Form_Admin_Level_Abstract {

  public function init() {
  
    $description = $this->getTranslator()->translate('Below, configure the options to be displayed in the TinyMCE editor on your website. Refer to KB Article to know the placement of these options in TinyMCE Editor. <br>');
    $moreinfo = $this->getTranslator()->translate('More Info: <a href="%1$s" target="_blank"> KB Article</a>');
    $description = vsprintf($description.$moreinfo, array('https://community.socialengine.com/blogs/597/140/tinymce-editor-settings'));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->setTitle('TinyMCE Editor Settings');
    $this->setDescription($description);
    
    // Element: level_id
    $multiOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if( $level->type == 'public') {
        continue;
      }
      $multiOptions[$level->getIdentity()] = $level->getTitle();
    }
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'description' => 'Choose the Member Level to which you want to assign the below settings.',
			'multiOptions' => $multiOptions,
			'onchange' => 'fetchLevelSettings(this.value);',
    ));
    
    if (!$this->isPublic()) {
    
      $this->addElement('Radio', 'core_menubar_editor', array(
        'label' => 'Enable Menu Bar',
        'description' => 'Do you want to enable menu bar (menu bar is used to define the presence and order of menus, such as File, Edit, and View) in the TinyMCE editor on your site?',
        'multiOptions' => array(
          '1' => "Yes",
          '0' => "No",
        ),
      ));
      
      $this->addElement('Radio', 'core_statusbar_editor', array(
        'label' => 'Enable Status Bar',
        'description' => 'Do you want to enable status bar (status bar is the gray bar aligned to the bottom of the editor\'s editable area) in the TinyMCE editor on your site?',
        'multiOptions' => array(
          '1' => "Yes",
          '0' => "No",
        ),
      ));

      $this->addElement('MultiCheckbox', 'core_editors_allow', array(
        'label' => 'Choose Editor Options / Menu Items',
        'description' => "Choose from below the options / menu items that you want to enable in the TinyMCE editor on your site. If you choose 'No' for 'Enable Menu Bar' setting above, then options under menu bar options will not display in the editor.",
        'multiOptions' => array(
          'table' => "Table",
          'fullscreen' => "Full Screen",
          'media' => "Media",
          'code' => "Source Code",
          'image' => "Image",
          'link' => "Link",
          'lists' => "Lists",
          'advlist' => "Advanced Lists",
          'searchreplace' => "Search & Replace",
          'emoticons' => "Emoticons",
          'autolink' => "Auto Link",
          'autosave' => "Auto Save",
          'table' => "Table",
          'preview' => "Preview",
          'directionality' => "Directionality",
          'visualblocks' => "Visual Blocks",
          'visualchars' => 'Visual Characters',
          'codesample' => 'Code Sample',
          'wordcount' => 'Word Count',
          'accordion' => 'Accordion',
          'charmap' => 'Character Map',
          'pagebreak' => 'Page Break',
          'nonbreaking' => 'Nonbreaking Space',
          'anchor' => 'Anchor',
          'insertdatetime' => 'Date Formats',
        ),
      ));
      
      $this->addElement('Text', 'core_autosave_editor', array(
        'label' => 'TinyMCE Content Autosave Duration',
        'description' => 'Enter the minutes for which you want to automatically save TinyMCE content in local browser of the users on your site. (Enter a number between 1 and 999).',
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('Int', true),
          new Engine_Validate_AtLeast(1),
        ),
      ));

      $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
      ));
    }
  }
}
