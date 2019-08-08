<?php

define('TEXT_UPDATE_VERSION_FROM','2.4');
define('TEXT_UPDATE_VERSION_TO','2.5');

include('includes/template_top.php');

$tables_array = array();
$tables_query = db_query("show tables");
while($tables = db_fetch_array($tables_query))
{
  $tables_array[] = current($tables);      
}

//print_r($columns_array);

//check if we have to run update for current database
if(!in_array('app_ext_mail',$tables_array))
{
  echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';

//required sql update   
$sql = "  
CREATE TABLE IF NOT EXISTS `app_ext_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `date_added` int(11) NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `subject_cropped` varchar(255) NOT NULL,
  `groups_id` int(11) NOT NULL,
  `is_new_group` tinyint(1) NOT NULL,
  `body` longtext NOT NULL,
  `body_text` longtext NOT NULL,
  `to_name` text NOT NULL,
  `to_email` text NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `reply_to_name` text NOT NULL,
  `reply_to_email` text NOT NULL,
  `cc_name` text NOT NULL,
  `cc_email` text NOT NULL,
  `bcc_name` text NOT NULL,
  `bcc_email` text NOT NULL,
  `attachments` text NOT NULL,
  `error_msg` tinytext NOT NULL,
  `is_sent` tinyint(1) NOT NULL,
  `is_star` tinyint(1) NOT NULL,
  `in_trash` tinyint(1) NOT NULL,
  `is_spam` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_to_email` (`to_email`(255)),
  KEY `idx_from_email` (`from_email`),
  KEY `idx_accounts_id` (`accounts_id`),
  KEY `idx_groups_id` (`groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL,
  `name` varchar(64) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `bg_color` varchar(16) NOT NULL,
  `imap_server` varchar(255) NOT NULL,
  `mailbox` varchar(64) NOT NULL,
  `login` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `delete_emails` tinyint(1) NOT NULL,
  `is_fetched` tinyint(1) NOT NULL,
  `use_smtp` tinyint(1) NOT NULL,
  `smtp_server` varchar(255) NOT NULL,
  `smtp_port` varchar(16) NOT NULL,
  `smtp_encryption` varchar(16) NOT NULL,
  `smtp_login` varchar(64) NOT NULL,
  `smtp_password` varchar(64) NOT NULL,
  `send_autoreply` tinyint(1) NOT NULL,
  `autoreply_msg` text NOT NULL,
  `not_group_by_subject` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `entities_id` int(11) NOT NULL,
  `parent_item_id` int(11) NOT NULL,
  `from_name` int(11) NOT NULL,
  `from_email` int(11) NOT NULL,
  `subject` int(11) NOT NULL,
  `body` int(11) NOT NULL,
  `attachments` int(11) NOT NULL,
  `bind_to_sender` tinyint(1) NOT NULL,
  `auto_create` int(1) NOT NULL,
  `title` varchar(64) NOT NULL,
  `hide_buttons` varchar(64) NOT NULL,
  `fields_in_listing` text NOT NULL,
  `fields_in_popup` text NOT NULL,
  `related_emails_position` varchar(16) NOT NULL,
  `sort_order` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_id` (`accounts_id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_parent_item_id` (`parent_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts_entities_fields` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_entities_id` int(10) UNSIGNED NOT NULL,
  `filters_id` int(11) NOT NULL,
  `fields_id` int(10) UNSIGNED NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fields_id` (`fields_id`),
  KEY `idx_account_entities_id` (`account_entities_id`) USING BTREE,
  KEY `idx_filters_id` (`filters_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `send_mail_as` varchar(128) NOT NULL,
  `signature` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_id` (`accounts_id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_id` (`accounts_id`),
  KEY `idx_name` (`name`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `has_words` text NOT NULL,
  `action` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_id` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `subject_cropped` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_check` (`accounts_id`,`subject_cropped`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_groups_from` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_groups_id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mail_groups_id` (`mail_groups_id`,`from_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_to_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_groups_id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `entities_id` int(11) NOT NULL,
  `items_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`),
  KEY `idx_items_id` (`items_id`),
  KEY `idx_mail_groups_id` (`mail_groups_id`) USING BTREE,
  KEY `idx_from_email` (`from_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts_entities_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_entities_id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `has_words` text NOT NULL,
  `parent_item_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_account_entities_id` (`account_entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_ext_mail_accounts_entities_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_entities_id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `has_words` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_account_entities_id` (`account_entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_public_forms` ADD `parent_item_id` INT NOT NULL AFTER `entities_id`, ADD `hide_parent_item` TINYINT(1) NOT NULL AFTER `parent_item_id`;

ALTER TABLE `app_ext_export_templates` ADD `button_title` VARCHAR(64) NOT NULL AFTER `description`, ADD `button_position` VARCHAR(64) NOT NULL AFTER `button_title`, ADD `button_color` VARCHAR(7) NOT NULL AFTER `button_position`, ADD `button_icon` VARCHAR(64) NOT NULL AFTER `button_color`;
UPDATE `app_ext_export_templates` set button_position='menu_more_actions,menu_with_selected';

ALTER TABLE `app_ext_export_templates` ADD `split_into_pages` TINYINT(1) NOT NULL DEFAULT '1' AFTER `page_orientation`;

ALTER TABLE `app_ext_processes` CHANGE `button_position` `button_position` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `app_ext_processes_buttons_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `button_color` varchar(7) NOT NULL,
  `button_icon` varchar(64) NOT NULL,
  `button_position` varchar(64) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_ext_processes` ADD `apply_fields_display_rules` TINYINT(1) NOT NULL AFTER `apply_fields_access_rules`;
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