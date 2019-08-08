<?php

define('TEXT_UPDATE_VERSION_FROM','2.3');
define('TEXT_UPDATE_VERSION_TO','2.4');

include('includes/template_top.php');

$tables_array = array();
$tables_query = db_query("show tables");
while($tables = db_fetch_array($tables_query))
{
  $tables_array[] = current($tables);      
}

//print_r($columns_array);

//check if we have to run update for current database
if(!in_array('app_ext_import_templates',$tables_array))
{
  echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';

//required sql update   
$sql = "  
CREATE TABLE IF NOT EXISTS `app_ext_subscribe_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `modules_id` int(11) NOT NULL,
  `contact_list_id` varchar(255) NOT NULL,
  `contact_email_field_id` int(11) NOT NULL,
  `contact_fields` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_modules_id` (`modules_id`),
  KEY `idx_fields_id` (`contact_email_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_email_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `action_type` varchar(64) NOT NULL,
  `send_to_users` text NOT NULL,
  `send_to_assigned_users` text NOT NULL,
  `monitor_fields_id` int(11) NOT NULL,
  `monitor_choices` text NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_monitor_fields_id` (`monitor_fields_id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_processes_actions` ADD `settings` TEXT NOT NULL AFTER `sort_order`;
ALTER TABLE `app_ext_processes` ADD `access_to_assigned` TEXT NOT NULL AFTER `assigned_to`;
ALTER TABLE `app_ext_processes` ADD `apply_fields_access_rules` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_active`;
ALTER TABLE `app_ext_processes` ADD `success_message` TEXT NOT NULL AFTER `apply_fields_access_rules`;
ALTER TABLE `app_ext_processes` ADD `redirect_to_items_listing` TINYINT(1) NOT NULL DEFAULT '0' AFTER `success_message`;
ALTER TABLE `app_ext_processes` ADD `disable_comments` TINYINT(1) NOT NULL AFTER `redirect_to_items_listing`;

ALTER TABLE `app_ext_calendar` ADD `default_view` VARCHAR(16) NOT NULL AFTER `name`;
ALTER TABLE `app_ext_calendar` ADD `highlighting_weekends` VARCHAR(64) NOT NULL AFTER `default_view`;

CREATE TABLE IF NOT EXISTS `app_ext_call_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL,
  `date_added` int(11) NOT NULL,
  `direction` varchar(16) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `duration` int(11) NOT NULL,
  `sms_text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_import_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `import_fields` text NOT NULL,
  `users_groups` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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