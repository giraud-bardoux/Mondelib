<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 10028 2013-03-28 22:11:22Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Form_Create extends Engine_Form
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
        $auth = Engine_Api::_()->authorization()->context;
        $user = Engine_Api::_()->user()->getViewer();


        $this->setTitle('Create Poll')
            ->setDescription('Create your poll below, then click "Create Poll" to start your poll.')
            ->setAttrib('class', 'global_form form_submit_ajax');

        $this->addElement('text', 'title', array(
            'label' => 'Poll Title',
            'required' => true,
            'maxlength' => 63,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
            ),
        ));

        $this->addElement('textarea', 'description', array(
            'label' => 'Description',
            'filters' => array(
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '400')),
                new Engine_Filter_Html(array('AllowedTags'=> array('a'))),
            ),
        ));
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.location', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
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
        }
        
        // prepare categories
        $categories = Engine_Api::_()->getDbtable('categories', 'poll')->getCategoriesAssoc();
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

        $this->addElement('textarea', 'options', array(
            'label' => 'Possible Answers',
            'style' => 'display:none;',
        ));
        
        $this->addElement('File', 'photo', array(
            'label' => 'Choose Profile Photo',
						'accept' => 'image/*',
        ));
        $this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg,webp');

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('poll', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            if(engine_count($networkOptions) > 0) {
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => "Networks",
                'description' => 'Choose the Networks to which this Poll will be displayed. If you want to show this Poll to all users then do not select any network.',
                'multiOptions' => $networkOptions,
            ));
            $this->networks->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Init profile view
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('poll', $user, 'auth_view');
        // Comment
        // Init profile comment
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('poll', $user, 'auth_comment');
        // Privacy
        if( $this->_parent_type == 'user' ) {
            $availableLabels = array(
                'everyone'            => 'Everyone',
                'registered'          => 'All Registered Members',
                'owner_network'       => 'Friends or Networks',
                'owner_member_member' => 'Friends of Friends',
                'owner_member'        => 'Friends Only',
                'owner'               => 'Just Me'
            );
            $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
            $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
         } else if( $this->_parent_type == 'group' ) {
            $group = Engine_Api::_()->getItem('group', $this->_parent_id);
            if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
              $viewOptions = $commentOptions = $availableLabels = array(
                'parent_member' => 'Group Members',
                'member'        => 'Poll Guests Only',
                'owner'         => 'Just Me',
              );
            } else {
              $availableLabels = array(
                'everyone'      => 'Everyone',
                'registered'    => 'All Registered Members',
                'parent_member' => 'Group Members',
                'member'        => 'Poll Guests Only',
                'owner'         => 'Just Me',
              );
              $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
              $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
            }
        }

        if( !empty($viewOptions) && engine_count($viewOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($viewOptions) == 1) {
                $this->addElement('hidden', 'auth_view', array('order' => 101, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'Privacy',
                    'description' => 'Who may see this poll?',
                    'multiOptions' => $viewOptions,
                    'value' => key($viewOptions),
                ));
                $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        if( !empty($commentOptions) && engine_count($commentOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($commentOptions) == 1) {
                $this->addElement('hidden', 'auth_comment', array('order' => 102, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post comments on this poll?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Search
        $this->addElement('Checkbox', 'search', array(
            'label' => "Show this poll in search results",
            'value' => 1,
        ));

        // Submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Create Poll',
            'ignore' => true,
            'decorators' => array('ViewHelper'),
            'type' => 'submit'
        ));

        $this->addElement('Cancel', 'cancel', array(
            'prependText' => ' or ',
            'label' => 'cancel',
            'link' => true,
            //'href' => 'javascr',
            //'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            ),
        ));

        $this->addDisplayGroup(array(
            'submit',
            'cancel'
        ), 'buttons', array(
            'decorators' => array(
                'FormElements',
                'DivDivDivWrapper',
            ),
        ));
    }
}
