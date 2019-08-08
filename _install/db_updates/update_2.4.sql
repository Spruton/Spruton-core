
ALTER TABLE `app_reports` ADD `listing_col_width` TEXT NOT NULL AFTER `listing_type`;

ALTER TABLE `app_fields` ADD `tooltip_in_item_page` TINYINT(1) NOT NULL DEFAULT '0' AFTER `tooltip_display_as`, ADD `tooltip_item_page` TEXT NOT NULL AFTER `tooltip_in_item_page`;

ALTER TABLE `app_global_lists_choices` ADD `users` TEXT NOT NULL AFTER `sort_order`;

CREATE TABLE IF NOT EXISTS `app_users_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `is_success` tinyint(1) NOT NULL,
  `date_added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_dashboard_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `sections_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `color` varchar(16) NOT NULL,
  `users_fields` text NOT NULL,
  `users_groups` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_sections_id` (`sections_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_dashboard_pages_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `grid` tinyint(1) NOT NULL,
  `sort_order` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_access_groups` CHANGE `ldap_filter` `ldap_filter` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `app_fields` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `app_help_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `description` text NOT NULL,
  `color` varchar(16) NOT NULL,
  `position` varchar(16) NOT NULL,
  `users_groups` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_reports` ADD `dashboard_counter_hide_count` TINYINT(1) NOT NULL DEFAULT '0' AFTER `in_dashboard_counter_fields`, ADD `dashboard_counter_sum_by_field` INT NOT NULL AFTER `dashboard_counter_hide_count`;

