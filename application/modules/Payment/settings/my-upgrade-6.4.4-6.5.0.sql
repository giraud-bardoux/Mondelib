INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_transaction', 'payment', 'Transaction History', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"transaction"}', 'user_settings_payment', '', 20),
('user_settings_verification', 'user', 'Verification', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"verification"}', 'user_settings', '', 10);

ALTER TABLE `engine4_payment_transactions` ADD `expiration_date` DATETIME NULL;

DROP TABLE IF EXISTS `engine4_payment_verifications`;
CREATE TABLE IF NOT EXISTS `engine4_payment_verifications` (
	`verification_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) DEFAULT NULL,
	`transaction_id` int(11) NOT NULL default "0",
	`params` VARCHAR(255) NULL,
	PRIMARY KEY (`verification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("payment_manual_verification", "payment", '{item:$subject} has made {var:$payment_method} payment for user verification. You can take appropriate action in {var:$adminsidelink}.', 0, "");

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_verification_active', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_terms],[object_link]'),
('payment_verification_cancelled', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_verification_pending', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_terms],[object_link]'),
('payment_verification_overdue', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_terms],[object_link]'),
('payment_verification_refunded', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_terms],[object_link]'),
('payment_verification_expired', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_terms],[object_link]'),
('payment_verification_recurrence', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_manual_verification', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[payment_method],[admin_link]');
