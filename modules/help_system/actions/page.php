<?php

$page_id = _get::int('id');

$page_info_query = db_query("select * from app_help_pages where type='page' and id='" . $page_id . "' and find_in_set(" . $app_user['group_id'] . ", users_groups) and is_active=1");
if(!$page_info = db_fetch_array($page_info_query))
{
	redirect_to('dashboard/page_not_found');
}