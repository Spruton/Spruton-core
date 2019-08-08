<?php

class access_rules
{
	public $access_schema;
	public $fields_view_only_access;
	public $comments_access_schema;
	
	function __construct($entities_id, $item_info)
	{
		global $app_user, $app_access_rules_fields_cache;
		
		$this->access_schema = null;
		$this->fields_view_only_access = '';
		$this->comments_access_schema = null;
		
		//don't check rules for admin
		if($app_user['group_id']==0) return true;
							
		if(isset($app_access_rules_fields_cache[$entities_id]))
		{			
			$access_rules_fields = $app_access_rules_fields_cache[$entities_id];
			
			if(is_numeric($item_info))
			{
				$item_info = db_find('app_entity_' . $entities_id,$item_info);
			}
						
			if(isset($item_info['field_' . $access_rules_fields['fields_id']]))
			{								
				if(strlen($value = $item_info['field_' . $access_rules_fields['fields_id']]))
				{
					$access_rules_query = db_query("select * from app_access_rules where find_in_set(" . $app_user['group_id'] . ", users_groups) and find_in_set(" . $value . ",choices) and  entities_id='" . db_input($entities_id) . "' and fields_id='" . db_input($access_rules_fields['fields_id']) . "'");
					if($access_rules = db_fetch_array($access_rules_query))
					{						
						$this->access_schema = $access_rules['access_schema'];
						$this->fields_view_only_access = $access_rules['fields_view_only_access'];
						$this->comments_access_schema = $access_rules['comments_access_schema'];
					}
				}
			}
		}
	}
	
	//get rules cache
	static function get_access_rules_fields_cache()
	{
		$cache = array();
		$access_rules_fields_query = db_query("select * from app_access_rules_fields");
		while($access_rules_fields = db_fetch_array($access_rules_fields_query))
		{
			$cache[$access_rules_fields['entities_id']] = $access_rules_fields;
		}	
		
		return $cache;
	}
	
	//get rules access schema
	function get_access_schema()
	{
		global $current_access_schema;
		
		if(!isset($this->access_schema))
		{
			return null;
		}
			
		$access_schema = array();
		foreach($current_access_schema as $val)
		{
			if(!in_array($val, array('update','delete','export','copy','move')))
			{
				$access_schema[] = $val;
			}
		}
		
		if(strlen($this->access_schema))
		{						
			foreach(explode(',',$this->access_schema) as $val)
			{
				$access_schema[] = $val;
			}
									
			return $access_schema;
			
		}
		else
		{			
			return $access_schema;
		}								
	}
	
	//get fields veiw only access
	function get_fields_view_only_access()
	{
		if(strlen($this->fields_view_only_access))
		{	
			$fields_access_schema = array();
			
			foreach(explode(',',$this->fields_view_only_access) as $field_id)
			{
				$fields_access_schema[$field_id] = 'view';
			}
			
			return $fields_access_schema;
		}
		else
		{
			return array();
		}
	}
	
	//get comments access
	function get_comments_access_schema()
	{		
		if(!isset($this->comments_access_schema) or $this->comments_access_schema=='false')
		{
			return null;
		}
		
		$this->comments_access_schema = ($this->comments_access_schema=='no' ? '':$this->comments_access_schema);
			
		return (strlen($this->comments_access_schema) ? explode(',',$this->comments_access_schema) : array());
	}
	
	static function has_add_buttons_access($entities_id, $parent_item_id)
	{
		global $app_entities_cache, $sql_query_having;
		
		if($app_entities_cache[$entities_id]['parent_id']==0)
		{
			return true;
		}
		else 
		{
			$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($app_entities_cache[$entities_id]['parent_id']). "' and reports_type='hide_add_button_rules" . $entities_id . "'");
			if($reports_info = db_fetch_array($reports_info_query))
			{				
				//prepare forumulas query
				$listing_sql_query_select = fieldtype_formula::prepare_query_select($reports_info['entities_id'],'');
				
				$listing_sql_query = reports::add_filters_query($reports_info['id'],'');
																
				//prepare having query for formula fields				
				if(isset($sql_query_having[$reports_info['entities_id']]))
				{
					$listing_sql_query .= reports::prepare_filters_having_query($sql_query_having[$reports_info['entities_id']]);
				}
				
				//has access if not fitlers setup
				if(!strlen($listing_sql_query))
				{
					return true;
				}
				
				$listing_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . $reports_info['entities_id'] . " e where e.id='" . $parent_item_id . "' " . $listing_sql_query;
						
				$item_info_query = db_query($listing_sql);
				if($item_info = db_fetch_array($item_info_query))
				{					
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}
	}
		
}