<?php

//check if report exist
$reports_info_query = db_query("select * from app_reports where id='" . db_input(_get::int('use_filters')). "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
	$alerts->add(TEXT_REPORT_NOT_FOUND,'error');
	redirect_to('dashboard/');
}

switch($app_module_action)
{
	case 'use':
		
		$app_current_users_filter[_get::int('reports_id')] = $reports_info['name'];
		
		//rset current filters
		db_query("delete from app_reports_filters where reports_id='" . db_input(_get::int('reports_id')) . "'");
		
		$filters_query = db_query("select * from app_reports_filters where  reports_id='" . db_input(_get::int('use_filters')) . "'");
		while($filters = db_fetch_array($filters_query))
		{
			$sql_data = array('reports_id'=>_get::int('reports_id'),
					'fields_id'=>$filters['fields_id'],
					'filters_values'=> $filters['filters_values'],
					'filters_condition'=> $filters['filters_condition'],
					'is_active'=>$filters['is_active'],
			);
			db_perform('app_reports_filters',$sql_data);
		}
		
		//update current report
		db_query("update app_reports set fields_in_listing='" . $reports_info['fields_in_listing']. "', listing_order_fields='" . $reports_info['listing_order_fields'] . "' where id='" . _get::int('reports_id') . "'");
		
			
		switch($app_redirect_to)
		{
			case 'listing':
				redirect_to('items/items','path=' . $app_path);
				break;
			case 'report':
				redirect_to('reports/view','reports_id=' . $_GET['reports_id']);
				break;
		}
		
		break;
}		

exit();