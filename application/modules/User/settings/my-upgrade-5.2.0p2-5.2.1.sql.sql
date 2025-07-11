INSERT IGNORE INTO `engine4_authorization_permissions`
SELECT
  level_id as `level_id`,
  'user' as `type`,
  'mention' as `name`,
  5 as `value`,
  '["owner_network","registered","network","member","owner"]' as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');