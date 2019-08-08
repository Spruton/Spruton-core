<?php

$field_info = db_find('app_fields',_get::int('fields_id'));

$cfg = new fields_types_cfg($field_info['configuration']);

switch($app_module_action)
{
	case 'save':

		$sql_data = array(
			'fields_id'=>_get::int('fields_id'),
			'entities_id'=>_get::int('entities_id'),		
			'name'=>$_POST['name'],		
			'sort_order'=>$_POST['sort_order'],		
		);

		if(isset($_GET['id']))
		{			 
			db_perform('app_user_roles',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");			
		}
		else
		{
			db_perform('app_user_roles',$sql_data);			
		}
	
		redirect_to('entities/user_roles','entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id'));
		break;
	case 'delete':
		if(isset($_GET['id']))
		{		
			$info_query = db_query("select * from app_user_roles where id='" . _get::int('id') . "'");
			if($info = db_fetch_array($info_query))
			{
				db_query("delete from app_user_roles where id='" . db_input($info['id']) . "'");
				db_query("delete from app_user_roles_access where user_roles_id='" . db_input($info['id']) . "'");
			}				

			$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$info['name']),'success');
			
			redirect_to('entities/user_roles','entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id'));
		}
		break;
	case 'sort':
		$choices_sorted = $_POST['choices_sorted'];
	
		if(strlen($choices_sorted)>0)
		{
			$choices_sorted = json_decode(stripslashes($choices_sorted),true);
	
			$sort_order = 0;
	    foreach($choices_sorted as $v)
	    {
	      db_query("update app_user_roles set sort_order='" . $sort_order . "' where id='" . db_input($v['id']) . "' and fields_id='" . db_input(_get::int('fields_id')) . "'");
	        
	      $sort_order++;
	    }
		}
		 
		redirect_to('entities/user_roles','entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id'));
		break;
}