<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Album.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Form_Album extends Engine_Form
{
    public function init()
    {
        $user_level = Engine_Api::_()->user()->getViewer()->level_id;
        $user = Engine_Api::_()->user()->getViewer();

        // Init form
        $this
            ->setTitle('Add New Photos')
            ->setDescription('Choose photos on your computer to add to this album.')
            ->setAttrib('id', 'form-upload')
            ->setAttrib('name', 'albums_create')
            ->setAttrib('enctype','multipart/form-data')
            ->setAttrib('class', 'global_form form_submit_ajax')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
        ;

        // Init album
        $albumTable = Engine_Api::_()->getItemTable('album');
        $myAlbums = $albumTable->select()
            ->from($albumTable, array('album_id', 'title'))
            ->where('owner_type = ?', 'user')
            ->where('owner_id = ?', Engine_Api::_()->user()->getViewer()->getIdentity())
            ->query()
            ->fetchAll();

        $albumOptions = array('0' => 'Create A New Album');
        foreach( $myAlbums as $myAlbum ) {
            $albumOptions[$myAlbum['album_id']] = $myAlbum['title'];
        }

        $this->addElement('Select', 'album', array(
            'label' => 'Choose Album',
            'multiOptions' => $albumOptions,
            'onchange' => "updateTextFields()",
        ));

        // Init name
        $this->addElement('Text', 'title', array(
            'label' => 'Album Title',
            'maxlength' => '40',
            'filters' => array(
                //new Engine_Filter_HtmlSpecialChars(),
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
            )
        ));

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

        // Init descriptions
        $this->addElement('Textarea', 'description', array(
            'label' => 'Album Description',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                //new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            ),
        ));


        //ADD AUTH STUFF HERE

        $availableLabels = array(
            'everyone'              => 'Everyone',
            'registered'            => 'All Registered Members',
            'owner_network'         => 'Friends or Networks',
            'owner_member_member'   => 'Friends of Friends',
            'owner_member'          => 'Friends Only',
            'owner'                 => 'Just Me'
        );

        // Init search
        $this->addElement('Checkbox', 'search', array(
            'label' => Zend_Registry::get('Zend_Translate')->_("Show this album in search results"),
            'value' => 1,
            'disableTranslator' => true
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
            $this->networks->getDecorator('Description')->setOption('placement', 'append');
            }
        }

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

        // Init file
        $this->addElement('HTMLUpload', 'file', [
            'form' => '#form-upload',
            'multi' => true,
            'url' => $this->getView()->url([
                'controller' => 'index',
                'action' => 'upload-photo'
            ], 'album_extended'),
            'accept' => 'image/*',
        ]);

        // Init submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Photos',
            'type' => 'submit',
        ));
    }

    public function clearAlbum()
    {
        $this->getElement('album')->setValue(0);
    }

    public function saveValues()
    {   
        $viewer = Engine_Api::_()->user()->getViewer();
        $setCover = false;
        $values = $this->getValues();        
        $params = Array();
        if ((empty($values['owner_type'])) || (empty($values['owner_id'])))
        {
            $params['owner_id'] = Engine_Api::_()->user()->getViewer()->user_id;
            $params['owner_type'] = 'user';
        }
        else
        {
            $params['owner_id'] = $values['owner_id'];
            $params['owner_type'] = $values['owner_type'];
            throw new Zend_Exception("Non-user album owners not yet implemented");
        }

        if( ($values['album'] == 0) )
        {
            $params['title'] = $values['title'];
            if (empty($params['title'])) {
                $params['title'] = "Untitled Album";
            }
            $params['category_id'] = (int) @$values['category_id'];
            $params['subcat_id'] = (int) @$values['subcat_id'];
            $params['subsubcat_id'] = (int) @$values['subsubcat_id'];
            $params['description'] = $values['description'];
            $params['search'] = $values['search'];
            if (is_array($values['networks']) && isset($values['networks'])) {
                $network_privacy = 'network_'. implode(',network_', $values['networks']);
                $params['networks'] = implode(',', $values['networks']);
            }
            if( empty($values['auth_view']) ) {
                $values['auth_view'] = 'everyone';
            }
            if( empty($values['auth_comment']) ) {
                $values['auth_comment'] = 'owner_member';
            }
            if( empty($values['auth_tag']) ) {
                $values['auth_tag'] = 'owner_member';
            }

            $params['view_privacy'] = $values['auth_view'];
            
            //approve setting work
            $params['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $viewer, 'approve');

            $album = Engine_Api::_()->getDbtable('albums', 'album')->createRow();
            if (is_null($values['subcat_id']))
              $params['subcat_id'] = 0;

            if (is_null($values['subsubcat_id']))
              $params['subsubcat_id'] = 0;
            
            $params['location'] = $_POST['location'] ? $_POST['location'] : null;
            $album->setFromArray($params);
            $album->save();
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $album);

            $setCover = true;

            // CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $tagMax = array_search($values['auth_tag'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($album, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($album, $role, 'tag', ($i <= $tagMax));
            }
        }
        else
        {
            if (!isset($album))
            {
                $album = Engine_Api::_()->getItem('album', $values['album']);
            }
        }
        
        // Add action and attachments
        $api = Engine_Api::_()->getDbtable('actions', 'activity');
        $countFiles = is_countable($values['file']) ? engine_count($values['file']) : 0;
        $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' => $countFiles, 'privacy' => isset($values['networks'])? $network_privacy : null));
        
        $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
        // Do other stuff
        $count = 0;
        
        foreach( $values['file'] as $photoId )
        {
            $path = $photoTable->getTemPhoto($photoId,1);
            if(!file_exists($path))
                continue;
            $photo = $photoTable->createRow();
            $photo->setFromArray([
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            ]);
            $photo->save();

            $photo->order = $photo->photo_id;
            $photo->setPhoto($path);
            $photo->save();

            if( $setCover )
            {
                $album->photo_id = $photo->photo_id;
                $album->save();
                $setCover = false;
            }

            $photo->album_id = $album->album_id;
            $photo->order = $photo->photo_id;
            
            $photo->approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $viewer, 'photoapprove');
            $photo->save();
            if($photo->approved) {
              $photo->resubmit = 1;
              $photo->save();
            } elseif($values['album'] != 0) {
              //Start Send Approval Request to Admin
              Engine_Api::_()->core()->contentApprove($photo, 'photo');
            }
            @unlink($path);
            
            if( $action instanceof Activity_Model_Action && $count < 100 ) {
              $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
            }
            $count++;
        }
        
//         if($values['album'] != 0) {
//           if(!Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $viewer, 'photoapprove')) {
//             Engine_Api::_()->core()->contentApprove($album, 'album photo');
//           }
//         }

        //Start Send Approval Request to Admin
        if($values['album'] == 0) {
          Engine_Api::_()->core()->contentApprove($album, 'album');
        }

        return $album;
    }

}
