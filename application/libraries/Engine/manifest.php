<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
return array(
  'package' => array(
    'type' => 'library',
    'name' => 'engine',
    'version' => '7.4.0',
    'revision' => '$Revision: 10271 $',
    'path' => 'application/libraries/Engine',
    'repository' => 'socialengine.com',
    'title' => 'Engine',
    'author' => 'SocialEngine Core',
    'license' => 'http://www.socialengine.com/license/',
    'dependencies' => array(
      array(
        'type' => 'core',
        'name' => 'install',
        'required' => true,
        'minVersion' => '4.10.4',
      ),
    ),
    'directories' => array(
      'application/libraries/Engine',
    )
  )
) ?>
