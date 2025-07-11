<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Bootstrap.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Bootstrap extends Engine_Application_Bootstrap_Abstract {

	public function __construct($application) {

    parent::__construct($application);
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Harmony_Plugin_Core);
	}
}
