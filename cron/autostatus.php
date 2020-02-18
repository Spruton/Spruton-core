<?php

chdir(substr(__DIR__,0,-5));

define('IS_CRON',true);

//load core
require('includes/application_core.php');

//include ext plugins
if(is_file('plugins/ext/application_core.php'))
{	
	require('plugins/ext/application_core.php');
}

//load app lng
if(is_file($v = 'includes/languages/' . CFG_APP_LANGUAGE))
{
	require($v);
}

if(is_file($v = 'plugins/ext/languages/' . CFG_APP_LANGUAGE))
{
	require($v);
}

//dynamic fields that can be using in autostatus filters
$dynamic_fields = [
		'fieldtype_input_date',
		'fieldtype_input_datetime',
		'fieldtype_hours_difference',
		'fieldtype_days_difference',		
		'fieldtype_formula',
		'fieldtype_mysql_query',
		'fieldtype_dynamic_date',
		'fieldtype_date_added',
		'fieldtype_date_updated',
];

//autostatus fields to update
$autostatus_fields = array();

//all filters fields can be included in formula
$filters_fields = array();

//link choices id to reprts id
$choices_to_reports_id = array();

$fields_query = db_query("select * from app_fields where type='fieldtype_autostatus'");
while($fields = db_fetch_array($fields_query))
{
	$cfg = new fields_types_cfg($fields['configuration']);
	
	$has_dynamic_fields = false;
	
	foreach(fields_choices::get_tree($fields['id']) as $choices)
	{
		$reports_info_query = db_query("select * from app_reports where entities_id='" . $fields['entities_id']. "' and reports_type='fields_choices" . $choices['id'] . "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			$choices_to_reports_id[$choices['id']]=$reports_info['id'];
			
			$reports_filters_query = db_query("select f.id, f.type from app_reports_filters rf, app_fields f where reports_id='" . $reports_info['id'] . "' and rf.fields_id=f.id");
			if($reports_filters = db_fetch_array($reports_filters_query))
			{
				if(in_array($reports_filters['type'],$dynamic_fields))
				{
					$has_dynamic_fields=true;
				}
				
				$filters_fields[] = $reports_filters['id'];
			}
		}
	}
	
	if($has_dynamic_fields)
	{
		$autostatus_fields[] = [
				'id'=>$fields['id'],
				'entities_id' =>$fields['entities_id'],
				'choices' => fields_choices::get_tree($fields['id']),
				'cfg' => $cfg,								
		];		
	}
}

//echo '<pre>';
//print_r($choices_to_reports_id);
//print_r($autostatus_fields);
//print_r($filters_fields);

if(count($autostatus_fields))
{	 
	foreach($autostatus_fields as $autostatus_field)
	{
		$exclude_items  = array();
		
		foreach($autostatus_field['choices'] as $choices)
		{
			if(isset($choices_to_reports_id[$choices['id']]))
			{	
				$reports_id = $choices_to_reports_id[$choices['id']];
				$entities_id = $autostatus_field['entities_id'];
				
				$sql_query_having = array();
					
				$listing_sql_query = reports::add_filters_query($reports_id,'');
		
				//prepare having query for formula fields
				if(isset($sql_query_having[$entities_id]))
				{
					$listing_sql_query .= reports::prepare_filters_having_query($sql_query_having[$entities_id]);
				}
		
				//select items to update for current condition
				$update_items = array();
				$item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($entities_id, '',false,array('fields_in_listing'=>implode(',',$filters_fields)))  . fieldtype_related_records::prepare_query_select($entities_id, ''). " from app_entity_" . $entities_id . " e where e.id>0 " . $listing_sql_query . (count($exclude_items) ? " and e.id not in (" . implode(',',$exclude_items). ")":''),false);
				while($item_info = db_fetch_array($item_info_query))
				{
					//update item if has different value
					if($item_info['field_' . $autostatus_field['id']] != $choices['id'])
					{
						$update_items[] = $item_info['id'];
					}
					
					//exclude update items for next check
					$exclude_items[] = $item_info['id'];
				}
				
				//if has itesm to update
				if(count($update_items))
				{			  	
					$sql_data = array(
							'field_' . $autostatus_field['id'] => $choices['id']
					);
			  	
					db_perform('app_entity_' . $entities_id,$sql_data,'update',"id in (" . implode(',',$update_items) . ")");			  											
				}
			}
		}
	}
}