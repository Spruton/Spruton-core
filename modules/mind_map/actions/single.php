<?php

$current_entity_id = _get::int('entities_id');
$current_item_id = _get::int('items_id');
$fields_id = _get::int('fields_id');

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

$mind_map = new mind_map($current_entity_id, $current_item_id, $fields_id);

//check access to field
if(!$mind_map->has_access())
{
	die();
}

$app_layout = 'mind_map_layout.php';

switch($app_module_action)
{
	case 'save':
		
		//echo '<pre>';
		//print_r($_POST);
		
		if(isset($_POST['root']))
		{					
			$mind_map->save($_POST['root']);			
		}
		
		exit();
		break;
}