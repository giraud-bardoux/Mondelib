<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Form_Admin_Widget_HomePoll extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();

    // Set form attributes
    $this
      ->setTitle('Home Poll')
      ->setDescription('Please choose a poll.')
      ;
    
    // Element: poll_id
    $this->addElement('Hidden', 'poll_id', array(
      'allowEmpty' => false,
      'required' => true,
    ));
  }
}
