<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Video.php 10268 2014-06-20 12:59:21Z vinit $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Video_Form_Video extends Engine_Form
{
    protected $_parent_type;

    protected $_parent_id;
    protected $_fromApi;
    public function getFromApi() {
        return $this->_fromApi;
    }
    public function setFromApi($fromApi) {
        $this->_fromApi = $fromApi;
        return $this;
    }
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
        // Init form
        $this
            ->setTitle('Add New Video')
            ->setAttrib('id', 'form-upload')
            ->setAttrib('name', 'video_create')
            ->setAttrib('enctype','multipart/form-data')
            ->setAttrib('class', 'global_form form_submit_ajax')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
        ;
        //->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module'=>'album', 'controller'=>'album', 'action'=>'upload-photo', 'format' => 'json'), 'default'));
        $user = Engine_Api::_()->user()->getViewer();

        // Init video
        $this->addElement('Select', 'type', array(
            'label' => 'Video Source',
            'required' => true,
            'multiOptions' => array('0' => 'Choose Source'),
            'onchange' => "updateTextFields()",
        ));

        //YouTube, Vimeo
        $videoOptions = Array();
        $videoOptions['iframely'] = 'External Site';

        //My Computer
        if( $this->_parent_type == 'user' ) {
          $allowedUpload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'upload');
        } else if( $this->_parent_type == 'group' ) {
          $allowedUpload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'videoupload');
        }
        $ffmpegPath = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
        if( !empty($ffmpegPath) && $allowedUpload ) {
            $lable = 'My Computer';
            if( Engine_Api::_()->hasModuleBootstrap('mobi') && Engine_Api::_()->mobi()->isMobile() ) {
                $lable = 'My Device';
            }
            $videoOptions['upload'] = $lable;
        }
        $this->type->addMultiOptions($videoOptions);

        //ADD AUTH STUFF HERE

        // Init url
        $this->addElement('Text', 'url', array(
            'label' => 'Video Link (URL)',
            'required' => false,
            'description' => 'Paste the web address of the video here. (For Instagram videos, only IGTV videos are supported.)',
            'maxlength' => '5000'
        ));
        $this->url->getDecorator("Description")->setOption("placement", "append");

        // Init name
        $this->addElement('Text', 'title', array(
            'label' => 'Video Title',
            'maxlength' => '100',
            'allowEmpty' => false,
            'required' => true,
            'filters' => array(
                //new Engine_Filter_HtmlSpecialChars(),
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '100')),
            )
        ));

        // init tag
        $this->addElement('Text', 'tags',array(
            'label'=>'Tags (Keywords)',
            'autocomplete' => 'off',
            'description' => 'Separate tags with commas.',
            'filters' => array(
                new Engine_Filter_Censor(),
                new Engine_Filter_HtmlSpecialChars(),
            )
        ));
        $this->tags->getDecorator("Description")->setOption("placement", "append");
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.location', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
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
            'label' => 'Video Description',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                //new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            ),
        ));

        // prepare categories
        $categories = Engine_Api::_()->video()->getCategories();

        if (engine_count($categories)!=0){
            $categories_prepared[0]= "";
            foreach ($categories as $category){
                $categories_prepared[$category->category_id]= $category->category_name;
            }

            // category field
            $this->addElement('Select', 'category_id', array(
              'label' => 'Category',
              'multiOptions' => $categories_prepared,
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

        // Init search
        $this->addElement('Checkbox', 'search', array(
            'label' => "Show this video in search results",
            'value' => 1,
        ));

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('video', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            if(engine_count($networkOptions) > 0) {
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => 'Networks',
                'description' => 'Choose the Networks to which this Video will be displayed. If you want to show this Video to all users then do not select any network.',
                'multiOptions' => $networkOptions,
            ));
            }
        }

        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_view');
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_comment');

        if( $this->_parent_type == 'user' ) {
            // View
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
        
            //$viewOptions[] = 'parent_member';
            $group = Engine_Api::_()->getItem('group', $this->_parent_id);
            if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
              $viewOptions = $commentOptions = $availableLabels = array(
                'parent_member' => 'Group Members',
                'member'        => 'Video Guests Only',
                'owner'         => 'Just Me',
              );
            } else {
              $availableLabels = array(
                'everyone'      => 'Everyone',
                'registered'    => 'All Registered Members',
                'parent_member' => 'Group Members',
                'member'        => 'Video Guests Only',
                'owner'         => 'Just Me',
              );
              $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
              $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
            }
        }

        if( !empty($viewOptions) && engine_count($viewOptions) >= 1 ) {
            // Make a hidden field
            if(engine_count($viewOptions) == 1) {
                $this->addElement('hidden', 'auth_view', array('order' => 5, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'Privacy',
                    'description' => 'Who may see this video?',
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
                $this->addElement('hidden', 'auth_comment', array('order' => 6, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post comments on this video?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Video rotation


        $this->addElement('Hidden', 'code', array(
            'order' => 1
        ));
        $this->addElement('Hidden', 'id', array(
            'order' => 2
        ));
        $this->addElement('Hidden', 'ignore', array(
            'order' => 3
        ));
        // Init file
        //$this->addElement('FancyUpload', 'file');

        if(!$this->_fromApi) {
            $this->addElement('Select', 'rotation', array(
                'label' => 'Video Rotation',
                'multiOptions' => array(
                  0 => '',
                  90 => '90&deg;',
                  180 => '180&deg;',
                  270 => '270&deg;'
                ),
            ));
            $fancyUpload = new Engine_Form_Element_HTMLUpload('Filedata');
            $this->addElement($fancyUpload);
        }else{
            $this->addElement('Select', 'rotation', array(
                'label' => 'Video Rotation',
                'multiOptions' => array(
                    0 => '',
                    90 => '90 degree',
                    180 => '180 degree',
                    270 => '270 degree'
                ),
            ));
            $this->addElement('file', 'upload_video', array(
                'Label'=>'Upload Video'
            ));
        }
        // Init submit
        $this->addElement('Button', 'upload', array(
            'label' => 'Save Video',
            'type' => 'submit',
        ));

        //$this->addElements(Array($album, $name, $description, $search, $file, $submit));
    }

    public function clearAlbum()
    {
        $this->getElement('album')->setValue(0);
    }

    public function saveValues()
    {
        $set_cover = False;
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
            $params['name'] = $values['name'];
            if (empty($params['name'])) {
                $params['name'] = "Untitled Album";
            }
            $params['description'] = $values['description'];
            $params['search'] = $values['search'];
            $album = Engine_Api::_()->getDbtable('albums', 'album')->createRow();
            $set_cover = True;
            $album->setFromArray($params);
            $album->save();


            // CREATE AUTH STUFF HERE
            /*    $context = $this->api()->authorization()->context;
          foreach( array('everyone', 'registered', 'member') as $role )
          {
            $context->setAllowed($this, $role, 'view', true);
          }
          $context->setAllowed($this, 'member', 'comment', true);
            */


        }
        else
        {
            if (is_null($album))
            {
                $album = Engine_Api::_()->getItem('album', $values['album']);
            }
        }

        // Add action and attachments
        $api = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' => engine_count($values['file'])));

        // Do other stuff
        $count = 0;
        foreach( $values['file'] as $photo_id )
        {
            $photo = Engine_Api::_()->getItem("album_photo", $photo_id);
            if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) continue;

            if( $set_cover )
            {
                $album->photo_id = $photo_id;
                $album->save();
                $set_cover = false;
            }

            $photo->collection_id = $album->album_id;
            $photo->save();

            if( $action instanceof Activity_Model_Action && $count < 8 )
            {
                $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
            }
            $count++;
        }

        return $album;
    }

}