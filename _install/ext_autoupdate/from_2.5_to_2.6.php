<?php

define('TEXT_UPDATE_VERSION_FROM','2.5');
define('TEXT_UPDATE_VERSION_TO','2.6');

include('includes/template_top.php');

$tables_array = array();
$tables_query = db_query("show tables");
while($tables = db_fetch_array($tables_query))
{
  $tables_array[] = current($tables);      
}

//print_r($columns_array);

//check if we have to run update for current database
if(!in_array('app_ext_pivot_calendars',$tables_array))
{
  echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';

//required sql update   
$sql = "  
ALTER TABLE `app_ext_ipages` ADD `attachments` TEXT NOT NULL AFTER `is_menu`;

CREATE TABLE IF NOT EXISTS `app_ext_pivot_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `default_view` varchar(16) NOT NULL,
  `highlighting_weekends` varchar(64) NOT NULL,
  `min_time` varchar(5) NOT NULL,
  `max_time` varchar(5) NOT NULL,
  `time_slot_duration` varchar(8) NOT NULL,
  `display_legend` tinyint(1) NOT NULL DEFAULT '0',
  `in_menu` tinyint(1) NOT NULL,
  `users_groups` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_pivot_calendars_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendars_id` int(11) NOT NULL,
  `entities_id` int(11) NOT NULL,
  `bg_color` varchar(10) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `heading_template` varchar(64) NOT NULL,
  `fields_in_popup` text NOT NULL,
  `background` varchar(10) NOT NULL,
  `use_background` int(11) NOT NULL,  
  PRIMARY KEY (`id`),
  KEY `idx_calendars_id` (`calendars_id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_item_pivot_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `allowed_groups` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  `related_entities_id` int(11) NOT NULL,
  `related_entities_fields` text NOT NULL,
  `position` varchar(16) NOT NULL,
  `rows_per_page` int(11) NOT NULL,
  `fields_in_listing` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_related_entities_id` (`related_entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_item_pivot_tables_calcs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reports_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  `formula` text NOT NULL,
  `select_query` text NOT NULL,
  `where_query` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reports_id` (`reports_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_public_forms` ADD `form_js` TEXT NOT NULL AFTER `form_css`;

ALTER TABLE `app_ext_calendar` ADD `min_time` VARCHAR(5) NOT NULL AFTER `highlighting_weekends`, ADD `max_time` VARCHAR(5) NOT NULL AFTER `min_time`, ADD `time_slot_duration` VARCHAR(8) NOT NULL AFTER `max_time`;

CREATE TABLE IF NOT EXISTS `app_ext_global_search_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `fields_for_search` text NOT NULL,
  `fields_in_listing` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_ipages` ADD `assigned_to` TEXT NOT NULL AFTER `users_groups`;
ALTER TABLE `app_ext_export_templates` ADD `template_header` TEXT NOT NULL AFTER `split_into_pages`, ADD `template_footer` TEXT NOT NULL AFTER `template_header`;
ALTER TABLE `app_ext_pivotreports` ADD `allow_edit` TINYINT(1) NOT NULL AFTER `allowed_groups`;

CREATE TABLE IF NOT EXISTS `app_ext_pivotreports_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reports_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `reports_settings` text NOT NULL,
  `view_mode` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reports_id` (`reports_id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_sms_rules` CHANGE `phone` `phone` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `app_ext_xml_export_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `button_title` varchar(64) NOT NULL,
  `button_position` varchar(64) NOT NULL,
  `button_color` varchar(7) NOT NULL,
  `button_icon` varchar(64) NOT NULL,
  `users_groups` text NOT NULL,
  `assigned_to` text NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `template_header` text NOT NULL,
  `template_body` text NOT NULL,
  `template_footer` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_email_rules` ADD `attach_attachments` TINYINT(1) NOT NULL AFTER `is_active`;

CREATE TABLE IF NOT EXISTS `app_ext_processes_clone_subitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actions_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `from_entity_id` int(11) NOT NULL,
  `to_entity_id` int(11) NOT NULL,
  `fields` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_from_entity_id` (`from_entity_id`),
  KEY `idx_to_entity_id` (`to_entity_id`),
  KEY `idx_actions_id` (`actions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_email_rules` ADD `send_to_email` TEXT NOT NULL AFTER `send_to_assigned_users`, ADD `send_to_assigned_email` TEXT NOT NULL AFTER `send_to_email`;
ALTER TABLE `app_ext_calendar` ADD `view_modes` VARCHAR(255) NOT NULL AFTER `default_view`;
ALTER TABLE `app_ext_pivot_calendars` ADD `view_modes` VARCHAR(255) NOT NULL AFTER `default_view`;
ALTER TABLE `app_ext_import_templates` ADD `multilevel_import` INT NOT NULL AFTER `entities_id`;
ALTER TABLE `app_ext_ganttchart` ADD `default_fields_in_listing` VARCHAR(64) NOT NULL DEFAULT 'start_date,end_date,duration' AFTER `use_background`;
ALTER TABLE `app_ext_ganttchart` ADD `grid_width` SMALLINT NOT NULL AFTER `default_fields_in_listing`;
ALTER TABLE `app_ext_ganttchart` ADD `default_view` VARCHAR(16) NOT NULL DEFAULT 'day' AFTER `grid_width`;
";
    
  db_query_from_content(trim($sql));
  
//extra code for update
   
//if there are no any errors display success message    
  echo '<div class="alert alert-success">' . TEXT_UPDATE_COMPLATED . '</div>';
}
else
{
  echo '<div class="alert alert-warning">' . TEXT_UPDATE_ALREADY_RUN . '</div>';
}

include('includes/template_bottom.php');