<?php

//check access
if($app_user['group_id']>0 and ($app_previously_logged_user!=_get::int('users_id')))
{
	redirect_to('dashboard/access_forbidden');
}

$user_info_query = db_query("select * from app_entity_1 where id='" . _get::int('users_id') . "' and field_5=1");
if(!$user_info = db_fetch_array($user_info_query))
{
	redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{
	case 'login':
		$app_previously_logged_user = $app_logged_users_id;
		$app_logged_users_id = $user_info['id'];		
		redirect_to('dashboard/dashboard');
		break;
	case 'login_back':
		$app_previously_logged_user = 0;
		$app_logged_users_id = $user_info['id'];
		redirect_to('items/items','path=1');
		break;
}