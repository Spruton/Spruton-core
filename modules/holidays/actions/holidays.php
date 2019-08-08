<?php

if (!app_session_is_registered('holidays_filter'))
{
	$holidays_filter = date('Y');
	app_session_register('holidays_filter');
}

switch($app_module_action)
{
	case 'set_holidays_filter':
		$holidays_filter = $_POST['holidays_filter'];
	
		redirect_to('holidays/holidays');
		break;
	case 'save':
		$sql_data = array(
			'name'=>$_POST['name'],
			'start_date'=>$_POST['start_date'],
			'end_date'=>$_POST['end_date'],
		);

		if(isset($_GET['id']))
		{
			db_perform('app_holidays',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_holidays',$sql_data);
		}

		redirect_to('holidays/holidays');
		break;
	case 'delete':
		if(isset($_GET['id']))
		{
			db_delete_row('app_holidays', _get::int('id'));
			
			redirect_to('holidays/holidays');
		}
		break;
}