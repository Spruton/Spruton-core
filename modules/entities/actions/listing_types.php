<?php


//autocreate listing types if not exist
listing_types::prepare_types(_get::int('entities_id'));

switch($app_module_action)
{
	case 'save':
		$sql_data = array(		
			'is_active' => (isset($_POST['is_active']) ? 1 : 0),		
			'is_default' => (isset($_POST['is_default']) ? 1 : 0),
			'width' => (isset($_POST['width']) ? $_POST['width'] : ''),
		);
		
		//reset is_default flag
		if(isset($_POST['is_default']))
		{
			db_query("update app_listing_types set is_default=0 where entities_id ='" . db_input($_GET['entities_id']) . "'");
		}
		
		if(isset($_GET['id']))
		{
			db_perform('app_listing_types',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_listing_types',$sql_data);
		}
		
		//check is_defatul flag
		$check_query = db_query("select * from app_listing_types where entities_id ='" . db_input($_GET['entities_id']) . "' and is_default=1");
		if(!$check = db_fetch_array($check_query))
		{
			db_query("update app_listing_types set is_default=1 where entities_id ='" . db_input($_GET['entities_id']) . "' and type='table'");
		}

		redirect_to('entities/listing_types', 'entities_id=' . $_GET['entities_id']);
		break;
}
