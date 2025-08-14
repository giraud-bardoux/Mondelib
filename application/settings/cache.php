<?php
defined('_ENGINE') or die('Access Denied');
return array (
  'default_backend' => 'File',
  'frontend' => 
  array (
    'core' => 
    array (
      'automatic_serialization' => true,
      'cache_id_prefix' => 'Engine4_207943750_',
      'lifetime' => '86400',
      'caching' => false,
      'gzip' => true,
    ),
  ),
  'backend' => 
  array (
    'File' => 
    array (
      'file_locking' => false,
      'cache_dir' => '/home/iwekyxd/mondelibertin/temporary/cache',
    ),
  ),
  'default_file_path' => '/home/iwekyxd/mondelibertin/temporary/cache',
); ?>