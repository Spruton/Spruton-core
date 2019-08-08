<?php

$listing_types_id = _get::int('listing_types_id');

$listing_types_query = db_query("select * from app_listing_types where id='" . _get::int('listing_types_id') . "'");
if(!$listing_types = db_fetch_array($listing_types_query))
{
	redirect_to('entities/listing_types','entities_id=' . _get::int('entities_id'));
}

switch($app_module_action)
{
	case 'save':
		$sql_data = array(
			'listing_types_id' => $listing_types['id'],
			'name'=>$_POST['name'],		
			'fields' => (isset($_POST['fields']) ? implode(',',$_POST['fields']) : ''),
			'display_field_names' => (isset($_POST['display_field_names']) ? 1 : 0),
			'sort_order'=>$_POST['sort_order'],			
			'text_align' => $_POST['text_align'],						
			'display_as' => $_POST['display_as'],
			'width' => (isset($_POST['width']) ? $_POST['width'] : ''),			
		);

		if(isset($_GET['id']))
		{
			db_perform('app_listing_sections',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{			
			db_perform('app_listing_sections',$sql_data);			
		}

		redirect_to('entities/listing_sections', 'listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id']);
		break;
	case 'delete':
		if(isset($_GET['id']))
		{			
			db_delete_row('app_listing_sections', _get::int('id'));
			
			redirect_to('entities/listing_sections', 'listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id']);
		}
		break;
	case 'sort':
		$choices_sorted = $_POST['choices_sorted'];
	
		if(strlen($choices_sorted)>0)
		{
			$choices_sorted = json_decode(stripslashes($choices_sorted),true);
						
			$sort_order = 1;
			foreach($choices_sorted as $v)
			{
				db_query("update app_listing_sections set sort_order={$sort_order} where id={$v['id']}");
				$sort_order++;
			}
		}
		 
		redirect_to('entities/listing_sections', 'listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id']);
		break;		
}