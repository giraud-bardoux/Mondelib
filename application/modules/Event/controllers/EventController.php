<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: EventController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_EventController extends Core_Controller_Action_Standard
{
    public function init()
    {
        $id = $this->_getParam('event_id', $this->_getParam('id', null));
        if( $id )
        {
            $event = Engine_Api::_()->getItem('event', $id);
            if( $event )
            {
                Engine_Api::_()->core()->setSubject($event);
            }
        }
    }

    public function editAction()
    {
        $event_id = $this->getRequest()->getParam('event_id');
        $this->view->event = $event = Engine_Api::_()->getItem('event', $event_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        if( !($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)) ) {
            return;
        }

        // Create form
        $event = Engine_Api::_()->core()->getSubject();
        $this->view->form = $form = new Event_Form_Edit(array('parent_type'=>$event->parent_type, 'parent_id'=>$event->parent_id));

        $this->view->category_id = (isset($event->category_id) && $event->category_id != 0) ? $event->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
        $this->view->subcat_id = (isset($event->subcat_id) && $event->subcat_id != 0) ? $event->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
        $this->view->subsubcat_id = (isset($event->subsubcat_id) && $event->subsubcat_id != 0) ? $event->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

        // Populate with categories
        $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
        asort($categories, SORT_LOCALE_STRING);
        $categoryOptions = array('0' => '');
        foreach( $categories as $k => $v ) {
            $categoryOptions[$k] = $v;
        }
        if (sizeof($categoryOptions) <= 1) {
            $form->removeElement('category_id');
        } else {
            if($form->category_id)
                $form->category_id->setMultiOptions($categoryOptions);
        }

        if( !$this->getRequest()->isPost() ) {
            // Populate auth
            $auth = Engine_Api::_()->authorization()->context;

            if( $event->parent_type == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            foreach( $roles as $role ) {
                if( isset($form->auth_view->options[$role]) && $auth->isAllowed($event, $role, 'view') ) {
                    $form->auth_view->setValue($role);
                }
                if( isset($form->auth_comment->options[$role]) && $auth->isAllowed($event, $role, 'comment') ) {
                    $form->auth_comment->setValue($role);
                }
                if( isset($form->auth_photo->options[$role]) && $auth->isAllowed($event, $role, 'photo') ) {
                    $form->auth_photo->setValue($role);
                }
            }
            $form->auth_invite->setValue($auth->isAllowed($event, 'member', 'invite'));
            $form->populate($event->toArray());

            // Convert and re-populate times
            $start = strtotime($event->starttime);
            $end = strtotime($event->endtime);
            $oldTz = date_default_timezone_get();
            date_default_timezone_set($viewer->timezone);
            $start = date('Y-m-d H:i:s', $start);
            $end = date('Y-m-d H:i:s', $end);
            date_default_timezone_set($oldTz);

            $form->populate(array(
                'starttime' => $start,
                'endtime' => $end,
                'networks' => explode(',', $event->networks),
            ));
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }


        // Process
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

        $values['view_privacy'] =  $values['auth_view'];

        // Convert times
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($viewer->timezone);
        $start = strtotime($values['starttime']);
        $end = strtotime($values['endtime']);

        // check dates
        if( $start > $end ) {
          $errors[] = array('errorMessage' => $this->view->translate('Start Date should be before End Date.'));
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
//             $form->starttime->setErrors(array('Start Date should be before End Date.'));
//             return;
        }

        date_default_timezone_set($oldTz);
        $values['starttime'] = date('Y-m-d H:i:s', $start);
        $values['endtime'] = date('Y-m-d H:i:s', $end);

        // Check parent
        if( !isset($values['host']) && $event->parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $group = Engine_Api::_()->getItem('group', $event->parent_id);
            $values['host']  = $group->getTitle();
        }

        // Process
        $db = Engine_Api::_()->getItemTable('event')->getAdapter();
        $db->beginTransaction();

        try
        {
            // Set event info
            $event->setFromArray($values);
            $event->save();

            //Save editor images
            Engine_Api::_()->core()->saveTinyMceImages($values['description'], $event);

            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $event, 'edit');

            if( !empty($values['photo']) ) {
                $event->setPhoto($form->photo);
            }


            // Process privacy
            $auth = Engine_Api::_()->authorization()->context;

            if( $event->parent_type == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($event, $role, 'view',    ($i <= $viewMax));
                $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'photo',   ($i <= $photoMax));
            }

            $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);

            // Commit
            $db->commit();
        }

        catch( Engine_Image_Exception $e )
        {
            $db->rollBack();
            //$form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
            $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        catch( Exception $e )
        {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }


        $db->beginTransaction();
        try {
            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach( $actionTable->getActionsByObject($event) as $action ) {
                $action->privacy = isset($values['networks'])? $network_privacy : null;
                $action->save();
                $actionTable->resetActivityBindings($action);
            }

            $db->commit();
        }
        catch( Exception $e )
        {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        // Redirect
        if( $this->_getParam('ref') === 'profile' ) {
          echo json_encode(array('status' => true, 'redirectURL' => $event->getHref()));die;
          //$this->_redirectCustom($event);
        } else {
          echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'event_general',true)));die;
          //$this->_redirectCustom(array('route' => 'event_general', 'action' => 'manage'));
        }
    }


    public function inviteAction()
    {

        if( !$this->_helper->requireUser()->isValid() ) return;
        if( !$this->_helper->requireSubject('event')->isValid() ) return;
        // @todo auth

        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->event = $event = Engine_Api::_()->core()->getSubject();
        $this->view->friends = $friends = $viewer->membership()->getMembers();

        // Prepare form
        $this->view->form = $form = new Event_Form_Invite();

        $count = 0;
        foreach( $friends as $friend )
        {
            if( $event->membership()->isMember($friend, null) ) continue;
            $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
            $count++;
        }
        $this->view->count = $count;
        // Not posting
        if( !$this->getRequest()->isPost() )
        {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) )
        {
            return;
        }

        // Process
        $table = $event->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try
        {
            $usersIds = $form->getValue('users');

            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            foreach( $friends as $friend )
            {
                if( !engine_in_array($friend->getIdentity(), $usersIds) )
                {
                    continue;
                }

                $event->membership()->addMember($friend)
                    ->setResourceApproved($friend);

                $notifyApi->addNotification($friend, $viewer, $event, 'event_invite');
            }


            $db->commit();
        }

        catch( Exception $e )
        {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Members invited')),
            'layout' => 'default-simple',
            'parentRefresh' => true,
        ));
    }

    public function styleAction()
    {
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'style')->isValid() ) return;

        $user = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->core()->getSubject('event');

        // Make form
        $this->view->form = $form = new Event_Form_Style();

        // Get current row
        $table = Engine_Api::_()->getDbtable('styles', 'core');
        $select = $table->select()
            ->where('type = ?', 'event')
            ->where('id = ?', $event->getIdentity())
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
            $row->type = 'event';
            $row->id = $event->getIdentity();
        }

        $row->style = $style;
        $row->save();

        $this->view->draft = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.');
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => false,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'))
        ));
    }


    public function deleteAction()
    {

        $viewer = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->getItem('event', $this->getRequest()->getParam('event_id'));
        if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid()) return;

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        // Make form
        $this->view->form = $form = new Event_Form_Delete();

        if( !$event )
        {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized to delete");
            return;
        }

        if( !$this->getRequest()->isPost() )
        {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();

        try
        {
            $event->delete();
            $db->commit();
        }
        catch( Exception $e )
        {
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected event has been deleted.');
        return $this->_forward('success' ,'utility', 'core', array(
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'event_general', true),
            'messages' => Array($this->view->message)
        ));
    }








}
