<?php

$map_id = _get::int('map_id');

$path_info = items::parse_path($app_path);
$current_entity_id = $path_info['entity_id'];
$current_item_id = $path_info['item_id'];

//get access schema for current entity
$current_access_schema = users::get_entities_access_schema($current_entity_id,$app_user['group_id']);

//checking view access
if(!users::has_access('view') and  !users::has_access('view_assigned'))
{
	die();
}

//check assigned access
if(users::has_access('view_assigned') and $app_user['group_id']>0 and $current_item_id>0)
{
	if(!users::has_access_to_assigned_item($current_entity_id,$current_item_id))
	{
		die();
	}
}

$app_layout = 'map_layout.php';