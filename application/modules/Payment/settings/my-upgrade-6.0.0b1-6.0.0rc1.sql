INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_subscription_changed', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[subscription_title],[subscription_description],[subscription_terms],[current_plan],[changed_plan]');

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("payment_subscription_changed", "payment", 'Your subscription plan changed from {var:$currentPlan} to {var:$changedPlan}.', 0, "");
