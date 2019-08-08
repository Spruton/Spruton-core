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

//check if we have to run updat for current database
if(!in_array('app_help_pages',$tables_array))
{
  echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';

//required sql update   
$sql = "  
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
		
";
    
  db_query_from_content(trim($sql));
  
  
//update times diff
$fields_query = db_query("select id, entities_id from app_fields where type in ('fieldtype_days_difference','fieldtype_hours_difference')");
while($fields = db_fetch_array($fields_query))
{
	db_query("ALTER TABLE app_entity_" . $fields['entities_id']. " CHANGE field_" . $fields['id'] . " field_" . $fields['id'] . " FLOAT NOT NULL;");	
}

//prepare date_updated field for all entities
$entities_query = db_query("select * from app_entities");
while($entities = db_fetch_array($entities_query))
{
	db_query("ALTER TABLE app_entity_" . $entities['id'] . " ADD date_updated INT NOT NULL DEFAULT '0' AFTER date_added");

	//prepare fieldtype_user_last_login_date
	$fields_query = db_query("select id, entities_id from app_fields where type in ('fieldtype_date_updated') and  entities_id='" . $entities['id'] . "'");
	if(!$fields = db_fetch_array($fields_query))
	{
		$tab_info_query = db_query("select forms_tabs_id from app_fields where type='fieldtype_date_added' and entities_id='" . $entities['id'] . "'");
		$tab_info = db_fetch_array($tab_info_query);

		$sql_data = [
				'type' => 'fieldtype_date_updated',
				'entities_id'=>$entities['id'],
				'forms_tabs_id' => $tab_info['forms_tabs_id'],
				'sort_order'=>3,
				'name' =>'',
		];

		db_perform('app_fields', $sql_data);
	}
}

require('../../includes/classes/fieldstypes/fieldtype_days_difference.php');
require('../../includes/classes/fieldstypes/fieldtype_hours_difference.php');

db_query("DROP FUNCTION IF EXISTS spruton_hours_diff");
db_query("DROP FUNCTION IF EXISTS spruton_days_diff");

fieldtype_days_difference::prepare_procedure();
fieldtype_hours_difference::prepare_procedure();
  
//if there are no any errors display success message    
  echo '<div class="alert alert-success">' . TEXT_UPDATE_COMPLATED . '</div>';
}
else
{
  echo '<div class="alert alert-warning">' . TEXT_UPDATE_ALREADY_RUN . '</div>';
}

include('includes/template_bottom.php');