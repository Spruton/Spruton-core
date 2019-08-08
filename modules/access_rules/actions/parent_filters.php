<?php

switch($app_module_action)
{
	case 'save':

		$values = '';

		if(isset($_POST['values']))
		{
			if(is_array($_POST['values']))
			{
				$values = implode(',',$_POST['values']);
			}
			else
			{
				$values = $_POST['values'];
			}
		}
		$sql_data = array('reports_id'=>$_GET['reports_id'],
				'fields_id'=>$_POST['fields_id'],
				'filters_condition'=>$_POST['filters_condition'],
				'filters_values'=>$values,
		);

		if(isset($_GET['id']))
		{
			db_perform('app_reports_filters',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_reports_filters',$sql_data);
		}

		redirect_to('access_rules/fields','entities_id=' . $_GET['entities_id']);
		break;
	case 'delete':
		if(isset($_GET['id']))
		{

			db_query("delete from app_reports_filters where id='" . db_input($_GET['id']) . "'");

			$alerts->add(TEXT_WARN_DELETE_FILTER_SUCCESS,'success');
			 

			redirect_to('access_rules/fields','entities_id=' . $_GET['entities_id']);
		}
		break;
}