<?php

//check if report exist  
$reports_info_query = db_query("select * from app_reports where id='" . db_input((int)$_GET['reports_id']). "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{  
  $alerts->add(TEXT_REPORT_NOT_FOUND,'error');
  redirect_to('reports/');
}

//check report access
if($reports_info['reports_type']=='common')
{
  //check access for common report
  $check_query = db_query("select r.* from app_reports r, app_entities e, app_entities_access ea  where r.id = '" . $reports_info['id'] . "' and  r.entities_id = e.id and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($app_user['group_id']) . "' and (find_in_set(" . $app_user['group_id'] . ",r.users_groups) or find_in_set(" . $app_user['id'] . ",r.assigned_to)) and r.reports_type = 'common' order by r.dashboard_sort_order, r.name");
  if(!$check = db_fetch_array($check_query))
  {
    redirect_to('dashboard/access_forbidden');
  }
}
elseif($app_logged_users_id!=$reports_info['created_by'])
{  
  redirect_to('dashboard/access_forbidden');
}

//get report entity info
  $entity_info = db_find('app_entities',$reports_info['entities_id']);
  $entity_cfg = new entities_cfg($reports_info['entities_id']);
     
//get page title
if($reports_info['reports_type']=='entity_menu')
{	
  $page_title = (strlen($entity_cfg->get('listing_heading'))>0 ? $entity_cfg->get('listing_heading') : $entity_info['name']);
  
  if(!filters_panels::has_any($reports_info['entities_id'], $entity_cfg))
  {  	
  	//use default filters if there is no any filters panes stup
  	$default_reports_query = db_query("select * from app_reports where entities_id='" . db_input($reports_info['entities_id']). "' and reports_type='default'");
  	if(db_num_rows($default_reports_query))
  	{
  		$default_reports_info = db_fetch_array($default_reports_query);
  		$force_filters_reports_id = $default_reports_info['id'];
  	}
  }
}
else
{
  $page_title = $reports_info['name'];
}

$app_title = app_set_title($page_title);

switch($app_module_action)
{
	case 'set_listing_type':
		$reports_info_query = db_query("select id from app_reports where id='" . _get::int('reports_id'). "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			db_query("update app_reports set listing_type='" . db_input($_GET['type']) . "' where id='" . $reports_info['id'] . "'");
		}
		 
		redirect_to('reports/view','reports_id=' . _get::int('reports_id'));
		break;
}