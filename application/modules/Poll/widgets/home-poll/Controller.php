<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Widget_HomePollController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if( !$this->_getParam('poll_id') ) {
      return $this->setNoRender();
    }

    $poll = Engine_Api::_()->getItem('poll', $this->_getParam('poll_id'));
    if( !$poll ) {
      return $this->setNoRender();
    }

    $this->view->poll = $poll;
    if(!$poll->approved)
      return $this->setNoRender();
    $this->view->owner = $owner = $poll->getOwner();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->pollOptions = $poll->getOptions();
    $this->view->hasVoted = $poll->viewerVoted();
    $this->view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showpiechart', false);
    $this->view->canVote = $poll->authorization()->isAllowed(null, 'vote');
    $this->view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);
    $this->view->hideLinks = true;
  }

  public function adminAction()
  {
    // Check auth
    if( !Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view') ) {
      return $this->setNoRender();
    }
    
    $this->view->form = $form = new Poll_Form_Admin_Widget_HomePoll();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $this->view->values = $form->getValues();
  }
}
