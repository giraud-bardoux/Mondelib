INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('user_settings_invites', 'invite', 'Manage Invites', 'Invite_Plugin_Menus', '{"route":"default", "module":"invite", "controller":"settings", "action":"manage-invites"}', 'user_settings', '', 10);

UPDATE `engine4_activity_notificationtypes` SET `body` = '{item:$subject} has requested to {var:$adminsidelink} the invite request for {var:$recipientemail}.' WHERE `engine4_activity_notificationtypes`.`type` = 'invite_notify_admin';
