<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Form_Edit extends Blog_Form_Create
{
  protected $_parent_type;

  protected $_parent_id;

  public function setParent_type($value)
  {
    $this->_parent_type = $value;
  }

  public function setParent_id($value)
  {
    $this->_parent_id = $value;
  }
  
  public function init()
  {
    parent::init();
    $this->setTitle('Edit Blog Entry')
          ->setDescription('Edit your entry below, then click "Post Entry" to publish the entry on your blog.')
          ->setAttrib('class', 'global_form form_submit_ajax');
    $this->submit->setLabel('Save Changes');
  }
}
