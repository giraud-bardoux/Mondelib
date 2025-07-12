
INSERT IGNORE INTO `engine4_authorization_permissions`
SELECT
  level_id as `level_id`,
  'user' as `type`,
  'pokeAction' as `name`,
  5 as `value`,
  '["owner_network","registered","network","member","owner"]' as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

UPDATE `engine4_core_menuitems` SET `plugin` = 'User_Plugin_Menus' WHERE `engine4_core_menuitems`.`name` = "user_settings_network";

UPDATE `engine4_core_menuitems` SET `label` = 'X Integration' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_twitter';
UPDATE `engine4_user_fields_meta` SET `label` = 'X' WHERE `engine4_user_fields_meta`.`type` = 'twitter';

INSERT IGNORE INTO `engine4_user_signup` (`signup_id`, `class`, `order`, `enable`) VALUES
(3, 'User_Plugin_Signup_Fields', 3, 1),
(4, 'User_Plugin_Signup_Photo', 4, 1),
(5, 'User_Plugin_Signup_Invite', 5, 0);

INSERT IGNORE INTO `engine4_user_fields_maps` (`field_id`, `option_id`, `child_id`, `order`) VALUES
(1, 1, 2, 2),
(1, 1, 3, 3),
(1, 1, 4, 4),
(1, 1, 5, 5),
(1, 1, 6, 6),
(1, 5, 13, 13),
(1, 5, 14, 14),
(1, 5, 15, 15),
(1, 5, 16, 16),
(1, 5, 17, 17),
(1, 9, 24, 24),
(1, 9, 25, 25),
(1, 9, 26, 26),
(1, 9, 27, 27),
(1, 9, 28, 28);

INSERT IGNORE INTO `engine4_user_fields_meta` (`field_id`, `type`, `label`, `description`, `alias`, `required`, `config`, `validators`, `filters`, `display`, `search`, `icon`) VALUES
(2, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(3, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(4, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(5, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(6, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt'),
(13, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(14, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(15, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(16, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(17, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt'),
(24, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(25, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(26, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(27, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(28, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt');

INSERT IGNORE INTO `engine4_user_fields_options` (`option_id`, `field_id`, `label`, `order`, `type`) VALUES
(2, 5, 'Male', 1,0),
(3, 5, 'Female', 2,0),
(4, 5, 'Other', 3,0),
(6, 16, 'Male', 6,0),
(7, 16, 'Female', 7,0),
(8, 16, 'Other', 8,0),
(10, 27, 'Male', 10,0),
(11, 27, 'Female', 11,0),
(12, 27, 'Other', 12,0);


INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');


INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');



INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("core_admin_main_telegram", "user", "Telegram Integration", "", '{"route":"admin_default","module":"user","controller":"settings","action":"telegram"}', "core_admin_main_socialmenus", "", 7);


CREATE TABLE IF NOT EXISTS `engine4_user_telegram` (
`telegram_id` int(11) NOT NULL auto_increment,
`user_id` INT(11) NOT NULL,
`telegram_uid` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
`access_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
PRIMARY KEY (`telegram_id`),
UNIQUE KEY `telegram_uid` (`telegram_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

UPDATE `engine4_user_fields_meta` SET `icon` = 'fa-brands fa-square-x-twitter' WHERE `engine4_user_fields_meta`.`type` = 'twitter';

