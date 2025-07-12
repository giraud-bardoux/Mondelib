INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_manual_subscribe', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[payment_method],[admin_link]');

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("payment_manual_subscribe", "payment", '{item:$subject} has subscribe with payment method {var:$payment_method} on this {var:$adminsidelink}.', 0, "");
