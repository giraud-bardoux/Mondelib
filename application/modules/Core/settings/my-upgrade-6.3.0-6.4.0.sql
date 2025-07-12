INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.general.enableloginlogs', '1'),
('core.general.logincrondays', '5');

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Clear Login Logs', 'core', 'Core_Plugin_Task_ClarLoginLog', 432000);
