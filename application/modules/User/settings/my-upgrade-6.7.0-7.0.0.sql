UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"payment","controller":"verification","action":"index"}' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_manage_verification";
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"payment","controller":"verification","action":"index"}' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_settings_verification";

ALTER TABLE `engine4_users` ADD `wallet_amount` FLOAT(16,2) NOT NULL DEFAULT '0.00';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_wallet', 'user', 'Wallet', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"wallet", "icon":"fa-solid fa-wallet"}', 'user_settings', '', 16);

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'viewtype' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'is_fullwidth' as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'tab' as `name`,
    3 as `value`,
    'outside' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');


UPDATE `engine4_activity_actiontypes` SET `body` = '{item:$subject} has added a new profile photo: {body:$body}' WHERE `engine4_activity_actiontypes`.`type` = 'profile_photo_update';

DROP TABLE IF EXISTS `engine4_user_recentsearch`;
CREATE TABLE IF NOT EXISTS  `engine4_user_recentsearch` (
  `recentsearch_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `user_id` INT NOT NULL ,
  `query` VARCHAR(128) NOT NULL,
  `id` INT(11) NOT NULL DEFAULT '0',
  `type` VARCHAR(128) NULL DEFAULT NULL,
  `creation_date` DATETIME NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

ALTER TABLE `engine4_user_emailsettings` CHANGE `type` `type` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;