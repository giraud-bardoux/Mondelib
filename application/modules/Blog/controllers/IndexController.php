<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10118 2013-11-20 17:15:32Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_IndexController extends Core_Controller_Action_Standard
{
    public function init()
    {
        // only show to member_level if authorized
        if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'view')->isValid() ) return;
    }

    public function indexAction()
    {
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();

        // Permissions
        $this->view->canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

        // Make form
        // Note: this code is duplicated in the blog.browse-search widget
        $this->view->form = $form = new Blog_Form_Search();

        $form->removeElement('draft');
        if( !$viewer->getIdentity() ) {
            $form->removeElement('show');
        }

        // Process form
        $defaultValues = $form->getValues();
        if( $form->isValid($this->_getAllParams()) ) {
            $values = $form->getValues();
        } else {
            $values = $defaultValues;
        }
        $this->view->formValues = array_filter($values);
        $values['draft'] = "0";
        $values['visible'] = "1";

        // Do the show thingy
        if( @$values['show'] == 2 ) {
            // Get an array of friend ids
            $table = Engine_Api::_()->getItemTable('user');
            $select = $viewer->membership()->getMembersSelect('user_id');
            $friends = $table->fetchAll($select);
            // Get stuff
            $ids = array();
            foreach( $friends as $friend )
            {
                $ids[] = $friend->user_id;
            }
            //unset($values['show']);
            $values['users'] = $ids;
        }

        $this->view->assign($values);
        
        if(!empty($_GET['tag_id']) && isset($_GET['tag_id'])) {
          $values['tag'] = $_GET['tag_id'];
        }
        
        // Get blogs
        $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);

        $items_per_page = Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.page', 12);
        $paginator->setItemCountPerPage($items_per_page);

        $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );

        if( !empty($values['category']) ) {
            $this->view->categoryObject = Engine_Api::_()->getDbtable('categories', 'blog')
                ->find($values['category'])->current();
        }

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function viewAction()
    {
        // Check permission
        $viewer = Engine_Api::_()->user()->getViewer();
        $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
        if( $blog ) {
            Engine_Api::_()->core()->setSubject($blog);
        }

        if( !$this->_helper->requireSubject()->isValid() ) {
            return;
        }
        if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'view')->isValid() ) {
            return;
        }
        if( !$blog || !$blog->getIdentity() || (($blog->draft || !$blog->approved) && !$blog->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_forward('requireauth', 'error', 'core');
        }
        
        if($blog->parent_type == 'group' && $blog->parent_id) {
          $group = Engine_Api::_()->getItem($blog->parent_type, $blog->parent_id);
          
          if( !$group || !$group->getIdentity() || ((!$group->approved) && !$group->isOwner($viewer)) ) {
            if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
            } else
              return $this->_forward('requireauth', 'error', 'core');
          }
          
          $viewPermission = $group->authorization()->isAllowed($viewer, 'view');
          if(empty($viewPermission)) {
            return $this->_forward('requireauth', 'error', 'core');
          }
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
        $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($blog);
        if(empty($networkPrivacy))
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Prepare data
        $blogTable = Engine_Api::_()->getDbtable('blogs', 'blog');

        if (strpos($blog->body, '<') === false) {
            $blog->body = nl2br($blog->body);
        }

        $this->view->blog = $blog;
        $this->view->owner = $owner = $blog->getOwner();
        $this->view->viewer = $viewer;

        if( !$blog->isOwner($viewer) ) {
            $blogTable->update(array(
                'view_count' => new Zend_Db_Expr('view_count + 1'),
            ), array(
                'blog_id = ?' => $blog->getIdentity(),
            ));
        }

        // Get tags
        $this->view->blogTags = $blog->tags()->getTagMaps();

        // Get category
        if( !empty($blog->category_id) ) {
            $this->view->category = Engine_Api::_()->getDbtable('categories', 'blog')
                ->find($blog->category_id)->current();
        }

        // Get styles
        $table = Engine_Api::_()->getDbtable('styles', 'core');
        $style = $table->select()
            ->from($table, 'style')
            ->where('type = ?', 'user_blog')
            ->where('id = ?', $owner->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn();
        if( !empty($style) ) {
            try {
                $this->view->headStyle()->appendStyle($style);
            }
                // silence any exception, exceptin in development mode
            catch (Exception $e) {
                if (APPLICATION_ENV === 'development') {
                    throw $e;
                }
            }
        }

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    // USER SPECIFIC METHODS
    public function manageAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;

        // Render
        $this->_helper->content->setEnabled();

        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->form = $form = new Blog_Form_Search();
        $this->view->canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

        // Process form
        $defaultValues = $form->getValues();
        if( $form->isValid($this->_getAllParams()) ) {
            $values = $form->getValues();
        } else {
            $values = $defaultValues;
        }
        $this->view->formValues = array_filter($values);
        $values['user_id'] = $viewer->getIdentity();
				$values['showblog'] = 1;
				$values['actionName'] = 'manage';
        // Get paginator
        $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);
        $items_per_page = Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.page', 12);
        $paginator->setItemCountPerPage($items_per_page);
        $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );
    }

    public function listAction()
    {
        // Preload info
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->owner = $owner = Engine_Api::_()->getItem('user', $this->_getParam('user_id'));
        Engine_Api::_()->core()->setSubject($owner);

        if( !$this->_helper->requireSubject()->isValid() ) {
            return;
        }


        // Make form
        $form = new Blog_Form_Search();
        // Process form
        $defaultValues = $form->getValues();
        if( $form->isValid($this->getRequest()->getParams()) ) {
            $values = $form->getValues();
        } else {
            $values = $defaultValues;
        }
        $this->view->formValues = array_filter($values);
        $values['user_id'] = $owner->getIdentity();

        // Prepare data
        $blogTable = Engine_Api::_()->getDbtable('blogs', 'blog');

        // Get paginator
        $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);
        $items_per_page = Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.page', 12);
        $paginator->setItemCountPerPage($items_per_page);
        $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function createAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->isValid()) return;

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);

        $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'blog', 'max');
        $this->view->current_count = $paginator->getTotalItemCount();

        $parent_type = $this->_getParam('parent_type');
        $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));

        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'blog') ) {
                return;
            }
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }

        $this->view->parent_type = $parent_type;
        
        $this->view->category_id = (isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0;
        $this->view->subcat_id = (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0;
        $this->view->subsubcat_id = (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0;
        
        // Prepare form
        $this->view->form = $form = new Blog_Form_Create(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id
        ));
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
          if($group->view_privacy == 'member')
            $view_privacy = 'parent_member';
          else 
            $view_privacy = $group->view_privacy;
          $form->getElement('auth_view')->setValue($view_privacy);
        }

        // If not post or form not valid, return
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        
        $itemFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('blog', $this->view->viewer()->level_id, 'flood');
        if(!empty($itemFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("blogs",'blog');
            $select = $tableFlood->select()->where("owner_id = ?",$this->view->viewer()->getIdentity())->order("creation_date DESC");
            if($itemFlood[1] == "minute"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($itemFlood[1] == "day"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(engine_count($floodItem) && $itemFlood[0] <= engine_count($floodItem)){
                $message = Engine_Api::_()->core()->floodCheckMessage($itemFlood,$this->view);
                $errors[] = array('errorMessage' => $message);
                echo json_encode(array('status' => false, 'error_message' => $errors));die;
                //$form->addError($message);
                //return;
            }
        }

        // Process
        $table = Engine_Api::_()->getItemTable('blog');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            // Create blog
            $viewer = Engine_Api::_()->user()->getViewer();
            $formValues = $form->getValues();

            if (isset($formValues['networks'])) {
                $network_privacy = 'network_'. implode(',network_', $formValues['networks']);
                $formValues['networks'] = implode(',', $formValues['networks']);
            }

            if( empty($formValues['auth_view']) ) {
                $formValues['auth_view'] = 'everyone';
            }

            if( empty($formValues['auth_comment']) ) {
                $formValues['auth_comment'] = 'everyone';
            }

            $values = array_merge($formValues, array(
                'owner_type' => $viewer->getType(),
                'owner_id' => $viewer->getIdentity(),
                'view_privacy' => $formValues['auth_view'],
            ));
            
            //approve setting work
            $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $viewer, 'approve');

            $values['parent_type'] = $parent_type;
            $values['parent_id'] =  $parent_id;
            
            $blog = $table->createRow();
            
            if (is_null($values['subcat_id']))
              $values['subcat_id'] = 0;
              
            if (is_null($values['subsubcat_id']))
              $values['subsubcat_id'] = 0;
              
            $blog->setFromArray($values);
            $blog->save();
            
            //Save editor images
            Engine_Api::_()->core()->saveTinyMceImages($values['body'], $blog);
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $blog);

            if( !empty($values['photo']) ) {
                $blog->setPhoto($form->photo);
            }

            // Auth
            $auth = Engine_Api::_()->authorization()->context;

            if( $values['parent_type'] == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
            }

            // Add tags
            $tags = preg_split('/[,]+/', $values['tags']);
            $blog->tags()->addTagMaps($viewer, $tags);

            // Add activity only if blog is published
            if( $values['draft'] == 0) {
							if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
								$action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $group, 'group_blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
							} else {
								$action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
							}
							// make sure action exists before attaching the blog to the activity
							if( $action ) {
									Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
							}
            }
            
						//Start Send Approval Request to Admin
						Engine_Api::_()->core()->contentApprove($blog, 'blog');

            // Send notifications for subscribers
            Engine_Api::_()->getDbtable('subscriptions', 'blog')->sendNotifications($blog);

            //Send to all group members
            if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
              $members = Engine_Api::_()->group()->groupMembers($group->getIdentity());
              foreach($members as $member) {
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($member, $viewer, $group, 'group_blogcreate');
              }
            }
            
            // Commit
            $db->commit();
        } catch( Exception $e ) {
          //return $this->exceptionWrapper($e, $form, $db);
          $errors[] = array('errorMessage' => $e->getMessage());
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
          echo json_encode(array('status' => true, 'redirectURL' => $group->getHref()));die;
          //$this->_redirectCustom($group);
        } else {
          echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'blog_general',true)));die;
          //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
        }
    }

    public function editAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;

        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->blog = $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
        if( !Engine_Api::_()->core()->hasSubject('blog') ) {
            Engine_Api::_()->core()->setSubject($blog);
        }
        
        $this->view->category_id = (isset($blog->category_id) && $blog->category_id != 0) ? $blog->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
        $this->view->subcat_id = (isset($blog->subcat_id) && $blog->subcat_id != 0) ? $blog->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
        $this->view->subsubcat_id = (isset($blog->subsubcat_id) && $blog->subsubcat_id != 0) ? $blog->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

        if( !$this->_helper->requireSubject()->isValid() ) return;

        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('blog_main');

        $parent_type = $blog->parent_type;
        $parent_id = $blog->parent_id;

        if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'edit')->isValid() ) {
            return;
        }

        // Prepare form
        $this->view->form = $form = new Blog_Form_Edit(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id
        ));

        // Populate form
        $form->populate($blog->toArray());

        $tagStr = '';
        foreach( $blog->tags()->getTagMaps() as $tagMap ) {
            $tag = $tagMap->getTag();
            if( !isset($tag->text) ) continue;
            if( '' !== $tagStr ) $tagStr .= ', ';
            $tagStr .= $tag->text;
        }

        $form->populate(array(
            'tags' => $tagStr,
            'networks' => explode(',', $blog->networks),
        ));
        $this->view->tagNamePrepared = $tagStr;

        $auth = Engine_Api::_()->authorization()->context;
        if( $parent_type == 'group' ) {
            $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
        } else {
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        }

        foreach( $roles as $role ) {
            if ($form->auth_view){
                if( $auth->isAllowed($blog, $role, 'view') ) {
                    $form->auth_view->setValue($role);
                }
            }

            if ($form->auth_comment){
                if( $auth->isAllowed($blog, $role, 'comment') ) {
                    $form->auth_comment->setValue($role);
                }
            }
        }

        // hide status change if it has been already published
        if( $blog->draft == "0" ) {
            $form->removeElement('draft');
        }


        // Check post/form
        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }

        // Process
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $values = $form->getValues();

            if (isset($values['networks'])) {
                $network_privacy = 'network_'. implode(',network_', $values['networks']);
                $values['networks'] = implode(',', $values['networks']);
            }

            if( empty($values['auth_view']) ) {
                $values['auth_view'] = 'everyone';
            }
            if( empty($values['auth_comment']) ) {
                $values['auth_comment'] = 'everyone';
            }

            $values['view_privacy'] = $values['auth_view'];
            $blog->setFromArray($values);
            $blog->modified_date = date('Y-m-d H:i:s');
            $blog->save();

            Engine_Api::_()->core()->saveTinyMceImages($values['body'], $blog);

            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $blog, 'edit');

            // Add photo
            if( !empty($values['photo']) ) {
                $blog->setPhoto($form->photo);
            }

            // Auth
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
            }

            // handle tags
            $tags = preg_split('/[,]+/', $values['tags']);
            $blog->tags()->setTagMaps($viewer, $tags);

            // insert new activity if blog is just getting published
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($blog);
            if( engine_count($action->toArray()) <= 0 && $values['draft'] == '0' ) {
            
                $blog->creation_date = date('Y-m-d H:i:s');
                $blog->save();
                
                if( $parent_type == 'group') {
                  $group = Engine_Api::_()->getItem($parent_type, $parent_id);
                  $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $group, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
                } else {
                  $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
                }
                // make sure action exists before attaching the blog to the activity
                if( $action != null ) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
                }
            }

            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach( $actionTable->getActionsByObject($blog) as $action ) {
              $action->privacy = isset($values['networks'])? $network_privacy : null;
              $action->save();
              $actionTable->resetActivityBindings($action);
            }

            // Send notifications for subscribers
            Engine_Api::_()->getDbtable('subscriptions', 'blog')
                ->sendNotifications($blog);

            $db->commit();

        }
        catch( Exception $e ) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'blog_general',true)));die;
        //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
    }

    public function deleteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $blog = Engine_Api::_()->getItem('blog', $this->getRequest()->getParam('blog_id'));
        if( !$this->_helper->requireAuth()->setAuthParams($blog, null, 'delete')->isValid()) return;

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        $this->view->form = $form = new Blog_Form_Delete();

        if( !$blog ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Blog entry doesn't exist or not authorized to delete");
            return;
        }

        if( !$this->getRequest()->isPost() ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $db = $blog->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $blog->delete();

            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your blog entry has been deleted.');
        return $this->_forward('success' ,'utility', 'core', array(
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'blog_general', true),
            'messages' => Array($this->view->message)
        ));
    }

    public function styleAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'style')->isValid()) return;

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        // Require user
        if( !$this->_helper->requireUser()->isValid() ) return;
        $user = Engine_Api::_()->user()->getViewer();

        // Make form
        $this->view->form = $form = new Blog_Form_Style();

        // Get current row
        $table = Engine_Api::_()->getDbtable('styles', 'core');
        $select = $table->select()
            ->where('type = ?', 'user_blog') // @todo this is not a real type
            ->where('id = ?', $user->getIdentity())
            ->limit(1);

        $row = $table->fetchRow($select);

        // Check post
        if( !$this->getRequest()->isPost() )
        {
            $form->populate(array(
                'style' => ( null === $row ? '' : $row->style )
            ));
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) )
        {
            return;
        }

        // Cool! Process
        $style = $form->getValue('style');

        // Save
        if( null == $row )
        {
            $row = $table->createRow();
            $row->type = 'user_blog'; // @todo this is not a real type
            $row->id = $user->getIdentity();
        }

        $row->style = $style;
        $row->save();

        $this->view->draft = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_("Your changes have been saved.");
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => false,
            'messages' => array($this->view->message)
        ));
    }

    public function uploadPhotoAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();

        $this->_helper->layout->disableLayout();

        if( !Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') ) {
            return false;
        }

        if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ) return;

        if( !$this->_helper->requireUser()->checkRequire() )
        {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        if( !$this->getRequest()->isPost() )
        {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        if( !isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name']) )
        {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }

        $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
        $db->beginTransaction();

        try
        {
            $viewer = Engine_Api::_()->user()->getViewer();

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray(array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            ));
            $photo->save();

            $photo->setPhoto($_FILES['userfile']);

            $this->view->status = true;
            $this->view->name = $_FILES['userfile']['name'];
            $this->view->photo_id = $photo->photo_id;
            $this->view->photo_url = $photo->getPhotoUrl();

            $table = Engine_Api::_()->getDbtable('albums', 'album');
            $album = $table->getSpecialAlbum($viewer, 'blog');

            $photo->album_id = $album->album_id;
            $photo->save();

            if( !$album->photo_id )
            {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }

            $auth      = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($photo, 'everyone', 'view',    true);
            $auth->setAllowed($photo, 'everyone', 'comment', true);
            $auth->setAllowed($album, 'everyone', 'view',    true);
            $auth->setAllowed($album, 'everyone', 'comment', true);

            $photo->order = $photo->photo_id;
            $photo->save();
            
            $db->commit();

        } catch( Album_Model_Exception $e ) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = $this->view->translate($e->getMessage());
            throw $e;
            return;

        } catch( Exception $e ) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
            throw $e;
            return;
        }
    }
    
    public function subcategoryAction() {

      $category_id = $this->_getParam('category_id', null);
      $CategoryType = $this->_getParam('type', null);
      $selected = $this->_getParam('selected', null);
      if ($category_id) {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'blog');
        $category_select = $categoryTable->select()
                                        ->from($categoryTable->info('name'))
                                        ->where('subcat_id = ?', $category_id);
        $subcategory = $categoryTable->fetchAll($category_select);
        $count_subcat = engine_count($subcategory->toarray());

        $data = '';
        if ($subcategory && $count_subcat) {
          if ($CategoryType == 'search') {
            $data .= '<option value="0">' . Zend_Registry::get('Zend_Translate')->_("Choose 2nd Level Category") . '</option>';
            foreach ($subcategory as $category) {
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
            }
          } else {
            $data .= '<option value=""></option>';
            foreach ($subcategory as $category) {
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
            }

          }
        }
      } else
        $data = '';
      echo $data;die;
    }

    public function subsubcategoryAction() {

      $category_id = $this->_getParam('subcategory_id', null);
      $CategoryType = $this->_getParam('type', null);
      $selected = $this->_getParam('selected', null);
      if ($category_id) {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'blog');
        $category_select = $categoryTable->select()
          ->from($categoryTable->info('name'))
          ->where('subsubcat_id = ?', $category_id);
        $subcategory = $categoryTable->fetchAll($category_select);
        $count_subcat = engine_count($subcategory->toarray());

        $data = '';
        if ($subcategory && $count_subcat) {
          $data .= '<option value=""></option>';
          foreach ($subcategory as $category) {
            $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '">' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
          }

        }
      } else
        $data = '';
      echo $data;
      die;
    }
}
