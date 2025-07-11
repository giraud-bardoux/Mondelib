DROP TABLE IF EXISTS `engine4_payment_wallets`;
CREATE TABLE IF NOT EXISTS `engine4_payment_wallets` (
	`wallet_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) DEFAULT NULL,
	`transaction_id` int(11) NOT NULL default "0",
	`params` VARCHAR(255) NULL,
	PRIMARY KEY (`wallet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `is_admin`) VALUES
("payment_wallet_active", "payment", 'Your {var:$walletlink} has been successfully recharged using {var:$payment_method}.', 0, "", "0");

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_wallet_active', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[wallet_terms],[object_link]'),
('payment_wallet_pending', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[wallet_terms],[object_link]'),
('payment_manual_wallet', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[payment_method],[admin_link]'),
('payment_wallet_cancelled', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[wallet_title],[wallet_description],[object_link]'),
('payment_verification_transaction', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[gateway_type],[attechment]');

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('payment.enablewallet', 1);

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `is_admin`) VALUES
("payment_manual_wallet", "payment", '{item:$subject} has made {var:$payment_method} payment for wallet recharge. You can take appropriate action in {var:$adminsidelink}.', 0, "", "1");

ALTER TABLE `engine4_payment_subscriptions` ADD `order_id` INT(11) NOT NULL DEFAULT '0', ADD `resource_id` INT(11) NOT NULL DEFAULT '0', ADD `resource_type` VARCHAR(64) NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_transactions` ADD `subscription_id` INT(11) NOT NULL DEFAULT '0';


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_packages`
--

DROP TABLE IF EXISTS `engine4_payment_verificationpackages`;
CREATE TABLE IF NOT EXISTS `engine4_payment_verificationpackages` (
  `verificationpackage_id` int(10) unsigned NOT NULL auto_increment,
  `level_id` int(10) unsigned NOT NULL,
  `price` decimal(16,2) unsigned NOT NULL,
  `recurrence` int(11) unsigned NOT NULL,
  `recurrence_type` enum('day','week','month','year','forever') NOT NULL,
  `duration` int(11) unsigned NOT NULL,
  `duration_type` enum('day','week','month','year','forever') NOT NULL,
  `verified_icon` VARCHAR(255) NULL DEFAULT NULL,
  `verified_tiptext` VARCHAR(255) NULL DEFAULT NULL,
  `verified` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`verificationpackage_id`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_payment_verificationpackages`
  SELECT
  	NULL as `verificationpackage_id`,
    level_id as `level_id`,
    1 as `price`,
    0 as `recurrence`,
    'forever' as `recurrence_type`,
    0 as `duration`,
    'forever' as `duration_type`,
    NULL as `verified_icon`,
    'Verified' as `verified_tiptext`,
    0 as `verified`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- DELETE FROM engine4_user_signup WHERE `engine4_user_signup`.`class` = "Payment_Plugin_Signup_Subscription";

DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_mini_currency";

UPDATE `engine4_core_tasks` SET `timeout` = '3600' WHERE `engine4_core_tasks`.`plugin` = 'Payment_Plugin_Task_Cleanup';