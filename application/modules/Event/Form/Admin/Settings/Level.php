<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9802 2012-10-20 16:56:13Z pamela $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
    public function init()
    {
        parent::init();

        // My stuff
        $this
            ->setTitle('Member Level Settings')
            ->setDescription('EVENT_FORM_ADMIN_LEVEL_DESCRIPTION');

        // Element: view
        $this->addElement('Radio', 'view', array(
            'label' => 'Allow Viewing of Events?',
            'description' => 'EVENT_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
            'multiOptions' => array(
                2 => 'Yes, allow members to view all events, even private ones.',
                1 => 'Yes, allow viewing and subscription of photo events.',
                0 => 'No, do not allow photo events to be viewed.',
            ),
            'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if( !$this->isModerator() ) {
            unset($this->view->options[2]);
        }

        if( !$this->isPublic() ) {

            // Element: create
            $this->addElement('Radio', 'create', array(
                'label' => 'Allow Creation of Events?',
                'description' => 'EVENT_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
                'multiOptions' => array(
                    1 => 'Yes, allow creation of events.',
                    0 => 'No, do not allow events to be created.',
                ),
                'value' => 1,
            ));

            // Element: edit
            $this->addElement('Radio', 'edit', array(
                'label' => 'Allow Editing of Events?',
                'description' => 'Do you want to let members edit and delete events?',
                'multiOptions' => array(
                    2 => "Yes, allow members to edit everyone's events.",
                    1 => "Yes, allow  members to edit their own events.",
                    0 => "No, do not allow events to be edited.",
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->edit->options[2]);
            }

            // Element: delete
            $this->addElement('Radio', 'delete', array(
                'label' => 'Allow Deletion of Events?',
                'description' => 'Do you want to let members delete events? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all events.',
                    1 => 'Yes, allow members to delete their own events.',
                    0 => 'No, do not allow members to delete their events.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->delete->options[2]);
            }
            
						//Element: approve
						$this->addElement('Radio', 'approve', array(
							'description' => 'Do you want events created by members of this level to be auto-approved?',
							'label' => 'Auto Approve Events',
							'multiOptions' => array(
								1 => 'Yes, auto-approve events.',
								0 => 'No, do not auto-approve events.'
							),
							'value' => 1,
						));

            // Element: comment
            $this->addElement('Radio', 'comment', array(
                'label' => 'Allow Commenting on Events?',
                'description' => 'Do you want to let members of this level comment on events?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to comment on all events, including private ones.',
                    1 => 'Yes, allow members to comment on events.',
                    0 => 'No, do not allow members to comment on events.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->comment->options[2]);
            }

            // Element: auth_view
            $this->addElement('MultiCheckbox', 'auth_view', array(
                'label' => 'Event Privacy',
                'description' => 'EVENT_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
                'multiOptions' => array(
                    'everyone' => 'Everyone',
                    'registered' => 'Registered Members',
                    'owner_network' => 'Friends or Networks (user events only)',
                    'owner_member_member' => 'Friends of Friends (user events only)',
                    'owner_member' => 'Friends Only (user events only)',
                    'parent_member' => 'Group Members (group events only)',
                    'member' => "Event Guests Only",
                    //'owner' => 'Just Me'
                )
            ));

            // Element: auth_comment
            $this->addElement('MultiCheckbox', 'auth_comment', array(
                'label' => 'Event Posting Options',
                'description' => 'EVENT_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
                'multiOptions' => array(
                    'registered' => 'Registered Members',
                    'owner_network' => 'Friends or Networks (user events only)',
                    'owner_member_member' => 'Friends of Friends (user events only)',
                    'owner_member' => 'Friends Only (user events only)',
                    'parent_member' => 'Group Members (group events only)',
                    'member' => "Event Guests Only",
                    'owner' => 'Just Me'
                )
            ));

            // Element: auth_photo
            $this->addElement('MultiCheckbox', 'auth_photo', array(
                'label' => 'Photo Upload Options',
                'description' => 'EVENT_FORM_ADMIN_LEVEL_AUTHUPHOTO_DESCRIPTION',
                'multiOptions' => array(
                    'member' => 'All Guests',
                    'owner' => 'Just Me'
                )
            ));
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
            // Element: allow_network
            $this->addElement('Radio', 'allow_network', array(
                'label' => 'Allow to Choose Network Privacy?',
                'description' => 'Do you want to let members of this level choose Network Privacy for their Events? These options appear on your members\' "Add Entry" and "Edit Entry" pages.',
                'multiOptions' => array(
                    1 => 'Yes, allow to choose Network Privacy.',
                    0 => 'No, do not allow to choose Network Privacy. '
                ),
                'value' => 1,
            ));
        }

            $this->addElement('Radio', 'style', array(
                'label' => 'Allow Event Style',
                'required' => true,
                'multiOptions' => array(
                    1 => 'Yes, allow custom event styles.',
                    0 => 'No, do not allow custom event styles.'
                ),
                'value' => 1
            ));
            
            // Element: auth_html
//             $this->addElement('Text', 'auth_html', array(
//                 'label' => 'HTML in Event?',
//                 'description' => 'If you want to allow specific HTML tags, you can enter them below (separated by commas). Example: b, img, a, embed, font',
//                 'value' => 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr'
//             ));

            // Element: create
            $this->addElement('Radio', 'coverphotoupload', [
                'label' => 'Allow to Upload Cover Photos ?',
                'description' => 'Do you want to let members upload Cover Photos for their events?',
                'multiOptions' => array(
                    1 => 'Yes, allow user to upload cover photos',
                    0 => 'No, do not allow users to upload cover photos.'
                ),
                'value' => 1,
            ]);
            
            //New File System Code
            $covers = array('' => '');
            $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
            foreach( $files as $file ) {
              $covers[$file->storage_path] = $file->name;
            }
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $fileLink = $view->baseUrl() . '/admin/files/';
            
            $this->addElement('Select', 'coverphoto', array(
              'label' => 'Default Event Cover Photo',
              'description' => 'Choose default event cover photo. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change event default photo.]',
              'multiOptions' => $covers,
            ));
            $this->coverphoto->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

//             $this->addElement('dummy', 'coverphoto_dummy', [
//                 'label' => 'Default Event Cover Photo',
//             ]);

//             $this->coverphoto_dummy->addDecorator('Description', [
//                 'placement' => 'PREPEND',
//                 'class' => 'description',
//                 'escape' => false,
//                 'order' => 998
//             ]);

            /* Forum Settings*/
            // Element: topic_create
            $this->addElement('Radio', 'topic_create', array(
                'label' => 'Allow Creation of Topics?',
                'description' => 'Do you want to let users create topics? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow creation of topics in forums, even private ones.',
                    1 => 'Yes, allow creation of topics.',
                    0 => 'No, do not allow topics to be created.'
                ),
                'order' => 301,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_create->options[2]);
            }

            // Element: topic_edit
            $this->addElement('Radio', 'topic_edit', array(
                'label' => 'Allow Editing of Topics?',
                'description' => 'Do you want to let users edit topics?',
                'multiOptions' => array(
                    2 => 'Yes, allow editing of topics in forums, including other members\' topics.',
                    1 => 'Yes, allow editing of topics.',
                    0 => 'No, do not allow topics to be edited.'
                ),
                'order' => 302,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_edit->options[2]);
            }

            // Element: topic_edit
            $this->addElement('Radio', 'topic_delete', array(
                'label' => 'Allow Deletion of Topics?',
                'description' => 'Do you want to let users delete topics?',
                'multiOptions' => array(
                    2 => 'Yes, allow deletion of topics in forums, including other members\' topics.',
                    1 => 'Yes, allow deletion of topics.',
                    0 => 'No, do not allow topics to be deleted.'
                ),
                'order' => 304,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_delete->options[2]);
            }

            // Element: post_create
            $this->addElement('Radio', 'post_create', array(
                'label' => 'Allow Posting?',
                'description' => 'Do you want to allow users to post to the forums? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow posting to forums, even private ones.',
                    1 => 'Yes, allow posting to forums.',
                    0 => 'No, do not allow forum posts.'
                ),
                'order' => 305,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_create->options[2]);
            }

            // Element: post_edit
            $this->addElement('Radio', 'post_edit', array(
                'label' => 'Allow Editing of Posts?',
                'description' => 'Do you want to allow users to edit posts in forums?',
                'multiOptions' => array(
                    2 => 'Yes, allow editing of posts, including other members\' posts.',
                    1 => 'Yes, allow editing of posts.',
                    0 => 'No, do not allow forum posts to be edited.'
                ),
                'order' => 306,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_edit->options[2]);
            }

            // Element: post_edit
            $this->addElement('Radio', 'post_delete', array(
                'label' => 'Allow Deletion of Posts?',
                'description' => 'Do you want to allow users to delete posts in forums?',
                'multiOptions' => array(
                    2 => 'Yes, allow deletion of posts, including other members\' posts.',
                    1 => 'Yes, allow deletion of posts.',
                    0 => 'No, do not allow forum posts to be deleted.'
                ),
                'order' => 307,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_delete->options[2]);
            }
            
            $this->addElement('FloodControl', 'flood', array(
                'label' => 'Maximum Allowed Events per Duration',
                'description' => 'Enter the maximum number of events allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
                'required' => true,
                'allowEmpty' => false,
                'value' => array(0, 'minute'),
            ));
        }
    }
}
