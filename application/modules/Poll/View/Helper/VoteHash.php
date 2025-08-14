<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Activity.php 9799 2012-10-16 22:11:00Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_View_Helper_VoteHash extends Zend_View_Helper_Abstract
{
    private $element = array();

    public function voteHash(Poll_Model_Poll $poll = null)
    {
        $this->element = new Engine_Form_Element_Hash('token_poll_' . $poll->getIdentity(), array(
          'timeout' => 3600
        ));
        return $this;
    }

    public function getElement()
    {
        return $this->element;
    }

    public function generateHash()
    {
        $this->element->initCsrfToken();
        return $this->element->getHash();
    }

}
