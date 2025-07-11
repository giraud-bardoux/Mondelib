UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"general", "icon":"fas fa-cog"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_general';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"privacy", "icon":"fas fa-lock"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_privacy';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"notifications", "icon":" fas fa-bell"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_notifications';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"emails", "icon":"fas fa-envelope"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_emails';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"password", "icon":"fas fa-key"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_password';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"delete", "icon":"fas fa-user-times"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_delete';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"settings","action":"network", "icon":"fas fa-flag"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_network';


UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"edit","action":"profile","icon":"fas fa-id-card-alt"}' WHERE `engine4_core_menuitems`.`name` = 'user_edit_profile';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"edit","action":"photo","icon":"fas fa-user-edit"}' WHERE `engine4_core_menuitems`.`name` = 'user_edit_photo';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"edit","action":"style","icon":"fas fa-user-tie"}' WHERE `engine4_core_menuitems`.`name` = 'user_edit_style';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"user_extended","module":"user","controller":"edit","action":"profile-photos","icon":"fas fa-trash-alt"}' WHERE `engine4_core_menuitems`.`name` = 'user_delete_photos';
INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `is_admin`) VALUES
("content_waitingapprovalforadmin", "user", '{item:$subject} has created a new {var:$content_text} {item:$object} and this is waiting for admin approval.', 0, "", 1),
("content_resubmitwaitapprforadmin", "user", '{item:$subject} has submitted again {var:$content_text} {item:$object} and is waiting for admin approval.', 0, "", 1),
("content_waitingapprovalforowner", "user", 'Your {var:$content_text} {item:$object} has been created and is waiting for admin approval.', 0, "", 0),
("content_approvedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been approved.', 0, "", 0),
("content_disapprovedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been disapproved.', 0, "", 0),
("content_rejectedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been rejected.', 0, "", 0),
("content_ticketcreate", "user", '{item:$subject} created a new support ticket {var:$admin_ticket_link}.', 0, "", 1),
("content_ticketreply", "user", 'You have received a reply on your support ticket {var:$ticket_link}.', 0, "", 0),
("content_newticketcreate", "user", 'A new support ticket {var:$ticket_link} has been created by our site admin.', 0, "", 0);

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`, `is_admin`) VALUES
("notify_content_waitingapprovalforadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[owner_title],[content_text]", 1),
("notify_content_resubmitwaitapprforadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[owner_title],[content_text]", 1),
("notify_content_waitingapprovalforowner", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_approvedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_disapprovedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_rejectedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[admin_ticket_link],[ticket_subject],[ticket_description]", 0),
("notify_content_ticketreply", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_subject],[ticket_description]", 0),
("notify_content_newticketcreate", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_id],[ticket_description]", 0),
("notify_content_ticketcreate", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_id],[ticket_description]", 1);

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticket' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticket' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
  

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticketreply' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticketreply' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_mini_friends";

ALTER TABLE `engine4_users` ADD INDEX(`is_verified`);
ALTER TABLE `engine4_user_membership` ADD INDEX(`active`);
ALTER TABLE `engine4_user_membership` ADD INDEX(`resource_approved`);
ALTER TABLE `engine4_user_membership` ADD INDEX(`user_approved`);
ALTER TABLE `engine4_users` ADD `referral_code` VARCHAR(256) NULL DEFAULT NULL, ADD `referral_count` INT(11) NOT NULL DEFAULT '0';
DELETE FROM `engine4_user_signup` WHERE `engine4_user_signup`.`class` = "User_Plugin_Signup_Invite";
ALTER TABLE `engine4_users` ADD INDEX(`referral_code`);
