<?php

class access_groups
{
	public static function get_access_view_value($access_schema)
	{
		switch(true)
		{
			case in_array('action_with_assigned',$access_schema):
				$view_access = 'action_with_assigned';
				break;
			case in_array('view_assigned',$access_schema):
				$view_access = 'view_assigned';
				break;
			case in_array('view',$access_schema):
				$view_access = 'view';
				break;
			default:
				$view_access = '';
				break;
		}
		
		return $view_access;
	}
	
	static function prepare_entities_access_schema($access_schema)
	{
		if((in_array('view_assigned',$access_schema) or in_array('action_with_assigned',$access_schema)) and !in_array('view',$access_schema))
		{
			$access_schema[] = 'view';
		}
		
		//check with selected
		if(in_array('update_selected',$access_schema) and !in_array('update',$access_schema))
		{
			$access_schema[] = 'update';
		}
		
		if(in_array('delete_selected',$access_schema) and !in_array('delete',$access_schema))
		{
			$access_schema[] = 'delete';
		}
		
		if(in_array('delete_creator',$access_schema) and !in_array('delete',$access_schema))
		{
			$access_schema[] = 'delete';
		}
		
		if(in_array('export_selected',$access_schema) and !in_array('export',$access_schema))
		{
			$access_schema[] = 'export';
		}
		
		return $access_schema;
		
	}
	
	public static function get_access_view_choices()
	{
		$choices = array(
				'' => TEXT_NO,
				'view' => TEXT_VIEW_ACCESS,
				'view_assigned' => TEXT_VIEW_ASSIGNED_ACCESS,
				'action_with_assigned' => TEXT_VIEW_ALL_ACTION_WIDHT_ASSIGNED_ACCESS,
		);
		
		return $choices;
	}
	
	public static function get_access_choices()
	{
		$access_choices = array(
				'create' => TEXT_CREATE_ACCESS,
				'update' => TEXT_UPDATE_ACCESS,				
		);
		
		//extra access available in extension
	  if(is_ext_installed())
	  {
	 	  $access_choices += array(	 			
	 			'update_selected' => TEXT_UPDATE_SELECTED_ACCESS,
	 	  	'copy' => TEXT_COPY_RECORDS,
	 	  	'move' => TEXT_MOVE_RECORDS,
	 	  	'repeat' => TEXT_EXT_REPEAT,
	 	  );
	  }	
		
	  $access_choices += array(
				'delete' => TEXT_DELETE_ACCESS,
				'delete_selected' => TEXT_DELETE_SELECTED_ACCESS,
	  		'delete_creator' => TEXT_DELETE_BY_CREATOR_ONLY,
				'export' => TEXT_EXPORT_ACCESS,
				'export_selected' => TEXT_EXPORT_SELECTED_ACCESS,
	  		'import' => TEXT_IMPORT,
				'reports' => TEXT_REPORTS_CREATE_ACCESS,
		);
		
		return $access_choices;
	}
	
	
  public static function get_ldap_default_group_id()
  {
    $group_info_query = db_query("select id from app_access_groups where is_ldap_default=1");
    if($group_info = db_fetch_array($group_info_query))
    {
      return $group_info['id'];
    }
    else
    {
      return false;
    }
  }
  
  public static function get_default_group_id()
  {
    $group_info_query = db_query("select id from app_access_groups where is_default=1");
    if($group_info = db_fetch_array($group_info_query))
    {
      return $group_info['id'];
    }
    else
    {
      return false;
    }
  }
  
  public static function get_name_by_id($id)
  {
  	global $app_access_groups_cache;
  	
    if($id==0)
    {
      return TEXT_ADMINISTRATOR;
    }
    else
    {
    	if(isset($app_access_groups_cache[$id]))
    	{
    		return $app_access_groups_cache[$id];
    	}
    	else 
    	{
    		return '';
    	}      
    }
  }
  
  static function get_name_by_id_list($list)
  {
  	  	
  	if(!is_array($list)) $list = explode(',',$list);
  	
  	$users_groups = [];
  	
  	foreach($list as $id)
  	{
  		$users_groups[] = self::get_name_by_id($id);
  	}
  	
  	return $users_groups;
  	
  }
  
  public static function check_before_delete($id)
  {
    if(($coutn = db_count('app_entity_1',$id,'field_6'))>0)
    {
      return sprintf(TEXT_ERROR_DELETE_USER_GROUP,$coutn);
    }
    else
    {
      return '';
    }
  }
  
  public static function get_choices($include_administrator = true)
  {
    $choices = array();
    
    if($include_administrator)
    {
      $choices[0] = TEXT_ADMINISTRATOR;
    }
    
    $groups_query = db_fetch_all('app_access_groups','','sort_order, name');
    while($v = db_fetch_array($groups_query))
    {
      $choices[$v['id']] = $v['name'];
    }
    
    return $choices;
  }
  
  public static function get_cache()
  {
    $cache = array();
    
    if(defined('TEXT_ADMINISTRATOR'))
    	$cache[0] = TEXT_ADMINISTRATOR;
    
    $groups_query = db_fetch_all('app_access_groups','','sort_order, name');
    while($v = db_fetch_array($groups_query))
    {
      $cache[$v['id']] = $v['name'];
    }
    
    return $cache;
  }
}