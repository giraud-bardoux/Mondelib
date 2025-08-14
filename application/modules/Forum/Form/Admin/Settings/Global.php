<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {
  
    $this->setTitle('Global Settings')
        ->setDescription('These settings affect all members in your community.');

    $topic_length = new Engine_Form_Element_Text('topic_length');
    $topic_length->setDescription('How many posts will be shown per topic page? (Enter a number between 1 and 999)');
    $topic_length->setLabel('Posts per topic page');
    $topic_length->setValue(25);

    $forum_length = new Engine_Form_Element_Text('forum_length');    
    $forum_length->setDescription('How topics will be shown per forum page? (Enter a number between 1 and 999');
    $forum_length->setLabel('Topics per forum page');
    $forum_length->setValue(25);

    // Add elements
    $this->addElements(array(
      $topic_length, 
      $forum_length,
    ));

    // Add submit button
    $submit = new Engine_Form_Element_Button('submit');
    $submit->setAttrib('type', 'submit')->setLabel('Save Changes');
    $this->addElement($submit);
  }
}
