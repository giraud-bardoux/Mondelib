<?php

class Install_Import_Version3_AlbumPhotoComments extends Install_Import_Version3_AbstractComments
{
  protected $_fromResourceType = 'media';

  protected $_toResourceType = 'album_photo';
}

/*
CREATE TABLE IF NOT EXISTS `se_mediacomments` (
  `mediacomment_id` int(10) unsigned NOT NULL auto_increment,
  `mediacomment_media_id` int(10) unsigned NOT NULL default '0',
  `mediacomment_authoruser_id` int(10) unsigned NOT NULL default '0',
  `mediacomment_date` int(14) NOT NULL default '0',
  `mediacomment_body` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY  (`mediacomment_id`),
  KEY `INDEX` (`mediacomment_media_id`,`mediacomment_authoruser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
 *
 */