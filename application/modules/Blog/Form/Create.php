<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 10168 2014-04-17 16:29:36Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Form_Create extends Engine_Form
{
    public $_error = array();

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
        $this->setTitle('Write New Entry')
            ->setDescription('Compose your new blog entry below, then click "Post Entry" to publish the entry to your blog.')
            ->setAttrib('class', 'global_form form_submit_ajax')
            ->setAttrib('name', 'blogs_create');
        $user = Engine_Api::_()->user()->getViewer();
        $userLevel = Engine_Api::_()->user()->getViewer()->level_id;

        $this->addElement('Text', 'title', array(
            'label' => 'Title',
            'allowEmpty' => false,
            'required' => true,
            'maxlength' => '128',
            'filters' => array(
                new Engine_Filter_Censor(),
                'StripTags',
                new Engine_Filter_StringLength(array('max' => '128'))
            ),
            'autofocus' => 'autofocus',
        ));

        // init to
        $this->addElement('Text', 'tags', array(
            'label'=>'Tags (Keywords)',
            'autocomplete' => 'off',
            'description' => 'Separate tags with commas.',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
            ),
        ));
        $this->tags->getDecorator("Description")->setOption("placement", "append");
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.location', 0)) {
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
        $categories = Engine_Api::_()->getDbtable('categories', 'blog')->getCategoriesAssoc();
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

        $this->addElement('Select', 'draft', array(
            'label' => 'Status',
            'multiOptions' => array("0"=>"Published", "1"=>"Saved As Draft"),
            'description' => 'If this entry is published, it cannot be switched back to draft mode.'
        ));
        $this->draft->getDecorator('Description')->setOption('placement', 'append');

        //$allowedHtml = Engine_Api::_()->authorization()->getPermission($userLevel, 'blog', 'auth_html');

        $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'upload-photo'), 'default', true);

        $editorOptions = array(
            'uploadUrl' => $uploadUrl,
            //'html' => (bool) $allowedHtml,
        );

        $this->addElement('TinyMce', 'body', array(
            'label' => 'Description',
            'disableLoadDefaultDecorators' => true,
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

        $this->addElement('File', 'photo', array(
            'label' => 'Choose Profile Photo',
            'accept' => 'image/*',
        ));
        $this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg,webp');

        $this->addElement('Checkbox', 'search', array(
            'label' => 'Show this blog entry in search results',
            'value' => 1,
        ));

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('blog', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            if(engine_count($networkOptions) > 0) {
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => 'Networks',
                'description' => 'Choose the Networks to which this Blog will be displayed. If you want to show this Blog to all users then do not select any network.',
                'multiOptions' => $networkOptions,
            ));
            $this->networks->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Element: auth_view
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $user, 'auth_view');
        // Element: auth_comment
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $user, 'auth_comment');

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
                'member'        => 'Blog Guests Only',
                'owner'         => 'Just Me',
              );
            } else {
              $availableLabels = array(
                'everyone'      => 'Everyone',
                'registered'    => 'All Registered Members',
                'parent_member' => 'Group Members',
                'member'        => 'Blog Guests Only',
                'owner'         => 'Just Me',
              );
              $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
              $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
            }
        }

        if( !empty($viewOptions) && engine_count($viewOptions) >= 1 ) {
            // Make a hidden field
            if( engine_count($viewOptions) == 1 ) {
                $this->addElement('hidden', 'auth_view', array( 'order' => 101, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'Privacy',
                    'description' => 'Who may see this blog entry?',
                    'multiOptions' => $viewOptions,
                    'value' => key($viewOptions),
                ));
                $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        if( !empty($commentOptions) && engine_count($commentOptions) >= 1 ) {
            // Make a hidden field
            if( engine_count($commentOptions) == 1 ) {
                $this->addElement('hidden', 'auth_comment', array('order' => 102, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post comments on this blog entry?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

//         $this->addElement('Hash', 'token', array(
//             'timeout' => 3600,
//         ));
        
        // Element: submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Post Entry',
            'type' => 'submit',
        ));
    }

    public function postEntry()
    {
        $values = $this->getValues();

        $user = Engine_Api::_()->user()->getViewer();
        $title = $values['title'];
        $body = $values['body'];
        $category_id = $values['category_id'];
        $tags = preg_split('/[,]+/', $values['tags']);

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
            // Transaction
            $table = Engine_Api::_()->getDbtable('blogs', 'blog');

            // insert the blog entry into the database
            $row = $table->createRow();
            $row->owner_id   =  $user->getIdentity();
            $row->owner_type = $user->getType();
            $row->category_id = $category_id;
            $row->creation_date = date('Y-m-d H:i:s');
            $row->modified_date   = date('Y-m-d H:i:s');
            $row->title   = $title;
            $row->body   = $body;
            //$row->category_id = $category_id;
            $row->save();

            $blogId = $row->blog_id;

            if( $tags ) {
                $this->handleTags($blogId, $tags);
            }

            $attachment = Engine_Api::_()->getItem($row->getType(), $blogId);
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $row, 'blog_new');
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }
    }

    public function handleTags($blogId, $tags)
    {
        $tagTable = Engine_Api::_()->getDbtable('tags', 'blog');
        $tabMapTable = Engine_Api::_()->getDbtable('tagmaps', 'blog');
        $tagDup = array();
        foreach( $tags as $tag ) {

            $tag = htmlspecialchars((trim($tag)));
            if( !engine_in_array($tag, $tagDup) && $tag !="" && strlen($tag) <  20 ) {
                $tagId = $this->checkTag($tag);
                // check if it is new. if new, createnew tag. else, get the tag_id and insert
                if( !$tagId ) {
                    $tagId = $this->createNewTag($tag, $blogId, $tagTable);
                }

                $tabMapTable->insert(array(
                    'blog_id' => $blogId,
                    'tag_id' => $tagId
                ));
                $tagDup[] = $tag;
            }
            if( strlen($tag) >= 20 ) {
                $this->_error[] = $tag;
            }
        }
    }

    public function checkTag($text)
    {
        $table = Engine_Api::_()->getDbtable('tags', 'blog');
        $select = $table->select()->order('text ASC')->where('text = ?', $text);
        $results = $table->fetchRow($select);
        $tagId = "";
        if( $results ) $tagId = $results->tag_id;
        return $tagId;
    }

    public function createNewTag($text, $blogId, $tagTable)
    {
        $row = $tagTable->createRow();
        $row->text =  $text;
        $row->save();
        $tagId = $row->tag_id;

        return $tagId;
    }
}
