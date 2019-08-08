<?php


$entities_cfg = new entities_cfg(_get::int('entities_id'));

switch($app_module_action)
{
	case 'set_default_status':
		$entities_cfg->set('default_filter_panel_status',$_POST['status']);
		exit();
		break;
				
	case 'set_default_panel_access':
		$entities_cfg->set('default_filter_panel_access',(isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''));
		redirect_to('filters_panels/panels','entities_id=' . $_GET['entities_id']);
		break;
		
	case 'set_listing_config_access':
		$entities_cfg->set('listing_config_access',(isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''));
		redirect_to('filters_panels/panels','entities_id=' . $_GET['entities_id']);
		break;
				
	case 'save':		
		$sql_data = array(
		'position'=>$_POST['position'],		
		'entities_id'=>$_GET['entities_id'],
		'is_active'=>(isset($_POST['is_active']) ? 1:0),
		'is_active_filters'=>(isset($_POST['is_active_filters']) ? 1:0),		
		'users_groups'=>(isset($_POST['users_groups']) ? implode(',',$_POST['users_groups']):''),
		'width'=>$_POST['width'],
		'sort_order'=>$_POST['sort_order'],		
		);
	
	
		if(isset($_GET['id']))
		{
			db_perform('app_filters_panels',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_filters_panels',$sql_data);
		}
	
		redirect_to('filters_panels/panels','entities_id=' . $_GET['entities_id']);
	
		break;
		
	case 'delete':
		
		db_delete_row('app_filters_panels',_get::int('id'));
		db_delete_row('app_filters_panels_fields',_get::int('id'),'panels_id');
	
		redirect_to('filters_panels/panels','entities_id=' . $_GET['entities_id']);
		break;		
}
