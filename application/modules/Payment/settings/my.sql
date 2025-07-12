
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: my.sql 10171 2014-04-18 19:03:41Z mfeineman $
 * @author     John Boehr <j@webligo.com>
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_gateways`
--

DROP TABLE IF EXISTS `engine4_payment_gateways`;
CREATE TABLE IF NOT EXISTS `engine4_payment_gateways` (
  `gateway_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `description` text NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `plugin` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `config` mediumblob NULL,
  `test_mode` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gateway_id`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `engine4_payment_gateways`
--

INSERT INTO `engine4_payment_gateways` (`gateway_id`, `title`, `description`, `enabled`, `plugin`, `test_mode`) VALUES
(1, 'PayPal', NULL, 0, 'Payment_Plugin_Gateway_PayPal', 0),
(4, 'Bank', NULL, 0, 'Payment_Plugin_Gateway_Bank', 0),
(5, 'Cash', NULL, 0, 'Payment_Plugin_Gateway_Cash', 0),
(6, 'Cheque', NULL, 0, 'Payment_Plugin_Gateway_Cheque', 0);

-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_orders`
--

DROP TABLE IF EXISTS `engine4_payment_orders`;
CREATE TABLE IF NOT EXISTS `engine4_payment_orders` (
  `order_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `gateway_id` int(10) unsigned NOT NULL,
  `gateway_order_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default NULL,
  `gateway_transaction_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default NULL,
  `state` enum('pending','cancelled','failed','incomplete','complete') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL default 'pending',
  `creation_date` datetime NOT NULL,
  `source_type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default NULL,
  `source_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `gateway_id` (`gateway_id`,`gateway_order_id`),
  KEY `state` (`state`),
  KEY `source_type` (`source_type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_packages`
--

DROP TABLE IF EXISTS `engine4_payment_packages`;
CREATE TABLE IF NOT EXISTS `engine4_payment_packages` (
  `package_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `level_id` int(10) unsigned NOT NULL,
  `downgrade_level_id` int(10) unsigned NOT NULL default '0',
  `price` decimal(16,2) unsigned NOT NULL,
  `recurrence` int(11) unsigned NOT NULL,
  `recurrence_type` enum('day','week','month','year','forever') NOT NULL,
  `duration` int(11) unsigned NOT NULL,
  `duration_type` enum('day','week','month','year','forever') NOT NULL,
  `trial_duration` int(11) unsigned NOT NULL default '0',
  `trial_duration_type` enum('day','week','month','year','forever') default NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '1',
  `signup` tinyint(1) unsigned NOT NULL default '1',
  `after_signup` tinyint(1) unsigned NOT NULL default '1',
  `default` tinyint(1) unsigned NOT NULL default '0',
  `extra_day` INT(8) NOT NULL,
  `reminder_email` INT(8) NOT NULL,
  `reminder_email_type` ENUM('day','week','month','year') NOT NULL,
  `send_reminder` TINYINT(1) NOT NULL DEFAULT "1",
  `order` INT(11) NOT NULL DEFAULT '0',
  `photo_id` VARCHAR(255) NULL DEFAULT NULL,
  `packagestyles` TEXT NULL DEFAULT NULL,
  `features` TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (`package_id`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `engine4_payment_products`
--

DROP TABLE IF EXISTS `engine4_payment_products`;
CREATE TABLE IF NOT EXISTS `engine4_payment_products` (
  `product_id` int(10) unsigned NOT NULL auto_increment,
  `extension_type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default NULL,
  `extension_id` int(10) unsigned default NULL,
  `sku` bigint(20) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(16,2) unsigned NOT NULL,
  PRIMARY KEY  (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `extension_type` (`extension_type`,`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_subscriptions`
--

DROP TABLE IF EXISTS `engine4_payment_subscriptions`;
CREATE TABLE IF NOT EXISTS `engine4_payment_subscriptions` (
  `subscription_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `package_id` int(11) unsigned NOT NULL,
  `main_package_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` enum('initial','trial','pending','active','cancelled','expired','overdue','refunded') NOT NULL default 'initial',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime default NULL,
  `payment_date` datetime default NULL,
  `expiration_date` datetime default NULL,
  `notes` text NULL,
  `gateway_id` int(10) unsigned default NULL,
  `gateway_profile_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default NULL,
  `email_reminder` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'This is for subscription plan is expiring reminder email.',
  PRIMARY KEY  (`subscription_id`),
  UNIQUE KEY `gateway_id` (`gateway_id`, `gateway_profile_id`),
  KEY `user_id` (`user_id`),
  KEY `package_id` (`package_id`),
  KEY `status` (`status`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_transactions`
--

DROP TABLE IF EXISTS `engine4_payment_transactions`;
CREATE TABLE IF NOT EXISTS `engine4_payment_transactions` (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `gateway_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `order_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `state` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `gateway_transaction_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_parent_transaction_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `gateway_order_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `amount` decimal(16,2) NOT NULL,
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL default '',
  `params` VARCHAR(255) NULL,
  `file_id` INT NULL,
  `expiration_date` DATETIME NULL,
  PRIMARY KEY  (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `gateway_id` (`gateway_id`),
  KEY `type` (`type`),
  KEY `state` (`state`),
  KEY `gateway_transaction_id` (`gateway_transaction_id`),
  KEY `gateway_parent_transaction_id` (`gateway_parent_transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_subscription_active', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_cancelled', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_expired', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_overdue', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_pending', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_recurrence', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_refunded', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_transaction', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[gateway_type],[attechment]'),
('payment_subscription_expiredsoon', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[plan_name],[period]');

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_subscription_changed', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[subscription_title],[subscription_description],[subscription_terms],[current_plan],[changed_plan]'),
('payment_manual_subscribe', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[payment_method],[admin_link]');


INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("payment_subscription_expiredsoon", "payment", 'Your subscription plan {var:$planName} is going to expire soon on {var:$period}.', 0, ""),
("payment_subscription_changed", "payment", 'Your subscription plan changed from {var:$currentPlan} to {var:$changedPlan}.', 0, ""),
("payment_manual_subscribe", "payment", '{item:$subject} has subscribe with payment method {var:$payment_method} on this {var:$adminsidelink}.', 0, "");
-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

/*
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('payment_admin_main_transactions', 'payment', 'Transactions', '', '{"route":"admin_default","module":"payment","controller":"transactions","action":"index"}', 'payment_admin_main', '', 1),
('payment_admin_main_settings', 'payment', 'Settings', '', '{"route":"admin_default","module":"payment","controller":"settings"}', 'payment_admin_main', '', 2),
('payment_admin_main_gateway', 'payment', 'Gateways', '', '{"route":"admin_default","module":"payment","controller":"gateway"}', 'payment_admin_main', '', 3),
('payment_admin_main_package', 'payment', 'Plans', '', '{"route":"admin_default","module":"payment","controller":"package"}', 'payment_admin_main', '', 4),
('payment_admin_main_subscription', 'payment', 'Subscriptions', '', '{"route":"admin_default","module":"payment","controller":"subscription"}', 'payment_admin_main', '', 5)
;
*/

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
-- ('core_mini_currency', 'payment', 'Currency Chooser', 'Payment_Plugin_Menus', '{"route":"default","module":"payment","icon":"fa fa-usd"}', 'core_mini', '', 8),
('user_settings_payment', 'user', 'Membership Subscription', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"index", "icon":"fas fa-money-check-alt"}', 'user_settings', '', 4),

('core_admin_main_payment', 'payment', 'Billing Settings', '', '{"route":"admin_default","module":"payment","controller":"settings","action":"index"}', 'core_admin_main_monetization', '', 7),
('core_admin_main_payment_settings', 'payment', 'Global Settings', '', '{"route":"admin_default","module":"payment","controller":"settings","action":"index"}', 'core_admin_main_payment', '', 1),
('core_admin_main_payment_gateways', 'payment', 'Gateways', '', '{"route":"admin_default","module":"payment","controller":"gateway","action":"index"}', 'core_admin_main_payment', '', 2),
("core_admin_main_payment_currency", "payment", "Manage Currency", "", '{"route":"admin_default","module":"payment","controller":"settings","action":"currency"}', "core_admin_main_payment", "", 3),

('core_admin_main_orders', 'payment', 'Orders', '', '{"route":"admin_default","module":"payment","controller":"index","action":"index"}', 'core_admin_main_monetization', '', 9),
('core_admin_main_orders_transactions', 'payment', 'Transactions', '', '{"route":"admin_default","module":"payment","controller":"index","action":"index"}', 'core_admin_main_orders', '', 1),

('core_admin_main_membership', 'payment', 'Member Subscription Settings', '', '{"route":"admin_default","module":"payment","controller":"package","action":"settings"}', 'core_admin_main_monetization', '', 6),
('core_admin_main_membership_settings', 'payment', 'Global Settings', '', '{"route":"admin_default","module":"payment","controller":"package","action":"settings"}', 'core_admin_main_membership', '', 1),
('core_admin_main_payment_packages', 'payment', 'Plans', '', '{"route":"admin_default","module":"payment","controller":"package","action":"index"}', 'core_admin_main_membership', '', 2),
('core_admin_main_payment_subscriptions', 'payment', 'Subscriptions', '', '{"route":"admin_default","module":"payment","controller":"subscription","action":"index"}', 'core_admin_main_membership', '', 3),

('user_settings_transaction', 'payment', 'Transaction History', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"transaction", "icon":"fas fa-money-check-alt"}', 'user_settings', '', 20),
('user_settings_verification', 'user', 'Verification', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"verification", "icon":"fas fa-check-circle"}', 'user_settings', '', 10);
-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('payment', 'Payment', 'Payment', '4.8.11', 1, 'standard');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('payment.benefit', 'all'),
('payment.currency', 'USD'),
('payment.secret', MD5(CONCAT(RAND(), NOW())));
/*
('payment.subscription.enabled', 0),
('payment.lapse', 'reassign'),
('payment.subscription.required', 0)
*/


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_tasks`
--

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Payment Maintenance', 'user', 'Payment_Plugin_Task_Cleanup', 3600);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_user_signup`
--

INSERT INTO `engine4_user_signup` (`class`, `order`, `enable`) VALUES
('Payment_Plugin_Signup_Subscription', 2, 0);


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
('payment_verification_recurrence', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_manual_verification', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[payment_method],[admin_link]');



DROP TABLE IF EXISTS `engine4_payment_currencies`;
CREATE TABLE IF NOT EXISTS `engine4_payment_currencies` (
  `currency_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `code` char(3) NOT NULL default '',
  `change_rate` FLOAT NOT NULL DEFAULT "1",
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `icon` VARCHAR(255) NULL,
  `symbol` char(6) NOT NULL default '',
  `seprator` char(2) NOT NULL default ',',
  `placement` char(5) NOT NULL default 'pre',
  `gateways` varchar(128) NULL,
  `order` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`currency_id`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_payment_currencies` (`currency_id`, `title`, `code`, `change_rate`, `enabled`, `icon`, `symbol`, `seprator`, `placement`, `gateways`, `order`) VALUES
(1, 'Afghan Afghani', 'AFA', 1, 0, NULL, '؋', ',', 'pre', NULL, 1),
(2, 'Albanian Lek', 'ALL', 1, 0, NULL, 'Lek', ',', 'pre', NULL, 2),
(3, 'Algerian Dinar', 'DZD', 1, 0, NULL, 'دج', ',', 'pre', NULL, 3),
(4, 'Angolan Kwanza', 'AOA', 1, 0, NULL, 'Kz', ',', 'pre', NULL, 4),
(5, 'Argentine Peso', 'ARS', 1, 0, NULL, '$', ',', 'pre', NULL, 5),
(6, 'Armenian Dram', 'AMD', 1, 0, NULL, '֏', ',', 'pre', NULL, 6),
(7, 'Aruban Florin', 'AWG', 1, 0, NULL, 'ƒ', ',', 'pre', NULL, 7),
(8, 'Australian Dollar', 'AUD', 1, 0, NULL, 'AU$', ',', 'pre', NULL, 8),
(9, 'Azerbaijani Manat', 'AZN', 1, 0, NULL, 'm', ',', 'pre', NULL, 9),
(10, 'Bahamian Dollar', 'BSD', 1, 0, NULL, 'B$', ',', 'pre', NULL, 10),
(11, 'Bahraini Dinar', 'BHD', 1, 0, NULL, '.د.ب', ',', 'pre', NULL, 11),
(12, 'Bangladeshi Taka', 'BDT', 1, 0, NULL, '৳', ',', 'pre', NULL, 12),
(13, 'Barbadian Dollar', 'BBD', 1, 0, NULL, 'Bds$', ',', 'pre', NULL, 13),
(14, 'Belarusian Ruble', 'BYR', 1, 0, NULL, 'Br', ',', 'pre', NULL, 14),
(15, 'Belgian Franc', 'BEF', 1, 0, NULL, 'fr', ',', 'pre', NULL, 15),
(16, 'Belize Dollar', 'BZD', 1, 0, NULL, '$', ',', 'pre', NULL, 16),
(17, 'Bermudan Dollar', 'BMD', 1, 0, NULL, '$', ',', 'pre', NULL, 17),
(18, 'Bhutanese Ngultrum', 'BTN', 1, 0, NULL, 'Nu.', ',', 'pre', NULL, 18),
(19, 'Bitcoin', 'BTC', 1, 0, NULL, '฿', ',', 'pre', NULL, 19),
(20, 'Bolivian Boliviano', 'BOB', 1, 0, NULL, 'Bs.', ',', 'pre', NULL, 20),
(21, 'Bosnia-Herzegovina Convertible Mark', 'BAM', 1, 0, NULL, 'KM', ',', 'pre', NULL, 21),
(22, 'Botswanan Pula', 'BWP', 1, 0, NULL, 'P', ',', 'pre', NULL, 22),
(23, 'Brazilian Real', 'BRL', 1, 0, NULL, 'R$', ',', 'pre', NULL, 23),
(24, 'British Pound Sterling', 'GBP', 1, 0, NULL, '£', ',', 'pre', NULL, 24),
(25, 'Brunei Dollar', 'BND', 1, 0, NULL, 'B$', ',', 'pre', NULL, 25),
(26, 'Bulgarian Lev', 'BGN', 1, 0, NULL, 'Лв.', ',', 'pre', NULL, 26),
(27, 'Burundian Franc', 'BIF', 1, 0, NULL, 'FBu', ',', 'pre', NULL, 27),
(28, 'Cambodian Riel', 'KHR', 1, 0, NULL, 'KHR', ',', 'pre', NULL, 28),
(29, 'Canadian Dollar', 'CAD', 1, 0, NULL, '$', ',', 'pre', NULL, 29),
(30, 'Cape Verdean Escudo', 'CVE', 1, 0, NULL, '$', ',', 'pre', NULL, 30),
(31, 'Cayman Islands Dollar', 'KYD', 1, 0, NULL, '$', ',', 'pre', NULL, 31),
(32, 'CFA Franc BCEAO', 'XOF', 1, 0, NULL, 'CFA', ',', 'pre', NULL, 32),
(33, 'CFA Franc BEAC', 'XAF', 1, 0, NULL, 'FCFA', ',', 'pre', NULL, 33),
(34, 'CFP Franc', 'XPF', 1, 0, NULL, '₣', ',', 'pre', NULL, 34),
(35, 'Chilean Peso', 'CLP', 1, 0, NULL, '$', ',', 'pre', NULL, 35),
(36, 'Chilean Unit of Account', 'CLF', 1, 0, NULL, 'CLF', ',', 'pre', NULL, 36),
(37, 'Chinese Yuan', 'CNY', 1, 0, NULL, '¥', ',', 'pre', NULL, 37),
(38, 'Colombian Peso', 'COP', 1, 0, NULL, '$', ',', 'pre', NULL, 38),
(39, 'Comorian Franc', 'KMF', 1, 0, NULL, 'CF', ',', 'pre', NULL, 39),
(40, 'Congolese Franc', 'CDF', 1, 0, NULL, 'FC', ',', 'pre', NULL, 40),
(41, 'Costa Rican Colón', 'CRC', 1, 0, NULL, '₡', ',', 'pre', NULL, 41),
(42, 'Croatian Kuna', 'HRK', 1, 0, NULL, 'kn', ',', 'pre', NULL, 42),
(43, 'Cuban Convertible Peso', 'CUC', 1, 0, NULL, '$, CUC', ',', 'pre', NULL, 43),
(44, 'Czech Republic Koruna', 'CZK', 1, 0, NULL, 'Kč', ',', 'pre', NULL, 44),
(45, 'Danish Krone', 'DKK', 1, 0, NULL, 'Kr.', ',', 'pre', NULL, 45),
(46, 'Djiboutian Franc', 'DJF', 1, 0, NULL, 'Fdj', ',', 'pre', NULL, 46),
(47, 'Dominican Peso', 'DOP', 1, 0, NULL, '$', ',', 'pre', NULL, 47),
(48, 'East Caribbean Dollar', 'XCD', 1, 0, NULL, '$', ',', 'pre', NULL, 48),
(49, 'Egyptian Pound', 'EGP', 1, 0, NULL, 'ج.م', ',', 'pre', NULL, 49),
(50, 'Eritrean Nakfa', 'ERN', 1, 0, NULL, 'Nfk', ',', 'pre', NULL, 50),
(51, 'Estonian Kroon', 'EEK', 1, 0, NULL, 'kr', ',', 'pre', NULL, 51),
(52, 'Ethiopian Birr', 'ETB', 1, 0, NULL, 'Nkf', ',', 'pre', NULL, 52),
(53, 'Euro', 'EUR', 1, 0, NULL, '€', ',', 'pre', NULL, 53),
(54, 'Falkland Islands Pound', 'FKP', 1, 0, NULL, '£', ',', 'pre', NULL, 54),
(55, 'Fijian Dollar', 'FJD', 1, 0, NULL, 'FJ$', ',', 'pre', NULL, 55),
(56, 'Gambian Dalasi', 'GMD', 1, 0, NULL, 'D', ',', 'pre', NULL, 56),
(57, 'Georgian Lari', 'GEL', 1, 0, NULL, 'ლ', ',', 'pre', NULL, 57),
(58, 'German Mark', 'DEM', 1, 0, NULL, 'DM', ',', 'pre', NULL, 58),
(59, 'Ghanaian Cedi', 'GHS', 1, 0, NULL, 'GH₵', ',', 'pre', NULL, 59),
(60, 'Gibraltar Pound', 'GIP', 1, 0, NULL, '£', ',', 'pre', NULL, 60),
(61, 'Greek Drachma', 'GRD', 1, 0, NULL, '₯, Δρχ', ',', 'pre', NULL, 61),
(62, 'Guatemalan Quetzal', 'GTQ', 1, 0, NULL, 'Q', ',', 'pre', NULL, 62),
(63, 'Guinean Franc', 'GNF', 1, 0, NULL, 'FG', ',', 'pre', NULL, 63),
(64, 'Guyanaese Dollar', 'GYD', 1, 0, NULL, '$', ',', 'pre', NULL, 64),
(65, 'Haitian Gourde', 'HTG', 1, 0, NULL, 'G', ',', 'pre', NULL, 65),
(66, 'Honduran Lempira', 'HNL', 1, 0, NULL, 'L', ',', 'pre', NULL, 66),
(67, 'Hong Kong Dollar', 'HKD', 1, 0, NULL, '$', ',', 'pre', NULL, 67),
(68, 'Hungarian Forint', 'HUF', 1, 0, NULL, 'Ft', ',', 'pre', NULL, 68),
(69, 'Icelandic Króna', 'ISK', 1, 0, NULL, 'kr', ',', 'pre', NULL, 69),
(70, 'Indian Rupee', 'INR', 1, 0, NULL, '₹', ',', 'pre', NULL, 70),
(71, 'Indonesian Rupiah', 'IDR', 1, 0, NULL, 'Rp', ',', 'pre', NULL, 71),
(72, 'Iranian Rial', 'IRR', 1, 0, NULL, '﷼', ',', 'pre', NULL, 72),
(73, 'Iraqi Dinar', 'IQD', 1, 0, NULL, 'د.ع', ',', 'pre', NULL, 73),
(74, 'Israeli New Sheqel', 'ILS', 1, 0, NULL, '₪', ',', 'pre', NULL, 74),
(75, 'Italian Lira', 'ITL', 1, 0, NULL, 'L,£', ',', 'pre', NULL, 75),
(76, 'Jamaican Dollar', 'JMD', 1, 0, NULL, 'J$', ',', 'pre', NULL, 76),
(77, 'Japanese Yen', 'JPY', 1, 0, NULL, '¥', ',', 'pre', NULL, 77),
(78, 'Jordanian Dinar', 'JOD', 1, 0, NULL, 'ا.د', ',', 'pre', NULL, 78),
(79, 'Kazakhstani Tenge', 'KZT', 1, 0, NULL, 'лв', ',', 'pre', NULL, 79),
(80, 'Kenyan Shilling', 'KES', 1, 0, NULL, 'KSh', ',', 'pre', NULL, 80),
(81, 'Kuwaiti Dinar', 'KWD', 1, 0, NULL, 'ك.د', ',', 'pre', NULL, 81),
(82, 'Kyrgystani Som', 'KGS', 1, 0, NULL, 'лв', ',', 'pre', NULL, 82),
(83, 'Laotian Kip', 'LAK', 1, 0, NULL, '₭', ',', 'pre', NULL, 83),
(84, 'Latvian Lats', 'LVL', 1, 0, NULL, 'Ls', ',', 'pre', NULL, 84),
(85, 'Lebanese Pound', 'LBP', 1, 0, NULL, '£', ',', 'pre', NULL, 85),
(86, 'Lesotho Loti', 'LSL', 1, 0, NULL, 'L', ',', 'pre', NULL, 86),
(87, 'Liberian Dollar', 'LRD', 1, 0, NULL, '$', ',', 'pre', NULL, 87),
(88, 'Libyan Dinar', 'LYD', 1, 0, NULL, 'د.ل', ',', 'pre', NULL, 88),
(89, 'Litecoin', 'LTC', 1, 0, NULL, 'Ł', ',', 'pre', NULL, 89),
(90, 'Lithuanian Litas', 'LTL', 1, 0, NULL, 'Lt', ',', 'pre', NULL, 90),
(91, 'Macanese Pataca', 'MOP', 1, 0, NULL, '$', ',', 'pre', NULL, 91),
(92, 'Macedonian Denar', 'MKD', 1, 0, NULL, 'ден', ',', 'pre', NULL, 92),
(93, 'Malagasy Ariary', 'MGA', 1, 0, NULL, 'Ar', ',', 'pre', NULL, 93),
(94, 'Malawian Kwacha', 'MWK', 1, 0, NULL, 'MK', ',', 'pre', NULL, 94),
(95, 'Malaysian Ringgit', 'MYR', 1, 0, NULL, 'RM', ',', 'pre', NULL, 95),
(96, 'Maldivian Rufiyaa', 'MVR', 1, 0, NULL, 'Rf', ',', 'pre', NULL, 96),
(97, 'Mauritanian Ouguiya', 'MRO', 1, 0, NULL, 'MRU', ',', 'pre', NULL, 97),
(98, 'Mauritian Rupee', 'MUR', 1, 0, NULL, '₨', ',', 'pre', NULL, 98),
(99, 'Mexican Peso', 'MXN', 1, 0, NULL, '$', ',', 'pre', NULL, 99),
(100, 'Moldovan Leu', 'MDL', 1, 0, NULL, 'L', ',', 'pre', NULL, 100),
(101, 'Mongolian Tugrik', 'MNT', 1, 0, NULL, '₮', ',', 'pre', NULL, 101),
(102, 'Moroccan Dirham', 'MAD', 1, 0, NULL, 'MAD', ',', 'pre', NULL, 102),
(103, 'Mozambican Metical', 'MZM', 1, 0, NULL, 'MT', ',', 'pre', NULL, 103),
(104, 'Myanmar Kyat', 'MMK', 1, 0, NULL, 'K', ',', 'pre', NULL, 104),
(105, 'Namibian Dollar', 'NAD', 1, 0, NULL, '$', ',', 'pre', NULL, 105),
(106, 'Nepalese Rupee', 'NPR', 1, 0, NULL, '₨', ',', 'pre', NULL, 106),
(107, 'Netherlands Antillean Guilder', 'ANG', 1, 0, NULL, 'ƒ', ',', 'pre', NULL, 107),
(108, 'New Taiwan Dollar', 'TWD', 1, 0, NULL, '$', ',', 'pre', NULL, 108),
(109, 'New Zealand Dollar', 'NZD', 1, 0, NULL, '$', ',', 'pre', NULL, 109),
(110, 'Nicaraguan Córdoba', 'NIO', 1, 0, NULL, 'C$', ',', 'pre', NULL, 110),
(111, 'Nigerian Naira', 'NGN', 1, 0, NULL, '₦', ',', 'pre', NULL, 111),
(112, 'North Korean Won', 'KPW', 1, 0, NULL, '₩', ',', 'pre', NULL, 112),
(113, 'Norwegian Krone', 'NOK', 1, 0, NULL, 'kr', ',', 'pre', NULL, 113),
(114, 'Omani Rial', 'OMR', 1, 0, NULL, '.ع.ر', ',', 'pre', NULL, 114),
(115, 'Pakistani Rupee', 'PKR', 1, 0, NULL, '₨', ',', 'pre', NULL, 115),
(116, 'Panamanian Balboa', 'PAB', 1, 0, NULL, 'B/.', ',', 'pre', NULL, 116),
(117, 'Papua New Guinean Kina', 'PGK', 1, 0, NULL, 'K', ',', 'pre', NULL, 117),
(118, 'Paraguayan Guarani', 'PYG', 1, 0, NULL, '₲', ',', 'pre', NULL, 118),
(119, 'Peruvian Nuevo Sol', 'PEN', 1, 0, NULL, 'S/.', ',', 'pre', NULL, 119),
(120, 'Philippine Peso', 'PHP', 1, 0, NULL, '₱', ',', 'pre', NULL, 120),
(121, 'Polish Zloty', 'PLN', 1, 0, NULL, 'zł', ',', 'pre', NULL, 121),
(122, 'Qatari Rial', 'QAR', 1, 0, NULL, 'ق.ر', ',', 'pre', NULL, 122),
(123, 'Romanian Leu', 'RON', 1, 0, NULL, 'lei', ',', 'pre', NULL, 123),
(124, 'Russian Ruble', 'RUB', 1, 0, NULL, '₽', ',', 'pre', NULL, 124),
(125, 'Rwandan Franc', 'RWF', 1, 0, NULL, 'FRw', ',', 'pre', NULL, 125),
(126, 'Salvadoran Colón', 'SVC', 1, 0, NULL, '₡', ',', 'pre', NULL, 126),
(127, 'Samoan Tala', 'WST', 1, 0, NULL, 'SAT', ',', 'pre', NULL, 127),
(128, 'São Tomé and Príncipe Dobra', 'STD', 1, 0, NULL, 'Db', ',', 'pre', NULL, 128),
(129, 'Saudi Riyal', 'SAR', 1, 0, NULL, '﷼', ',', 'pre', NULL, 129),
(130, 'Serbian Dinar', 'RSD', 1, 0, NULL, 'din', ',', 'pre', NULL, 130),
(131, 'Seychellois Rupee', 'SCR', 1, 0, NULL, 'SRe', ',', 'pre', NULL, 131),
(132, 'Sierra Leonean Leone', 'SLL', 1, 0, NULL, 'Le', ',', 'pre', NULL, 132),
(133, 'Singapore Dollar', 'SGD', 1, 0, NULL, '$', ',', 'pre', NULL, 133),
(134, 'Slovak Koruna', 'SKK', 1, 0, NULL, 'Sk', ',', 'pre', NULL, 134),
(135, 'Solomon Islands Dollar', 'SBD', 1, 0, NULL, 'Si$', ',', 'pre', NULL, 135),
(136, 'Somali Shilling', 'SOS', 1, 0, NULL, 'Sh.so.', ',', 'pre', NULL, 136),
(137, 'South African Rand', 'ZAR', 1, 0, NULL, 'R', ',', 'pre', NULL, 137),
(138, 'South Korean Won', 'KRW', 1, 0, NULL, '₩', ',', 'pre', NULL, 138),
(139, 'South Sudanese Pound', 'SSP', 1, 0, NULL, '£', ',', 'pre', NULL, 139),
(140, 'Special Drawing Rights', 'XDR', 1, 0, NULL, 'SDR', ',', 'pre', NULL, 140),
(141, 'Sri Lankan Rupee', 'LKR', 1, 0, NULL, 'Rs', ',', 'pre', NULL, 141),
(142, 'St. Helena Pound', 'SHP', 1, 0, NULL, '£', ',', 'pre', NULL, 142),
(143, 'Sudanese Pound', 'SDG', 1, 0, NULL, '.س.ج', ',', 'pre', NULL, 143),
(144, 'Surinamese Dollar', 'SRD', 1, 0, NULL, '$', ',', 'pre', NULL, 144),
(145, 'Swazi Lilangeni', 'SZL', 1, 0, NULL, 'E', ',', 'pre', NULL, 145),
(146, 'Swedish Krona', 'SEK', 1, 0, NULL, 'kr', ',', 'pre', NULL, 146),
(147, 'Swiss Franc', 'CHF', 1, 0, NULL, 'CHf', ',', 'pre', NULL, 147),
(148, 'Syrian Pound', 'SYP', 1, 0, NULL, 'LS', ',', 'pre', NULL, 148),
(149, 'Tajikistani Somoni', 'TJS', 1, 0, NULL, 'SM', ',', 'pre', NULL, 149),
(150, 'Tanzanian Shilling', 'TZS', 1, 0, NULL, 'TSh', ',', 'pre', NULL, 150),
(151, 'Thai Baht', 'THB', 1, 0, NULL, '฿', ',', 'pre', NULL, 151),
(152, 'Tongan Paanga', 'TOP', 1, 0, NULL, '$', ',', 'pre', NULL, 152),
(153, 'Trinidad & Tobago Dollar', 'TTD', 1, 0, NULL, '$', ',', 'pre', NULL, 153),
(154, 'Tunisian Dinar', 'TND', 1, 0, NULL, 'ت.د', ',', 'pre', NULL, 154),
(155, 'Turkish Lira', 'TRY', 1, 0, NULL, '₺', ',', 'pre', NULL, 155),
(156, 'Turkmenistani Manat', 'TMT', 1, 0, NULL, 'T', ',', 'pre', NULL, 156),
(157, 'Ugandan Shilling', 'UGX', 1, 0, NULL, 'USh', ',', 'pre', NULL, 157),
(158, 'Ukrainian Hryvnia', 'UAH', 1, 0, NULL, '₴', ',', 'pre', NULL, 158),
(159, 'United Arab Emirates Dirham', 'AED', 1, 0, NULL, 'إ.د', ',', 'pre', NULL, 159),
(160, 'Uruguayan Peso', 'UYU', 1, 0, NULL, '$', ',', 'pre', NULL, 160),
(161, 'US Dollar', 'USD', 1, 1, NULL, '$', ',', 'pre', NULL, 161),
(162, 'Uzbekistan Som', 'UZS', 1, 0, NULL, 'лв', ',', 'pre', NULL, 162),
(163, 'Vanuatu Vatu', 'VUV', 1, 0, NULL, 'VT', ',', 'pre', NULL, 163),
(164, 'Venezuelan BolÃvar', 'VEF', 1, 0, NULL, 'Bs', ',', 'pre', NULL, 164),
(165, 'Vietnamese Dong', 'VND', 1, 0, NULL, '₫', ',', 'pre', NULL, 165),
(166, 'Yemeni Rial', 'YER', 1, 0, NULL, '﷼', ',', 'pre', NULL, 166),
(167, 'Zambian Kwacha', 'ZMK', 1, 0, NULL, 'ZK', ',', 'pre', NULL, 167),
(168, 'Zimbabwean dollar', 'ZWL', 1, 0, NULL, '$', ',', 'pre', NULL, 168);

ALTER TABLE `engine4_payment_transactions` ADD `change_rate` FLOAT NOT NULL DEFAULT '0', ADD `current_currency` CHAR(3) NULL DEFAULT NULL COMMENT 'paid by the user in currency';

ALTER TABLE `engine4_payment_transactions` CHANGE `currency` `currency` CHAR(3) NOT NULL COMMENT 'default currency';

INSERT IGNORE INTO `engine4_payment_gateways` (`title`, `description`, `enabled`, `plugin`, `test_mode`) VALUES ("Stripe", NULL, 0, "Payment_Plugin_Gateway_Stripe", 0);



DROP TABLE IF EXISTS `engine4_payment_packagetemplates`;
CREATE TABLE `engine4_payment_packagetemplates` (
  `packagetemplate_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `title` varchar(255) NOT NULL,
  `body_container_clr` varchar(16) DEFAULT NULL,
  `header_bgclr` varchar(16) DEFAULT NULL,
  `header_txtclr` varchar(16) DEFAULT NULL,
  `overlap` tinyint(1) NOT NULL DEFAULT 0,
  `params` TEXT NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `engine4_payment_packagestyles`;
CREATE TABLE `engine4_payment_packagestyles` (
  `packagestyle_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `package_id` int(11) NOT NULL,
  `packagetemplate_id` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `column_title` varchar(255) NOT NULL,
  `column_width` varchar(64) DEFAULT NULL,
  `column_margin` int(2) DEFAULT NULL,
  `row_height` int(6) DEFAULT NULL,
  `column_descr_height` int(6) DEFAULT NULL,
  `icon_position` tinyint(1) NOT NULL, 
  `show_highlight` tinyint(1) DEFAULT NULL, 
  `show_label` tinyint(1) NOT NULL DEFAULT 0,
  `label_text` varchar(255) DEFAULT NULL,
  `footer_text` varchar(256) DEFAULT NULL,
  `column_text_color` varchar(64) DEFAULT NULL,
  `footer_text_color` varchar(64) DEFAULT NULL,
  `footer_bg_color` varchar(64) DEFAULT NULL,
  `column_color` varchar(32) DEFAULT NULL,
  `column_row_color` varchar(32) DEFAULT NULL,
  `column_row_text_color` varchar(32) DEFAULT NULL,
  `label_text_color` varchar(32) DEFAULT NULL,
  `label_color` varchar(32) DEFAULT NULL,
  `row_border_color` varchar(32) DEFAULT NULL,
  `upgrade_footer_text` VARCHAR(255) NULL DEFAULT NULL,
  `label_position` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `engine4_payment_currencies` ADD INDEX(`code`);
ALTER TABLE `engine4_payment_currencies` ADD INDEX(`order`);
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('payment.body.container.clr', '#FFFFFF'),
('payment.header.bgclr', '#FFFFFF'),
('payment.header.txtclr', '#000000'),
('payment.table.description', 'Please choose a subscription plan from the options below.'),
('payment.table.title', 'Subscription Plans');


-- wallet
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