<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Privacy.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Privacy extends Engine_Form
{
  public    $saveSuccessful  = FALSE;
  protected $_roles           = array('owner', 'member', 'network', 'owner_network','registered', 'everyone');
  protected $_item;

  public function setItem(User_Model_User $item)
  {
    $this->_item = $item;
  }

  public function getItem()
  {
    if( null === $this->_item ) {
      throw new User_Model_Exception('No item set in ' . get_class($this));
    }

    return $this->_item;
  }
  
  public function init()
  {
    $auth = Engine_Api::_()->authorization()->context;
    $user = $this->getItem();
    $viewer = Engine_Api::_()->user()->getViewer();


    $this->setTitle('Privacy Settings')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setAttrib('class', 'global_form form_submit_ajax')
      ;

    // Init blocklist
    $this->addElement('Hidden', 'blockList', array(
      'label' => 'Blocked Members',
      'description' => 'Adding a person to your block list makes your profile (and all of your other content) unviewable to them and vice-versa. Blocked users will not be able to message you or view things you post. Any connections you have to the blocked person will be canceled. To add someone to your block list, visit that person\'s profile page.',
      'order' => -1
    ));
    Engine_Form::addDefaultDecorators($this->blockList);
    
    // Init search
    $this->addElement('Checkbox', 'search', array(
      'label' => 'Do not display me in searches, browsing members, or the "Online Members" list.',
      'checkedValue' => 0,
      'uncheckedValue' => 1,
    ));
    
    $availableLabels = array(
      'owner'       => 'Only Me',
      'member'      => 'Only My Friends',
      'network'     => 'Friends or Networks',
      'registered'  => 'All Registered Members',
      'everyone'    => 'Everyone',
    );
    
    // Init profile view
    $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_view');
    $view_options = array_intersect_key($availableLabels, array_flip($view_options));

    $this->addElement('Select', 'privacy', array(
      'label' => 'Profile Privacy',
      'description' => 'Who can view your profile?',
      'multiOptions' => $view_options,
    ));

    foreach( $this->_roles as $role ) {
      if( 1 === $auth->isAllowed($user, $role, 'view') ) {
        $this->privacy->setValue($role);
      }
    }

    $availableLabelsComment = array(
      'owner'       => 'Only Me',
      'member'      => 'Only My Friends',
      'network'     => 'Friends or Networks',
      'registered'  => 'All Registered Members',
    );

    // Init profile comment
    $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_comment');
    $commentOptions = array_intersect_key($availableLabelsComment, array_flip($commentOptions));

    $this->addElement('Select', 'comment', array(
      'label' => 'Profile Posting Privacy',
      'description' => 'Who can post on your profile?',
      'multiOptions' => $commentOptions,
    ));

    $commentRoles = array_intersect($this->_roles, array_flip($commentOptions));
    foreach( $commentRoles as $role ) {
      if( 1 === $auth->isAllowed($user, $role, 'comment') ) {
        $this->comment->setValue($role);
      }
    }

    $availableOptions = array(
        'owner'       => 'Only Me',
        'member'      => 'My Friends',
        'network'    => 'Friends or Networks',
        'owner_network' => 'Network',
        'registered'  => 'All Registered Members',
    );
    $userMention_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'mention');
    $userMention_options = array_intersect_key($availableOptions, array_flip($userMention_options));
   
    $this->addElement('Select', 'mention', array(
      'label' => 'User @ Mentions',
      'description' => 'Who can @ mention you?',
      'multiOptions' => $userMention_options,
    ));
    foreach ($this->_roles as $role) {
      if (1 === $auth->isAllowed($user, $role, 'mention')) {
        $this->mention->setValue($role);
      }
    }
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poke')) {
      //Poke Privacy
      $userPoke_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'pokeAction');
      $userPoke_options = array_intersect_key($availableOptions, array_flip($userPoke_options));

      $this->addElement('Select', 'pokeAction', array(
        'label' => 'Poke Privacy',
        'description' => 'Who can Poke or Send Action to you?',
        'multiOptions' => $userPoke_options,
      ));
      foreach ($this->_roles as $role) {
        if (1 === $auth->isAllowed($user, $role, 'pokeAction')) {
          $this->pokeAction->setValue($role);
        }
      }
    }
    
    $birthdayOptions = array(
      'monthday' => 'Month/Day',
      'monthdayyear' => 'Month/Day/Year',
    );
    $birthday_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'birthday_options');

    if (engine_count($birthday_options) > 1) {
      $birthday_options = array_intersect_key($birthdayOptions, array_flip($birthday_options));
      $this->addElement('Select', 'birthday_format', array(
        'label' => 'Birthday Privacy Setting',
        'description' => 'How to show your Birthday?',
        'multiOptions' => $birthday_options,
      ));
    }

    //Follow
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('core.followenable', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.allowuserverfication', 0)) {
      $this->addElement('Select', 'follow_verification', array(
        'label' => 'Enable Follow Verification',
        'description' => 'Do you want to enable follow verification?',
        'multiOptions' => array(
          '1' => 'Yes, enable verification',
          '0' => "No, enable auto follow",
        ),
      ));
    }
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
    
    return $this;
  }

  public function save()
  {
    $auth = Engine_Api::_()->authorization()->context;
    $user = $this->getItem();

    // Process member profile viewing privacy
    $privacy_value = $this->getValue('privacy');

    if( empty($privacy_value) ) {
      $privacy_setting = end(Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_view'));
      // If admin did not choose any options, make it everyone.
      // If not, use the one option they have set since the only option may not aways be set to 'everyone'.
      $privacy_value = empty($privacy_setting)
                     ? 'everyone'
                     : $privacy_setting;
    }

    $privacy_max_role = array_search($privacy_value, $this->_roles);
    foreach( $this->_roles as $i => $role )
      $auth->setAllowed($user, $role, 'view', ($i <= $privacy_max_role) );
    
    // Process member profile commenting privacy
    $comment_value = $this->getValue('comment');
    if( empty($comment_value) ) {
      $comment_setting = end(Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_comment'));
      $comment_value = empty($comment_setting)
                     ? 'registered'
                     : $comment_setting;
    }

    $comment_max_role = array_search($comment_value, $this->_roles);
    foreach( $this->_roles as $i => $role )
      $auth->setAllowed($user, $role, 'comment', ($i <= $comment_max_role) );

    // Process member mention privacy
    $mention_value = $this->getValue('mention');
    if( empty($mention_value) ) {
      $mention_setting = end(Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'mention'));
      $mention_value = empty($mention_setting)
                     ? 'registered'
                     : $mention_setting;
    }

    $mention_max_role = array_search($mention_value, $this->_roles);
    foreach( $this->_roles as $i => $role )
      $auth->setAllowed($user, $role, 'mention', ($i <= $mention_max_role) );
    
    // Process member poke privacy
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poke')) {
      $poke_action_value = $this->getValue('pokeAction');
      if( empty($poke_action_value) ) {
        $poke_action_setting = end(Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'pokeAction'));
        $poke_action_value = empty($poke_action_setting) ? 'registered' : $poke_action_setting;
      }

      $poke_max_role = array_search($poke_action_value, $this->_roles);
      foreach( $this->_roles as $i => $role )
        $auth->setAllowed($user, $role, 'pokeAction', ($i <= $poke_max_role) );
    }
  }
}
