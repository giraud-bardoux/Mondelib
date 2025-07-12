--
-- indexing for table `engine4_users`
--
ALTER TABLE `engine4_users` ADD INDEX(`level_id`);
ALTER TABLE `engine4_users` ADD INDEX(`verified`);
ALTER TABLE `engine4_users` ADD INDEX(`approved`);
ALTER TABLE `engine4_users` ADD INDEX(`modified_date`);
ALTER TABLE `engine4_users` ADD INDEX(`view_count`);
ALTER TABLE `engine4_users` ADD INDEX(`comment_count`);
ALTER TABLE `engine4_users` ADD INDEX(`like_count`);
ALTER TABLE `engine4_users` ADD INDEX(`coverphoto`);
ALTER TABLE `engine4_users` ADD INDEX(`disable_email`);

--
-- indexing for table `engine4_user_logins`
--
ALTER TABLE `engine4_user_logins` ADD INDEX(`state`);
ALTER TABLE `engine4_user_logins` ADD INDEX(`active`);

--
-- indexing for table `engine4_user_signup
--
ALTER TABLE `engine4_user_signup` ADD INDEX(`order`);
ALTER TABLE `engine4_user_signup` ADD INDEX(`enable`);

--
-- Add Other in Gender Field on upgrade
--
INSERT IGNORE INTO `engine4_user_fields_options`
  SELECT
    null as `option_id`,
    field_id as `field_id`,
    'Other' as `label`,
    '999' as `order`
  FROM `engine4_user_fields_meta` WHERE type = 'gender';

ALTER TABLE `engine4_user_fields_meta` ADD `icon` TEXT NULL DEFAULT NULL AFTER `error`;

INSERT IGNORE INTO `engine4_user_signup` (`class`, `order`, `enable`) VALUES
("User_Plugin_Signup_Otp", 20, 1);

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('user_otp', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[code]'),
('user_deleteotp', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[code]');

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_general","action":"browse","icon":"fa fa-user"}' WHERE `name` = 'core_main_user';

UPDATE `engine4_core_settings` SET `value` = 'login' WHERE `engine4_core_settings`.`name` = 'core.facebook.enable' AND `engine4_core_settings`.`value` = 'publish';

UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-user' WHERE `engine4_user_fields_meta`.`type` = 'first_name';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-user' WHERE `engine4_user_fields_meta`.`type` = 'last_name';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-venus-mars' WHERE `engine4_user_fields_meta`.`type` = 'gender';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-calendar-alt' WHERE `engine4_user_fields_meta`.`type` = 'birthdate';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-globe' WHERE `engine4_user_fields_meta`.`type` = 'website';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa-brands fa-x-twitter' WHERE `engine4_user_fields_meta`.`type` = 'twitter';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fab fa-facebook-f' WHERE `engine4_user_fields_meta`.`type` = 'facebook';
UPDATE `engine4_user_fields_meta` SET `icon` = 'fa fa-info-circle' WHERE `engine4_user_fields_meta`.`type` = 'about_me';
  
UPDATE `engine4_core_settings` SET `value` = 'login' WHERE `engine4_core_settings`.`name` = 'core.facebook.enable' AND `engine4_core_settings`.`value` = 'publish';

CREATE TABLE IF NOT EXISTS `engine4_user_codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `code` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `email` (`email`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

UPDATE `engine4_core_settings` SET `value` = 'login' WHERE `engine4_core_settings`.`name` = 'core.facebook.enable' AND `engine4_core_settings`.`value` = 'publish';

CREATE TABLE IF NOT EXISTS `engine4_user_codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `code` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `email` (`email`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

