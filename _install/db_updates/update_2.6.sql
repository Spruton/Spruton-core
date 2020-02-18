ALTER TABLE `app_filters_panels` ADD `type` VARCHAR(64) NOT NULL AFTER `entities_id`;
ALTER TABLE `app_filters_panels_fields` ADD `search_type_match` TINYINT(1) NOT NULL AFTER `display_type`;
ALTER TABLE `app_global_lists_choices` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `lists_id`;
ALTER TABLE `app_fields_choices` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `fields_id`;
ALTER TABLE `app_reports` ADD `assigned_to` TEXT NOT NULL AFTER `users_groups`;
ALTER TABLE `app_global_lists` ADD `notes` TEXT NOT NULL AFTER `name`;
ALTER TABLE `app_global_lists_choices` ADD `notes` TEXT NOT NULL AFTER `users`;
ALTER TABLE `app_reports` ADD `dashboard_counter_hide_zero_count` TINYINT(1) NOT NULL AFTER `dashboard_counter_hide_count`;

CREATE TABLE IF NOT EXISTS `app_records_visibility_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `users_groups` text NOT NULL,
  `merged_fields` text NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;