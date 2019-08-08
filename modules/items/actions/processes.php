<?php

//check if process exist
$app_process_info_query = db_query("select * from app_ext_processes where id='" . _get::int('id'). "' and is_active=1");
if(!$app_process_info = db_fetch_array($app_process_info_query))
{
	redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{
	case 'run':
		$app_send_to = array();
		
		if(!isset($_POST['reports_id'])) $_POST['reports_id'] = 0;
		
		if($_POST['reports_id']>0)
		{
			$processes = new processes($app_process_info['entities_id']);
		}
		else
		{
			$processes = new processes($current_entity_id);
			$processes->items_id = $current_item_id;
		}
		
		//check access to process
		$check = false;
		foreach($processes->get_buttons_list() as $button)
		{
			if($button['id']==$app_process_info['id']) $check = true;
		}
		
		if(!$check)
		{	
			redirect_to('dashboard/access_forbidden');
		}
		
		$processes->run($app_process_info,(isset($_POST['reports_id']) ? _post::int('reports_id'):false));								
		break;
}