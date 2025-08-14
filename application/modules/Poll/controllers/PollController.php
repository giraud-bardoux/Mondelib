<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PollController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_PollController extends Core_Controller_Action_Standard
{
    public function init()
    {
        // Get subject
        $poll = null;
        if( null !== ($pollIdentity = $this->_getParam('poll_id')) ) {
            $poll = Engine_Api::_()->getItem('poll', $pollIdentity);
            if( null !== $poll ) {
                Engine_Api::_()->core()->setSubject($poll);
            }
        }

        // Get viewer
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        // only show polls if authorized
        $resource = ( $poll ? $poll : 'poll' );
        $viewer = ( $viewer && $viewer->getIdentity() ? $viewer : null );
        if( !$this->_helper->requireAuth()->setAuthParams($resource, $viewer, 'view')->isValid() ) {
            return;
        }
    }

    public function closeAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;

        $viewer = Engine_Api::_()->user()->getViewer();
        $poll = Engine_Api::_()->getItem('poll', $this->_getParam('poll_id'));
        if( !Engine_Api::_()->core()->hasSubject('poll') ) {
            Engine_Api::_()->core()->setSubject($poll);
        }
        $this->view->poll = $poll;

        // Check auth
        if( !$this->_helper->requireSubject()->isValid() ) {
            return;
        }
        if( !$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid() ) {
            return;
        }

        // @todo convert this to post only

        $table = $poll->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $poll->closed = (bool) $this->_getParam('closed');
            $poll->save();

            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        if( !($returnUrl = $this->_getParam('return_url')) ) {
            return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'poll_general', true);
        } else {
            return $this->_redirect($returnUrl, array('prependBase' => false));
        }
    }

    public function deleteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
        if( !$this->_helper->requireAuth()->setAuthParams($poll, null, 'delete')->isValid()) return;

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        $this->view->form = $form = new Poll_Form_Delete();

        if( !$poll ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
            return;
        }

        if( !$this->getRequest()->isPost() ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $db = $poll->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $poll->delete();

            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your poll has been deleted.');
        return $this->_forward('success' ,'utility', 'core', array(
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'poll_general', true),
            'messages' => Array($this->view->message)
        ));
    }

    public function editAction()
    {
        // Check auth
        if( !$this->_helper->requireUser()->isValid() ) {
            return;
        }
        if( !$this->_helper->requireSubject()->isValid() ) {
            return;
        }
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) {
            return;
        }

        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('poll_main');

        // Setup
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->poll = $poll = Engine_Api::_()->core()->getSubject('poll');
        $parent_type = $poll->parent_type;
        $parent_id = $poll->parent_id;
        
        $this->view->category_id = (isset($poll->category_id) && $poll->category_id != 0) ? $poll->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
        $this->view->subcat_id = (isset($poll->subcat_id) && $poll->subcat_id != 0) ? $poll->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
        $this->view->subsubcat_id = (isset($poll->subsubcat_id) && $poll->subsubcat_id != 0) ? $poll->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);
        
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'poll') ) {
                return;
            }
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }
        
        // Get form
        $this->view->form = $form = new Poll_Form_Edit(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id
        ));
        $form->removeElement('title');
        $form->removeElement('description');
        $form->removeElement('options');

        // Prepare privacy
        $auth = Engine_Api::_()->authorization()->context;
        
        if($parent_type == 'user') {
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        } else if($parent_type = 'group') {
            if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
              $roles = array('owner', 'member', 'parent_member');
            } else {
              $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            }
        }

        // Populate form with current settings
        $form->search->setValue($poll->search);

        $form->populate(array(
            'networks' => explode(',', $poll->networks),
        ));
        $form->populate($poll->toArray());
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('poll', Engine_Api::_()->user()->getViewer(), 'allow_network') && $poll->networks)
            $form->networks->setValue(explode(',', $poll->networks));

        foreach( $roles as $role ) {
            if( 1 === $auth->isAllowed($poll, $role, 'view') ) {
                $form->auth_view->setValue($role);
            }
            if( 1 === $auth->isAllowed($poll, $role, 'comment') ) {
                $form->auth_comment->setValue($role);
            }
        }

        // Check method/valid
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

            // CREATE AUTH STUFF HERE
            if( empty($values['auth_view']) ) {
                $values['auth_view'] = 'everyone';
            }
            if( empty($values['auth_comment']) ) {
                $values['auth_comment'] = 'everyone';
            }

            if( $parent_type == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
            }

            $poll->search = (bool) $values['search'];
            $poll->view_privacy = $values['auth_view'];
            $poll->networks = $values['networks'];
            $poll->setFromArray($values);
            $poll->save();
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $poll, 'edit');

            // Add photo
            if( !empty($values['photo']) ) {
                $poll->setPhoto($form->photo);
            }

            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach( $actionTable->getActionsByObject($poll) as $action ) {
                $action->privacy = isset($values['networks'])? $network_privacy : null;
                $action->save();
                $actionTable->resetActivityBindings($action);
            }
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'poll_general',true)));die;
        //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'poll_general', true);
    }

    public function viewAction()
    {
        // Check auth
        if( !$this->_helper->requireSubject('poll')->isValid() ) {
            return;
        }
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) {
            return;
        }

        $this->view->poll = $poll = Engine_Api::_()->core()->getSubject('poll');
        $this->view->owner = $owner = $poll->getOwner();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->pollOptions = $poll->getOptions();
        $this->view->hasVoted = $poll->viewerVoted();
        $this->view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showpiechart', false);
        $this->view->canVote = $poll->authorization()->isAllowed(null, 'vote');
        $this->view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);
        $this->view->canDelete = Engine_Api::_()->authorization()->isAllowed(null, null, 'delete');
        $this->view->canEdit = Engine_Api::_()->authorization()->isAllowed(null, null, 'edit');
        
        if($poll->parent_type == 'group' && $poll->parent_id) {
          $group = Engine_Api::_()->getItem($poll->parent_type, $poll->parent_id);

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

        if( !$poll || !$poll->getIdentity() || ((!$poll->approved) && !$poll->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
        $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($poll, 'user_id');
        if(empty($networkPrivacy))
            return $this->_forward('requireauth', 'error', 'core');
        }

        if( !$owner->isSelf($viewer) ) {
            $poll->view_count++;
            $poll->save();
        }
        $this->view->viewer_id = $viewer->getIdentity();
        $this->view->rating_count = Engine_Api::_()->getDbTable('ratings', 'poll')->ratingCount($poll->getIdentity());
        $this->view->rated = Engine_Api::_()->getDbTable('ratings', 'poll')->checkRated($poll->getIdentity(), $viewer->getIdentity());
        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function voteAction()
    {
        // Check auth
        if( !$this->_helper->requireUser()->isValid() ) {
            return;
        }
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) {
            return;
        }

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        $option_id = $this->_getParam('option_id');
        $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);

        $poll = Engine_Api::_()->core()->getSubject('poll');
        $viewer = Engine_Api::_()->user()->getViewer();

        if( !$poll ) {
            $this->view->success = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('This poll does not seem to exist anymore.');
            return;
        }

        $hashElement = $this->view->voteHash($poll)->getElement();
        if (!$hashElement->isValid($this->_getParam('token'))) {
            $this->view->success = false;
            $this->view->error = join(';', $hashElement->getMessages());
            return;
        }

        if( $poll->closed ) {
            $this->view->success = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('This poll is closed.');
            return;
        }

        if( $poll->hasVoted($viewer) && !$canChangeVote ) {
            $this->view->success = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('You have already voted on this poll, and are not permitted to change your vote.');
            return;
        }

        $db = Engine_Api::_()->getDbtable('polls', 'poll')->getAdapter();
        $db->beginTransaction();
        try {
            $poll->vote($viewer, $option_id);

            $db->commit();
        } catch( Exception $e ) {
            $db->rollback();
            $this->view->success = false;
            throw $e;
        }

        $this->view->token = $this->view->voteHash($poll)->generateHash();
        $this->view->success = true;
        $pollOptions = array();
        foreach( $poll->getOptions()->toArray() as $option ) {
            $option['votesTranslated'] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
            $pollOptions[] = $option;
        }
        $this->view->pollOptions = $pollOptions;
        $this->view->votes_total = $poll->vote_count;
    }
}
