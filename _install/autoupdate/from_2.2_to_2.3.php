<?php

define('TEXT_UPDATE_VERSION_FROM','2.2');
define('TEXT_UPDATE_VERSION_TO','2.3');

include('includes/template_top.php');

$tables_array = array();
$tables_query = db_query("show tables");
while($tables = db_fetch_array($tables_query))
{
  $tables_array[] = current($tables);      
}

//print_r($columns_array);

//check if we have to run updat for current database
if(!in_array('app_image_map_markers',$tables_array))
{
  echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';

//required sql update   
$sql = "  
ALTER TABLE `app_fields_choices` ADD `filename` VARCHAR(255) NOT NULL AFTER `value`;

CREATE TABLE IF NOT EXISTS `app_image_map_markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `items_id` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_items_id` (`items_id`),
  KEY `idx_map_id` (`map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_image_map_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `map_id` int(11) NOT NULL,
  `choices_id` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_map_id` (`map_id`),
  KEY `idx_choices_id` (`choices_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_mind_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `items_id` int(11) DEFAULT NULL,
  `fields_id` int(11) DEFAULT NULL,
  `reports_id` int(11) DEFAULT NULL,
  `mm_id` varchar(64) NOT NULL,
  `mm_parent_id` varchar(64) NOT NULL,
  `mm_text` varchar(255) NOT NULL,
  `mm_layout` varchar(16) NOT NULL,
  `mm_shape` varchar(16) NOT NULL,
  `mm_side` varchar(16) NOT NULL,
  `mm_color` varchar(16) NOT NULL,
  `mm_icon` varchar(32) NOT NULL,
  `mm_collapsed` varchar(1) NOT NULL,
  `mm_value` varchar(64) NOT NULL,
  `mm_items_id` int(11) DEFAULT '0',
  `parent_entity_item_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_items_id` (`items_id`),
  KEY `idx_fields_id` (`fields_id`),
  KEY `idx_reports_id` (`reports_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_reports` ADD `listing_type` VARCHAR(16) NOT NULL AFTER `notification_time`;

CREATE TABLE IF NOT EXISTS `app_listing_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_default` tinyint(4) NOT NULL,
  `width` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_listing_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_types_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `fields` text NOT NULL,
  `display_as` varchar(16) NOT NULL,
  `display_field_names` tinyint(1) NOT NULL,
  `text_align` varchar(16) NOT NULL,
  `width` varchar(16) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_listing_types_id` (`listing_types_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_reports_groups` ADD `is_common` TINYINT(1) NOT NULL DEFAULT '0' AFTER `created_by`, ADD `users_groups` TEXT NOT NULL AFTER `is_common`;
";
    
  db_query_from_content(trim($sql));
  

//if there are no any errors display success message    
  echo '<div class="alert alert-success">' . TEXT_UPDATE_COMPLATED . '</div>';
}
else
{
  echo '<div class="alert alert-warning">' . TEXT_UPDATE_ALREADY_RUN . '</div>';
}

include('includes/template_bottom.php');