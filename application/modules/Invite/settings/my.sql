
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: my.sql 10194 2014-05-01 17:41:40Z mfeineman $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_invites`
--

DROP TABLE IF EXISTS `engine4_invites`;
CREATE TABLE IF NOT EXISTS `engine4_invites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_request` INT NOT NULL,
  `timestamp` datetime NOT NULL,
  `message` text NOT NULL,
  `new_user_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `code` (`code`),
  KEY `user_id` (`user_id`),
  KEY `recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('invite', 'invite', '[host],[email],[sender_email],[sender_title],[sender_link],[sender_photo],[message],[object_link],[code]'),
('invite_code', 'invite', '[host],[email],[sender_email],[sender_title],[sender_link],[sender_photo],[message],[object_link],[code]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_invite', 'invite', 'Invite', 'Invite_Plugin_Menus::canInvite', '{"route":"default","module":"invite","icon":"fa fa-envelope"}', 'core_main', '', 1),
('user_home_invite', 'invite', 'Invite Your Friends', 'Invite_Plugin_Menus::canInvite', '{"route":"default","module":"invite"}', 'user_home', '', 5),

('core_admin_main_manage_invites', 'user', 'Manage Invites', '', '{"route":"admin_default","module":"invite","controller":"settings"}', 'core_admin_main_manage', '', 7),

('user_settings_invites', 'invite', 'Manage Invites / Referrals', 'Invite_Plugin_Menus', '{"route":"default", "module":"invite", "controller":"index", "action":"index", "icon":"fas fa-envelope-open"}', 'user_settings', '', 3);

UPDATE `engine4_core_menuitems` SET `enabled` = '0' WHERE `engine4_core_menuitems`.`name` = 'core_main_invite';
-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('invite', 'Invites', 'Invites', '4.8.7', 1, 'standard');	


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('invite.allowCustomMessage', '1'),
('invite.fromEmail', ''),
('invite.fromName', ''),
('invite.max', '10'),
('invite.message', 'You are being invited to join our social network.'),
('invite.subject', 'Join Us');


INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("invite_notify_admin", "invite", '{item:$subject} has requested to {var:$adminsidelink} the invite request for {var:$recipientemail}.', 0, "", 1);


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES 
('invite_admin_settings', 'invite', 'Invite Settings', '', '{"route":"admin_default","module":"invite","controller":"settings"}', 'core_admin_main_manage_invites', '', 1),
('invite_admin_manage', 'invite', 'Manage Invites', '', '{"route":"admin_default","module":"invite","controller":"manage"}', 'core_admin_main_manage_invites', '', 2);

ALTER TABLE `engine4_invites` ADD `import_method` VARCHAR(64) NOT NULL DEFAULT 'Invite';

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('invite.socialmediaoptions', 'a:3:{i:0;s:8:"facebook";i:1;s:3:"csv";i:2;s:11:"emailinvite";}');
