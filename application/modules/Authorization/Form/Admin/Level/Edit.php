<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 10086 2013-09-16 19:27:24Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Form_Admin_Level_Edit extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();
    
    //New File System Code
    $covers = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $covers[$file->storage_path] = $file->name;
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    
    // My stuff
    $this
        ->setTitle('Member Level Settings')
        ->setDescription("AUTHORIZATION_FORM_ADMIN_LEVEL_EDIT_DESCRIPTION");
        
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'allowEmpty' => false,
      'required' => true,
    ));

    $this->addElement('Textarea', 'description', array(
      'label' => 'Description',
      'allowEmpty' => true,
      'required' => false,
    ));

    if( !$this->isPublic() ) {

      // Element: edit
      if( $this->isModerator() ) {
        $this->addElement('Select', 'edit', array(
          'label' => 'Allow Profile Moderation',
          'required' => true,
          'multiOptions' => array(
            2 => 'Yes, allow members in this level to edit other profiles and settings.',
            1 => 'No, do not allow moderation.'
          ),
          'value' => 0,
        ));
      }

      // Element: delete
      $this->addElement('Select', 'delete', array(
        'label' => 'Allow Account Deletion?',
        'multiOptions' => array(
          2 => 'Yes, allow members in this level to delete other users.',
          1 => 'Yes, allow members to delete their account.',
          0 => 'No, do not allow account deletion.',
        ),
        'value' => 1,
      ));
      if( !$this->isModerator() ) {
        unset($this->getElement('delete')->options[2]);
      }
      $this->delete->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: activity
      if( $this->isModerator() ) {
        $this->addElement('Select', 'activity', array(
          'label' => 'Allow Activity Feed Moderation',
          'required' => true,
          'multiOptions' => array(
            1 => 'Yes, allow members in this level to delete any feed item.',
            0 => 'No, do not allow moderation.'
          ),
          'value' => 0,
        ));
      }

      // Element: block
      $this->addElement('Select', 'block', array(
        'label' => 'Allow Blocking Other Members',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_BLOCK_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->block->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: block
      $this->addElement('Select', 'canblock', array(
        'label' => 'Can this member level be blocked?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_MEMBERLEVEL_BLOCK_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->block->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Profile Viewing Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHVIEW_DESCRIPTION',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Profile Commenting Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        )
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'PREPEND');      

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Profile Commenting Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        )
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: status
      $this->addElement('Select', 'status', array(
        'label' => 'Allow status messages?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_STATUS_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));

      // $this->addElement('Text', 'activity_edit_time', array(
      //   'label' => 'Maximum Allowed time for editing status posts?',
      //   'description' => 'Enter the maximum allowed time (in minutes) for which members will be able to edit their status posts via activity feed.'
      //   . ' The field must contain an integer between 1 and 1000000, or 0 for unlimited.',
      //   'validators' => array(
      //     array('Int', true),
      //     new Engine_Validate_AtLeast(0),
      //   ),
      // ));
      
      // Element: username
      $this->addElement('Select', 'changeemail', array(
        'label' => 'Allow users to change email?',
        'description' => "Do you want to allow members of this level to change their emails? If you choose 'Yes', then members of this level can change their emails.",
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'onchange' => "showHideSettings('changeemail', this.value);",
      ));
      $this->changeemail->getDecorator('Description')->setOption('placement', 'PREPEND');
      
      $this->addElement('Select', 'emailverify', array(
        'label' => 'Verify Email Address?',
        'description' => 'Force members to verify their email address before they change their emails? If set to YES, members will be sent an email with a verification link which they must click to change the email.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->emailverify->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: username
      $this->addElement('Select', 'username', array(
        'label' => 'Allow username changes?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_USERNAME_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->username->getDecorator('Description')->setOption('placement', 'PREPEND');
      
      // Element: edit profile type
      $this->addElement('Select', 'editprofiletype', array(
        'label' => 'Allow Profile Type Change',
        'description' => 'If set to "yes", members will be able to change their Profile Types when members edit their profiles. Once they choose to change their profile type, the data of their previous profile type will be lost. (Note: This setting will only work if there are more than 1 profile type on your site excluding Admin & Super Admin profile types.)',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'onchange' => 'showProfileMemberLevel(this.value);',
      ));

      $profileTypeLink = array($view->baseUrl() . '/admin/authorization/level/manage-profile-type-mapping');
      $description = vsprintf('If set to "yes", then Member Level (mapped with the changed <a href="%s">Profile Type</a>) will also be updated when members change their Profile Types.', $profileTypeLink);
      $this->addElement('Select', 'editprotylevel', array(
        'label' => 'Change Member Level on Profile Type Change',
        'description' => $description,
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
      ));
      $this->editprotylevel->getDecorator('Description')->setOption('escape', false);

      // Element: quota
      $this->addElement('Select', 'quota', array(
        'label' => 'Storage Quota',
        'required' => true,
        'multiOptions' => Engine_Api::_()->getItemTable('storage_file')->getStorageLimits(),
        'value' => 0, // unlimited
        'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_QUOTA_DESCRIPTION'
      ));

      // Element: messages_auth
      $this->addElement('Select', 'messages_auth', array(
        'label' => 'Allow messaging?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_MESSAGESAUTH_DESCRIPTION',
        'multiOptions' => array(
          'everyone' => 'Everyone',
          'friends' => 'Friends Only',
          'none' => 'Disable messaging',
        ),
        'onchange' => 'hideShowMessageSettings(this.value)',
      ));
      $this->messages_auth->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: messages_editor
      $this->addElement('Select', 'messages_editor', array(
        'label' => 'Use editor for messaging?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_MESSAGEEDITOR_DESCRIPTION',
        'multiOptions' => array(
          'editor' => 'Editor',
          'plaintext' => 'Plain Text',
        )
      ));
      
      //Cover Photo Settings
      //New File System Code
      $covers = array('' => '');
      $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
      foreach( $files as $file ) {
        $covers[$file->storage_path] = $file->name;
      }
      
      $fileLink = $view->baseUrl() . '/admin/files/';
      
      $this->addElement('Select', 'coverphoto', array(
        'label' => 'Default User Cover Photo',
        'description' => 'Choose default user cover photo. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change user default photo.]',
        'multiOptions' => $covers,
      ));
      $this->coverphoto->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      
      $this->addElement('Select', 'is_fullwidth', array(
        'label' => "Show Cover Photo in Full Width",
        'description' => 'Do you want to show Cover Photo in full width?',
        'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      $this->addElement('Select', 'tab', array(
        'label' => 'Tab Placement',
        'description' => "Choose from below where you want to show Tab (Note: This setting does not work in Template - 2)",
        'multiOptions' => array(
          'inside' => 'Inside Cover Photo Widget',
          'outside' => 'Outside Cover Photo Widget',
        ),
        'value' => 1,
      ));
      //End Cover Photo Settings

        $this->addElement('FloodControl', 'activity_flood', array(
            'label' => 'Maximum Allowed Status Messages per Duration',
            'description' => 'Enter the maximum number of status messages allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

        $this->addElement('FloodControl', 'messages_flood', array(
            'label' => 'Maximum Allowed Messages per Duration',
            'description' => 'Enter the maximum number of messages allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

        $this->addElement('MultiCheckbox', 'mention', array(
            'label' => 'Users @ Mentions Options',
            'description' => 'Your members can choose from any of the options checked below when they decide that who can "mention" them in posts. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
            'multiOptions' => array(
              'registered'  => 'All Registered Members',
              'owner_network' => 'Network',
              'network'    => 'Friends or Networks',
              'member'      => 'My Friends',
              'owner'       => 'Only Me',
            ),
        ));
        
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poke')) {
          $this->addElement('MultiCheckbox', 'pokeAction', array(
              'label' => 'Users Poke & Action Options',
              'description' => 'Your members can choose from any of the options checked below when they decide "Who can Poke & Send Action" to them. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
              'multiOptions' => array(
                'registered'  => 'All Registered Members',
                'owner_network' => 'Network',
                'network'    => 'Friends or Networks',
                'member'      => 'My Friends',
                'owner'       => 'Only Me',
              ),
          ));
        }

      if($this->isAdmin() ) {
        $this->addElement('Select', 'abuseNotifi', array(
          'label' => 'Show abuse notification?',
          'description' => 'If set to yes, it will show an in-site notification if something is reported on the site. This notification will send you to the admin panel abuse section to take the actions.',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value'=>1
        ));
        $this->abuseNotifi->getDecorator('Description')->setOption('placement', 'PREPEND');

        $this->addElement('Select', 'abuseEmail', array(
          'label' => 'Send emailed abuse notification?',
          'description' => 'If set to yes, this emails a notification for the abuse notification if something is reported on the site.',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value'=>1,
        ));
        $this->abuseEmail->getDecorator('Description')->setOption('placement', 'PREPEND');
      }

      $this->addElement('MultiCheckbox', 'birthday_options', array(
        'label' => 'Birthday Privacy Setting Options',
        'description' => "Your members can choose from any of the options checked below that in which way they want to show their birthday. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.",
          'multiOptions' => array(
            'monthday' => 'Month/Day',
            'monthdayyear' => 'Month/Day/Year',
          ),
        'value'=>1,
      ));
      $this->birthday_options->getDecorator('Description')->setOption('placement', 'PREPEND');

    }
  }
}
