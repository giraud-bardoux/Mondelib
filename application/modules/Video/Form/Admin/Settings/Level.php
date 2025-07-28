<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
    public function init()
    {
        parent::init();

        // My stuff
        $this
            ->setTitle('Member Level Settings')
            ->setDescription('These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below.');

        // Element: view
        $this->addElement('Radio', 'view', array(
            'label' => 'Allow Viewing of Videos?',
            'description' => 'Do you want to let members view videos? If set to no, some other settings on this page may not apply.',
            'multiOptions' => array(
                2 => 'Yes, allow viewing of all videos, even private ones.',
                1 => 'Yes, allow viewing of videos.',
                0 => 'No, do not allow videos to be viewed.',
            ),
            'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if( !$this->isModerator() ) {
            unset($this->view->options[2]);
        }

        if( !$this->isPublic() ) {

            // Element: create
            $this->addElement('Radio', 'create', array(
                'label' => 'Allow Creation of Videos?',
                'description' => 'Do you want to let members create videos? If set to no, some other settings on this page may not apply. This is useful if you want members to be able to view videos, but only want certain levels to be able to create videos.',
                'multiOptions' => array(
                    1 => 'Yes, allow creation of videos.',
                    0 => 'No, do not allow video to be created.'
                ),
                'value' => 1,
            ));

            // Element: edit
            $this->addElement('Radio', 'edit', array(
                'label' => 'Allow Editing of Videos?',
                'description' => 'Do you want to let members edit videos? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to edit all videos.',
                    1 => 'Yes, allow members to edit their own videos.',
                    0 => 'No, do not allow members to edit their videos.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->edit->options[2]);
            }

            // Element: delete
            $this->addElement('Radio', 'delete', array(
                'label' => 'Allow Deletion of Videos?',
                'description' => 'Do you want to let members delete videos? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all videos.',
                    1 => 'Yes, allow members to delete their own videos.',
                    0 => 'No, do not allow members to delete their videos.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->delete->options[2]);
            }
            
						//Element: approve
						$this->addElement('Radio', 'approve', array(
							'description' => 'Do you want videos created by members of this level to be auto-approved?',
							'label' => 'Auto Approve Videos',
							'multiOptions' => array(
								1 => 'Yes, auto-approve videos.',
								0 => 'No, do not auto-approve videos.'
							),
							'value' => 1,
						));

            // Element: comment
            $this->addElement('Radio', 'comment', array(
                'label' => 'Allow Commenting on Videos?',
                'description' => 'Do you want to let members of this level comment on videos?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to comment on all videos, including private ones.',
                    1 => 'Yes, allow members to comment on videos.',
                    0 => 'No, do not allow members to comment on videos.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->comment->options[2]);
            }

            // Element: upload
            $this->addElement('Radio', 'upload', array(
                'label' => 'Allow Video Upload?',
                'description' => 'Do you want to let members to upload their own videos? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    1 => 'Yes, allow video uploads.',
                    0 => 'No, do not allow video uploads.',
                ),
                'value' => 1,
            ));

            // Element: auth_view
            $this->addElement('MultiCheckbox', 'auth_view', array(
                'label' => 'Video Privacy',
                'description' => 'Your members can choose from any of the options checked below when they decide who can see their video. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
                'multiOptions' => array(
                    'everyone'            => 'Everyone',
                    'registered'          => 'All Registered Members',
                    'owner_network'       => 'Friends or Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member'        => 'Friends Only',
                    'owner'               => 'Just Me',
                ),
                'value' => array('everyone', 'owner_network', 'owner_member_member', 'owner_member', 'owner'),
            ));

            // Element: auth_comment
            $this->addElement('MultiCheckbox', 'auth_comment', array(
                'label' => 'Video Comment Options',
                'description' => 'Your members can choose from any of the options checked below when they decide who can post comments on their video. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice. ',
                'multiOptions' => array(
                    'everyone'            => 'Everyone',
                    'registered'          => 'All Registered Members',
                    'owner_network'       => 'Friends or Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member'        => 'Friends Only',
                    'owner'               => 'Just Me',
                ),
                'value' => array('everyone', 'owner_network', 'owner_member_member', 'owner_member', 'owner'),
            ));
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
            // Element: allow_network
            $this->addElement('Radio', 'allow_network', array(
                'label' => 'Allow to Choose Network Privacy?',
                'description' => 'Do you want to let members of this level choose Network Privacy for their Videos? These options appear on your members\' "Add Entry" and "Edit Entry" pages.',
                'multiOptions' => array(
                    1 => 'Yes, allow to choose Network Privacy.',
                    0 => 'No, do not allow to choose Network Privacy. '
                ),
                'value' => 1,
            ));
        }

            // Element: max
            $this->addElement('Text', 'max', array(
                'label' => 'Maximum Allowed Videos',
                'description' => 'Enter the maximum number of allowed videos. The field must contain an integer, use zero for unlimited.',
                'validators' => array(
                    array('Int', true),
                    new Engine_Validate_AtLeast(0),
                ),
            ));
            $this->addElement('FloodControl', 'flood', array(
                'label' => 'Maximum Allowed Videos per Duration',
                'description' => 'Enter the maximum number of videos allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
                'required' => true,
                'allowEmpty' => false,
                'value' => array(0, 'minute'),
            ));
        }

    }
}
