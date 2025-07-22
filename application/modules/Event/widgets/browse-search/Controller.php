<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9989 2013-03-20 01:13:58Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Widget_BrowseSearchController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $p = $request->getParams();
    
    $filter = !empty($p['filter']) ? $p['filter'] : 'future';
    if( $filter != 'past' && $filter != 'future' ) $filter = 'future';
    $this->view->filter = $filter;

    // Create form
    if ( $request->getModuleName() == 'event' && $request->getControllerName() == 'index' && $request->getActionName() == 'manage' ) {
      $this->view->form = $formFilter = new Event_Form_Filter_Manage();
      $defaultValues = $formFilter->getValues();
    } else {
      $this->view->form = $formFilter = new Event_Form_Filter_Browse();
      $defaultValues = $formFilter->getValues();
      
      if( !$viewer || !$viewer->getIdentity() ) {
        $formFilter->removeElement('view');
      }
    }


    // Populate form data
    $formValues = array_merge($defaultValues, $p);
    if( $formFilter->isValid($formValues) ) {
      $this->view->formValues = $values = $formFilter->getValues();
    } else {
      $formFilter->populate($defaultValues);
      $this->view->formValues = $values = array();
    }

    // Prepare data
    $this->view->formValues = $values = $formFilter->getValues();

    if( $formFilter instanceof Event_Form_Filter_Browse ) {
      if( $viewer->getIdentity() && @$values['view'] == 1 ) {
        $values['users'] = array();
        foreach( $viewer->membership()->getMembersInfo(true) as $memberinfo ) {
          $values['users'][] = $memberinfo->user_id;
        }
      }

      $values['search'] = 1;

      if( $filter == "past" ) {
        $values['past'] = 1;
      } else {
        $values['future'] = 1;
      }

       // check to see if request is for specific user's listings
      if( ($user_id = $this->_getParam('user')) ) {
        $values['user_id'] = $user_id;
      }
    }
  }
}
