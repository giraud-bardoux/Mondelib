<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Widget_GutterSearchController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Only blog or user as subject
    if( Engine_Api::_()->core()->hasSubject('blog') ) {
      $this->view->blog = $blog = Engine_Api::_()->core()->getSubject('blog');
      $this->view->owner = $owner = $blog->getOwner();
    } else if( Engine_Api::_()->core()->hasSubject('user') ) {
      $this->view->blog = null;
      $this->view->owner = $owner = Engine_Api::_()->core()->getSubject('user');
    } else {
      return $this->setNoRender();
    }

    // Prepare data
    $blogTable = Engine_Api::_()->getDbtable('blogs', 'blog');

    // Make form
    $this->view->form = $form = new Blog_Form_Search();
    $form->removeElement('show');

    // Process form
    $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $form->isValid($p);
    $values = $form->getValues();
    $this->view->formValues = array_filter($values);
    $values['user_id'] = $owner->getIdentity();
    $values['draft'] = "0";
    $values['visible'] = "1";
    $this->view->assign($values);
    
    // Other stuff
    $this->view->archiveList = $blogTable->getArchiveList($owner);
    $this->view->userTags = Engine_Api::_()->getDbtable('tags', 'core')->getTagsByTagger('blog', $owner);
    $this->view->userCategories = Engine_Api::_()->getDbtable('categories', 'blog')
        ->getUserCategoriesAssoc($owner->getIdentity());
  }
}
