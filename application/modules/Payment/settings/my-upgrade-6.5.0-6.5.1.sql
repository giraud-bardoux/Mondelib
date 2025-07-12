UPDATE `engine4_core_menuitems` SET `label` = 'Billing Settings' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment";
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"payment","controller":"settings","action":"index"}' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment";

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES 
('core_admin_main_orders', 'payment', 'Orders', '', '{"route":"admin_default","module":"payment","controller":"index","action":"index"}', 'core_admin_main_monetization', '', 9);

UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_orders' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment_transactions";
UPDATE `engine4_core_menuitems` SET `name` = 'core_admin_main_orders_transactions' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment_transactions";

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_membership', 'payment', 'Member Subscription Settings', '', '{"route":"admin_default","module":"payment","controller":"package","action":"settings"}', 'core_admin_main_monetization', '', 6),
('core_admin_main_membership_settings', 'payment', 'Global Settings', '', '{"route":"admin_default","module":"payment","controller":"package","action":"settings"}', 'core_admin_main_membership', '', 1);
UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_membership' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment_packages";
UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_membership' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_payment_subscriptions";



UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default", "module":"payment", "controller":"settings", "action":"index", "icon":"fas fa-money-check-alt"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_payment';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default", "module":"payment", "controller":"settings", "action":"transaction", "icon":"fas fa-money-check-alt"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_transaction';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default", "module":"payment", "controller":"settings", "action":"verification", "icon":"fas fa-check-circle"}' WHERE `engine4_core_menuitems`.`name` = 'user_settings_verification';
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("core_admin_main_payment_currency", "payment", "Manage Currency", "", '{"route":"admin_default","module":"payment","controller":"settings","action":"currency"}', "core_admin_main_payment", "", 8),
('core_mini_currency', 'payment', 'Currency Chooser', 'Payment_Plugin_Menus', '{"route":"default","module":"payment","icon":"fa fa-usd"}', 'core_mini', '', 8);

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
ALTER TABLE `engine4_payment_transactions` CHANGE `currency` `currency` CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'default currency';
INSERT IGNORE INTO `engine4_payment_gateways` (`title`, `description`, `enabled`, `plugin`, `test_mode`) VALUES ("Stripe", NULL, 0, "Payment_Plugin_Gateway_Stripe", 0);

ALTER TABLE `engine4_payment_packages` ADD `order` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_payment_packages` ADD `photo_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_packages` ADD `packagestyles` TEXT NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_packages` ADD `features` TEXT NULL DEFAULT NULL;

ALTER TABLE `engine4_payment_packages` ADD `row1_text` VARCHAR(255) NULL DEFAULT NULL, ADD `row1_description` VARCHAR(255) NULL DEFAULT NULL, ADD `row1_file_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_packages` ADD `row2_text` VARCHAR(255) NULL DEFAULT NULL, ADD `row2_description` VARCHAR(255) NULL DEFAULT NULL, ADD `row2_file_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_packages` ADD `row3_text` VARCHAR(255) NULL DEFAULT NULL, ADD `row3_description` VARCHAR(255) NULL DEFAULT NULL, ADD `row3_file_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `engine4_payment_packages` ADD `row4_text` VARCHAR(255) NULL DEFAULT NULL, ADD `row4_description` VARCHAR(255) NULL DEFAULT NULL, ADD `row4_file_id` VARCHAR(255) NULL DEFAULT NULL;

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
ALTER TABLE `engine4_payment_subscriptions` ADD `email_reminder` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'This is for subscription plan is expiring reminder email.';
DELETE FROM engine4_core_mailtemplates WHERE `engine4_core_mailtemplates`.`type` = "payment_verification_expired";

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('payment.body.container.clr', '#FFFFFF'),
('payment.header.bgclr', '#FFFFFF'),
('payment.header.txtclr', '#000000'),
('payment.table.description', 'Please choose a subscription plan from the options below.'),
('payment.table.title', 'Subscription Plans');
