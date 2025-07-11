<?php
/**
 * @package     Engine_Core
 * @version     $Id: index.php 9764 2012-08-17 00:04:31Z matthew $
 * @copyright   Copyright (c) 2006-2020 Webligo Developments
 * @license     http://www.socialengine.com/license/
 */

// Check version
if (version_compare(phpversion(), '8.0', '<')) {
    printf('PHP 8.0 is required, you have %s', phpversion());
    exit(1);
}
// enable development log
defined('_ENGINE_ENABLE_LOG') || define('_ENGINE_ENABLE_LOG', false);

// Constants
define('_ENGINE_R_BASE', dirname($_SERVER['SCRIPT_NAME']));
define('_ENGINE_R_FILE', $_SERVER['SCRIPT_NAME']);

include dirname(__FILE__)
    . DIRECTORY_SEPARATOR.
    'boot.php';

$boot = new Engine_Boot(dirname(__FILE__));
$boot->boot();
