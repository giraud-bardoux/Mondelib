<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Form_Album_Edit extends Engine_Form
{
    public function init()
    {
        $user = Engine_Api::_()->user()->getViewer();

        $user_level = Engine_Api::_()->user()->getViewer()->level_id;
        $this->setTitle('Edit Album Settings')
            ->setAttrib('name', 'albums_edit')
            ->setAttrib('class', 'global_form form_submit_ajax');

        $this->addElement('Text', 'title', array(
            'label' => 'Album Title',
            'required' => true,
            'notEmpty' => true,
            'validators' => array(
                'NotEmpty',
            ),
            'filters' => array(
                new Engine_Filter_Censor(),
                //new Engine_Filter_HtmlSpecialChars(),
                'StripTags',
                new Engine_Filter_StringLength(array('max' => '63'))
            )
        ));
        $this->title->getValidator('NotEmpty')->setMessage("Please specify an album title");

        // prepare categories
        $categories = Engine_Api::_()->getDbtable('categories', 'album')->getCategoriesAssoc();
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
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.location', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
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

        $this->addElement('Textarea', 'description', array(
            'label' => 'Album Description',
            'rows' => 2,
            'filters' => array(
                new Engine_Filter_Censor(),
                'StripTags',
                //new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            )
        ));

        $this->addElement('Checkbox', 'search', array(
            'label' => "Show this album in search results",
        ));

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('album', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            if(engine_count($networkOptions) > 0) {
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => "Networks",
                'description' => 'Choose the Networks to which this Album will be displayed. If you want to show this Album to all users then do not select any network.',
                'multiOptions' => $networkOptions,
            ));
            }
        }

        // View
        $availableLabels = array(
            'everyone'            => 'Everyone',
            'registered'          => 'All Registered Members',
            'owner_network'       => 'Friends or Networks',
            'owner_member_member' => 'Friends of Friends',
            'owner_member'        => 'Friends Only',
            'owner'               => 'Just Me'
        );

        // Element: auth_view
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $user, 'auth_view');
        $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));

        if( !empty($viewOptions) && engine_count($viewOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($viewOptions) == 1) {
                $this->addElement('hidden', 'auth_view', array('order' => 101, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'Privacy',
                    'description' => 'Who may see this album?',
                    'multiOptions' => $viewOptions,
                    'value' => key($viewOptions),
                ));
                $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Element: auth_comment
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $user, 'auth_comment');
        $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));

        if( !empty($commentOptions) && engine_count($commentOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($commentOptions) == 1) {
                $this->addElement('hidden', 'auth_comment', array('order' => 102, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post comments on this album?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Element: auth_tag
        $tagOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $user, 'auth_tag');
        $tagOptions = array_intersect_key($availableLabels, array_flip($tagOptions));

        if( !empty($tagOptions) && engine_count($tagOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($tagOptions) == 1) {
                $this->addElement('hidden', 'auth_tag', array('order' => 103, 'value' => key($tagOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_tag', array(
                    'label' => 'Tagging',
                    'description' => 'Who may tag photos in this album?',
                    'multiOptions' => $tagOptions,
                    'value' => key($tagOptions),
                ));
                $this->auth_tag->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Submit or succumb!
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Album',
            'type' => 'submit',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    }
}
