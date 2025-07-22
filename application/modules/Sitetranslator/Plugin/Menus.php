<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9770 2012-08-30 02:36:05Z richard $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Sitetranslator_Plugin_Menus
{
	public function onMenuInitialize_SitetranslatorAdminMainMobileTranslator() {
        return Engine_Api::_()->sitetranslator()->checkSitemobileMode('tablet-mode') || Engine_Api::_()->sitetranslator()->checkSitemobileMode('mobile-mode');
    }
}