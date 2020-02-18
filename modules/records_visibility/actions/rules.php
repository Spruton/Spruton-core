<?php

switch($app_module_action)
{
	case 'save':
		$sql_data = array(
			'entities_id'=>_get::int('entities_id'),
			'is_active' => (isset($_POST['is_active']) ? 1:0),
			'users_groups'=>implode(',',$_POST['users_groups']),
			'merged_fields'=>(isset($_POST['merged_fields']) ? implode(',',$_POST['merged_fields']):''),
			'notes'=>$_POST['notes'],
		);

		if(isset($_GET['id']))
		{
			db_perform('app_records_visibility_rules',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_records_visibility_rules',$sql_data);
		}

		redirect_to('records_visibility/rules','entities_id=' . _get::int('entities_id'));
		break;
	case 'delete':
		if(isset($_GET['id']))
		{			
				db_delete_row('app_records_visibility_rules',_get::int('id'));	
				
				$report_info_query = db_query("select * from app_reports where reports_type='records_visibility" . db_input($_GET['id']). "'");
				if($report_info = db_fetch_array($report_info_query))
				{
					reports::delete_reports_by_id($report_info['id']);
				}

				$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,''),'success');
		}	

		redirect_to('records_visibility/rules','entities_id=' . _get::int('entities_id'));
		
		break;
}