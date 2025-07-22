<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Form_Edit extends Engine_Form
{
    protected $_parent_type;

    protected $_parent_id;

    public function setParent_type($value)
    {
        $this->_parent_type = $value;
    }

    public function setParent_id($value)
    {
        $this->_parent_id = $value;
    }

    public function init()
    {
        $user = Engine_Api::_()->user()->getViewer();
        $userLevel = Engine_Api::_()->user()->getViewer()->level_id;
        
        $this->setTitle('Edit Event')
            ->setAttrib('id', 'event_create_form')
            ->setMethod("POST")
            ->setAttrib('class', 'global_form form_submit_ajax')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

        // Title
        $this->addElement('Text', 'title', array(
            'label' => 'Event Name',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(1, 64)),
            ),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
            ),
        ));

        $title = $this->getElement('title');

        //$allowedHtml = Engine_Api::_()->authorization()->getPermission($userLevel, 'event', 'auth_html');
        $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'upload-photo'), 'default', true);

        $editorOptions = array(
            'uploadUrl' => $uploadUrl,
            //'html' => (bool) $allowedHtml,
        );

        $this->addElement('TinyMce', 'description', array(
            'disableLoadDefaultDecorators' => true,
            'label' => 'Event Description',
            'required' => true,
            'allowEmpty' => false,
            'decorators' => array(
                'ViewHelper'
            ),
            'editorOptions' => $editorOptions,
            'filters' => array(
                new Engine_Filter_Censor(),
                //new Engine_Filter_Html(array('AllowedTags' => $allowedHtml))
            ),
        ));

        // Start time
        $start = new Engine_Form_Element_CalendarDateTime('starttime');
        $start->setLabel("Start Time");
        $start->setAllowEmpty(false);
        $this->addElement($start);

        // End time
        $end = new Engine_Form_Element_CalendarDateTime('endtime');
        $end->setLabel("End Time");
        $end->setAllowEmpty(false);
        $this->addElement($end);

        // Host
        if ($this->_parent_type == 'user')
        {
            $this->addElement('Text', 'host', array(
                'label' => 'Host',
                'filters' => array(
                    new Engine_Filter_Censor(),
                    'StripTags',
                ),
            ));
        }
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
          // Online
          $this->addElement('Select', 'is_online', array(
            'label' => 'Event Type',
            'description' => "What type of event you want?",
            'multiOptions' => array(
                '1' => 'Online Event',
                '0' => 'Offline Event',
            ),
            'onchange' => 'isOnline(this.value);',
          ));
          
          //Location
          $this->addElement('Text', 'location', array(
            'label' => 'Location',
            'filters' => array(
              new Engine_Filter_Censor(),
              new Engine_Filter_HtmlSpecialChars(),
            ),
          ));
          if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
            $this->addElement('Hidden', 'lat', array('order' => 3000, 'value' => ''));
            $this->addElement('Hidden', 'lng', array('order' => 3001, 'value' => ''));
            $this->addElement('Hidden', 'city', array('order' => 3002, 'value' => ''));
            $this->addElement('Hidden', 'state', array('order' => 3003, 'value' => ''));
            $this->addElement('Hidden', 'country', array('order' => 3004, 'value' => ''));
            $this->addElement('Hidden', 'zip', array('order' => 3005, 'value' => ''));
          }
        } else {
          $this->addElement('Hidden', 'is_online', array(
            'value' => 1,
          ));
        }
        
        $this->addElement('Text', 'website', array(
            'label' => 'Website',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
            ),
        ));

        // Photo
        $this->addElement('File', 'photo', array(
            'label' => 'Main Photo',
						'accept' => 'image/*',
        ));
        $this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg,webp');

        // prepare categories
        $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
        if (engine_count($categories) > 0) {
          $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'multiOptions' => $categories,
            'onchange' => "showSubCategory(this.value);",
          ));
          $this->addElement('Select', 'subcat_id', array(
            'label' => "2nd-level Category",
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array('0' => ''),
            'registerInArrayValidator' => false,
            'onchange' => "showSubSubCategory(this.value);"
          ));
          $this->addElement('Select', 'subsubcat_id', array(
            'label' => "3rd-level Category",
            'allowEmpty' => true,
            'registerInArrayValidator' => false,
            'required' => false,
            'multiOptions' => array('0' => '')
          ));
        }

        // Search
        $this->addElement('Checkbox', 'search', array(
            'label' => 'People can search for this event',
            'value' => 1,
        ));

        // Approval
        $this->addElement('Checkbox', 'approval', array(
            'label' => 'People must be invited to RSVP for this event',
        ));

        // Invite
        $this->addElement('Checkbox', 'auth_invite', array(
            'label' => 'Invited guests can invite other people as well',
            'value' => 1,
        ));

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('event', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            if(engine_count($networkOptions) > 0) {
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => 'Networks',
                'description' => 'Choose the Networks to which this Event will be displayed. If you want to show this Event to all users then do not select any network.',
                'multiOptions' => $networkOptions,
            ));
            }
        }

        // Privacy
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('event', $user, 'auth_view');
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('event', $user, 'auth_comment');
        $photoOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('event', $user, 'auth_photo');

        if( $this->_parent_type == 'user' ) {
            $availableLabels = array(
                'everyone'            => 'Everyone',
                'registered'          => 'All Registered Members',
                'owner_network'       => 'Friends or Networks',
                'owner_member_member' => 'Friends of Friends',
                'owner_member'        => 'Friends Only',
                'member'              => 'Event Guests Only',
                'owner'               => 'Just Me'
            );
            $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
            $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
            $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));

        } else if( $this->_parent_type == 'group' ) {

            $group = Engine_Api::_()->getItem('group', $this->_parent_id);
            if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
              $viewOptions = $commentOptions = $availableLabels = array(
                'parent_member' => 'Group Members',
                'member'        => 'Event Guests Only',
                'owner'         => 'Just Me',
              );
              $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));
            } else {
              $availableLabels = array(
                'everyone'      => 'Everyone',
                'registered'    => 'All Registered Members',
                'parent_member' => 'Group Members',
                'member'        => 'Event Guests Only',
                'owner'         => 'Just Me',
              );
              $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
              $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
              $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));
            }
        }

        // View
        if( !empty($viewOptions) && engine_count($viewOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($viewOptions) == 1) {
                $this->addElement('hidden', 'auth_view', array('order' => 101, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'Privacy',
                    'description' => 'Who may see this event?',
                    'multiOptions' => $viewOptions,
                    'value' => key($viewOptions),
                ));
                $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Comment
        if( !empty($commentOptions) && engine_count($commentOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($commentOptions) == 1) {
                $this->addElement('hidden', 'auth_comment', array('order' => 102, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post comments on this event?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Photo
        if( !empty($photoOptions) && engine_count($photoOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($photoOptions) == 1) {
                $this->addElement('hidden', 'auth_photo', array('order' => 103, 'value' => key($photoOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_photo', array(
                    'label' => 'Photo Uploads',
                    'description' => 'Who may upload photos to this event?',
                    'multiOptions' => $photoOptions,
                    'value' => key($photoOptions)
                ));
                $this->auth_photo->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Buttons
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
            'decorators' => array(
                'FormElements',
                'DivDivDivWrapper',
            ),
        ));
    }
}
