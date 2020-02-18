<?php

class records_visibility
{
	
	static function merget_fields_choices($entities_id)
	{
		global $app_entities_cache;
		
		$allowed_types = [
				'fieldtype_dropdown',
				'fieldtype_dropdown_multiple',
				'fieldtype_dropdown_multilevel',
				'fieldtype_tags',
				'fieldtype_checkboxes',
				'fieldtype_radioboxes',
				'fieldtype_entity',
				'fieldtype_entity_ajax',
				'fieldtype_entity_multilevel',
		];
		
		$choices = [];
		
		$users_fields_query = db_query("select id, name, type, configuration from app_fields where entities_id=1 and type in ('" . implode("','",$allowed_types). "')");
		while($users_fields = db_fetch_array($users_fields_query))
		{
			$users_cfg = new fields_types_cfg($users_fields['configuration']);
			
			$fields_query = db_query("select id, name, type, configuration from app_fields where entities_id='" . $entities_id . "' and type in ('" . implode("','",$allowed_types). "')");
			while($fields = db_fetch_array($fields_query))
			{
				$cfg = new fields_types_cfg($fields['configuration']);
				//echo $users_fields['name'] . ' ' . $cfg->get('use_global_list') . '==' . $fields['name'] . ': ' . $users_cfg->get('use_global_list') . '<br>';
				
				if(($cfg->get('entity_id')==$users_cfg->get('entity_id') and $cfg->get('entity_id')>0 and $users_cfg->get('entity_id')>0) 
						or ($cfg->get('use_global_list')==$users_cfg->get('use_global_list') and $cfg->get('use_global_list')>0 and $users_cfg->get('use_global_list')>0))
				{
					$choices[$users_fields['id'] . '-' . $fields['id']] = TEXT_USERS . ': ' . $users_fields['name'] . ' => ' . $app_entities_cache[$entities_id]['name'] . ': ' . $fields['name'];   
				}
			}
		}
		
		return $choices;
	}
	
	static function count_filters($rules_id)
	{
		$count = 0;
		$reports_info_query = db_query("select id from app_reports where reports_type='records_visibility" . $rules_id . "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			$count_query = db_query("select count(*) as total from app_reports_filters where reports_id='" . $reports_info['id'] . "'");
			$count = db_fetch_array($count_query);
				
			$count = $count['total'];
		}
	
		return $count;
	}
	
	static function add_access_query($entities_id)
	{
		global $app_user, $app_fields_cache;
		
		//print_rr($app_user);
		
		$sql = [];
		
		//skip admins
		if($app_user['group_id']==0) return '';
		
		$rules_query = db_query("select * from app_records_visibility_rules where is_active=1 and entities_id='" . $entities_id . "' and find_in_set(" . $app_user['group_id']. ",users_groups)");						
		while($rules = db_fetch_array($rules_query))
		{
			$listing_sql_query = "";
			
			$reports_info_query = db_query("select id from app_reports where reports_type='records_visibility" . $rules['id'] . "'");
			if($reports_info = db_fetch_array($reports_info_query))
			{
				$listing_sql_query = reports::add_filters_query($reports_info['id'],$listing_sql_query);								
			}
			
			if(strlen($rules['merged_fields']))
			{
				foreach(explode(',',$rules['merged_fields']) as $merged_fields)
				{
					$merged_fields = explode('-',$merged_fields);
					$users_fields_id = $merged_fields[0];
					$fields_id = $merged_fields[1];
															
					if(!isset($app_user['fields']['field_' . $users_fields_id])) continue;
					
					$value = $app_user['fields']['field_' . $users_fields_id];
					
					if(!strlen($value)) $value=0;
					
					if(in_array($app_fields_cache[$entities_id][$fields_id]['type'],['fieldtype_entity_multilevel']))
					{
						$listing_sql_query .= " and e.field_{$fields_id}='{$value}'";
					}
					else 
					{					
						$listing_sql_query .= " and (select count(*) from app_entity_" . $entities_id . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($fields_id)  . "' and cv.value in (" . $value . "))>0 ";
					}
				}
			}
			
			
			if(substr($listing_sql_query,0,3) == 'and') $listing_sql_query = substr($listing_sql_query,3);
			if(substr($listing_sql_query,0,4) == ' and') $listing_sql_query = substr($listing_sql_query,4);
			
			if(strlen($listing_sql_query))
			{
				$sql[] = $listing_sql_query;
			}
		}
		
		//print_r($sql);
		
		if(count($sql))
		{
			return " and ((" . implode(') or (', $sql). "))";
		}
		else
		{
			return '';
		}
		
	}
	
}