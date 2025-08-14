<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription('FORUM_FORM_ADMIN_LEVEL_DESCRIPTION');

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Forums?',
      'description' => 'FORUM_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
      'value' => 1,
      'multiOptions' => array(
        2 => 'Yes, allow viewing and subscription of forums, even private ones.',
        1 => 'Yes, allow viewing and subscription of forums.',
        0 => 'No, do not allow forums to be viewed or subscribed to.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if( !$this->isModerator() ) {
      unset($this->view->options[2]);
    }
    
    if( !$this->isPublic() ) {

      // Element: topic_create
      $this->addElement('Radio', 'topic_create', array(
        'label' => 'Allow Creation of Topics?',
        'description' => 'FORUM_FORM_ADMIN_LEVEL_TOPIC_CREATE_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow creation of topics in forums, even private ones.',
          1 => 'Yes, allow creation of topics.',
          0 => 'No, do not allow topics to be created.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->topic_create->options[2]);
      }

      // Element: topic_edit
      $this->addElement('Radio', 'topic_edit', array(
        'label' => 'Allow Editing of Topics?',
        'description' => 'FORUM_FORM_ADMIN_LEVEL_TOPIC_EDIT_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow editing of topics in forums, including other members\' topics.',
          1 => 'Yes, allow editing of topics.',
          0 => 'No, do not allow topics to be edited.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->topic_edit->options[2]);
      }

      // Element: topic_edit
      $this->addElement('Radio', 'topic_delete', array(
        'label' => 'Allow Deletion of Topics?',
        'description' => 'FORUM_FORM_ADMIN_LEVEL_TOPIC_DELETE_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow deletion of topics in forums, including other members\' topics.',
          1 => 'Yes, allow deletion of topics.',
          0 => 'No, do not allow topics to be deleted.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->topic_delete->options[2]);
      }

      // Element: post_create
      $this->addElement('Radio', 'post_create', array(
        'label' => 'Allow Posting?',
        'description' => 'FORUM_FORM_ADMIN_LEVEL_POST_CREATE_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow posting to forums, even private ones.',
          1 => 'Yes, allow posting to forums.',
          0 => 'No, do not allow forum posts.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->post_create->options[2]);
      }

      // Element: post_edit
      $this->addElement('Radio', 'post_edit', array(
        'label' => 'Allow Editing of Posts?',
        'description' => 'FORUM_FORM_ADMIN_LEVEL_POST_EDIT_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow editing of posts, including other members\' posts.',
          1 => 'Yes, allow editing of posts.',
          0 => 'No, do not allow forum posts to be edited.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->post_edit->options[2]);
      }

      // Element: post_edit
      $this->addElement('Radio', 'post_delete', array(
        'label' => 'Allow Deletion of Posts?',
        'description' => 'Do you want to allow users to delete posts in forums?',
        'multiOptions' => array(
          2 => 'Yes, allow deletion of posts, including other members\' posts.',
          1 => 'Yes, allow deletion of posts.',
          0 => 'No, do not allow forum posts to be deleted.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->post_delete->options[2]);
      }
      
      //Element: approve
      $this->addElement('Radio', 'approve', array(
        'description' => 'Do you want topics created by members of this level to be auto-approved?',
        'label' => 'Auto Approve Topics',
        'multiOptions' => array(
          1 => 'Yes, auto-approve topics.',
          0 => 'No, do not auto-approve topics.'
        ),
        'value' => 1,
      ));
      
      //Element: approve
      $this->addElement('Radio', 'post_approve', array(
        'description' => 'Do you want posts created by members of this level to be auto-approved?',
        'label' => 'Auto Approve Posts',
        'multiOptions' => array(
          1 => 'Yes, auto-approve posts.',
          0 => 'No, do not auto-approve posts.'
        ),
        'value' => 1,
      ));

        $this->addElement('FloodControl', 'topic_flood', array(
            'label' => 'Maximum Allowed Forum Topics per Duration',
            'description' => 'Enter the maximum number of topics allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

        $this->addElement('FloodControl', 'post_flood', array(
            'label' => 'Maximum Allowed Topic Posts per Duration',
            'description' => 'Enter the maximum number of posts allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

    }
    
  }

}
