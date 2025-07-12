UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default", "module":"invite", "controller":"settings", "action":"manage-invites", "icon":"fas fa-envelope-open"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_invites';
UPDATE `engine4_core_menuitems` SET `enabled` = '0' WHERE `engine4_core_menuitems`.`name` = 'core_main_invite';



-- New invite work
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"invite","controller":"settings"}' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_manage_invites';
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES 
('invite_admin_settings', 'invite', 'Invite Settings', '', '{"route":"admin_default","module":"invite","controller":"settings"}', 'core_admin_main_manage_invites', '', 1),
('invite_admin_manage', 'invite', 'Manage Invites', '', '{"route":"admin_default","module":"invite","controller":"manage"}', 'core_admin_main_manage_invites', '', 2);

ALTER TABLE `engine4_invites` DROP INDEX `code`;
ALTER TABLE `engine4_invites` ADD INDEX(`code`);

ALTER TABLE `engine4_invites` ADD `import_method` VARCHAR(64) NOT NULL DEFAULT 'invite';

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default", "module":"invite", "controller":"index", "action":"index", "icon":"fas fa-envelope-open"}' WHERE `engine4_core_menuitems`.`name` = "user_settings_invites";
UPDATE `engine4_core_menuitems` SET `label` = 'Manage Invites / Referrals' WHERE `engine4_core_menuitems`.`name` = 'user_settings_invites';
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('invite.socialmediaoptions', 'a:3:{i:0;s:8:"facebook";i:1;s:3:"csv";i:2;s:11:"emailinvite";}');
