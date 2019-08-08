<?php

class user_roles
{
	static function get_access_by_role($entities_id,$items_id, $check_entities_id = false)
	{
		global $app_user;
		
		//admin have full access
		if($app_user['group_id']==0) return false;
		
		//get itesm full path
		$path_array = items::get_path_array($entities_id, $items_id);
		
		//print_rr($path_array);
		
		//search entity where there is fieldtype_user_roles		
		foreach($path_array as $info)
		{
			$fields_list = [];
			$fields_query = db_query("select id from app_fields where entities_id='"  . $info['entities_id'] . "' and type='fieldtype_user_roles'");
			while($fields = db_fetch_array($fields_query))
			{
				$fields_list[] = $fields['id'];
			}
			
			if(count($fields_list)) break;
		}
		
		//exit if no any roles
		if(!count($fields_list)) return false;
		
		$roles_info = false;
		
		//check if there is assigned roles to item		
		$assigend_role_query = db_query("select * from app_user_roles_to_items where fields_id in (" . implode(',',$fields_list) . ") and entities_id='" . $info['entities_id'] . "' and items_id='" . $info['items_id'] . "' and users_id='" . $app_user['id'] . "'");
		if($assigend_role = db_fetch_array($assigend_role_query))
		{
			$roles_entities_access=[]; 
			$roles_entities_comments_access=[];
			$roles_entities_fields_access=[];
			
			//get roles access
			$roles_access_query = db_query("select * from app_user_roles_access where fields_id='" . $assigend_role['fields_id'] . "' and user_roles_id='" . $assigend_role['roles_id'] . "'");
			while($roles_access = db_fetch_array($roles_access_query))
			{
				$roles_entities_access[$roles_access['entities_id']] = (strlen($roles_access['access_schema']) ? explode(',',$roles_access['access_schema']) : []);
				
				$roles_entities_comments_access[$roles_access['entities_id']] = (strlen($roles_access['comments_access']) ? explode(',',$roles_access['comments_access']) : []);
				
				$roles_entities_fields_access[$roles_access['entities_id']] = (strlen($roles_access['fields_access']) ? json_decode($roles_access['fields_access'],true) : []);
			}	
			
			//print_r($roles_entities_access);
			
			if(count($roles_entities_access))
			{
				//global roles access for entitites list
				$roles_info['roles_entities_access'] = $roles_entities_access;
				
				//to rewrite curren entity access
				if($check_entities_id)
				{
					foreach($roles_entities_access as $entity_id=>$access)
					{
						if($check_entities_id == $entity_id)
						{
							$roles_info['current_access_schema'] = $access;
							$roles_info['current_comments_access_schema'] = $roles_entities_comments_access[$entity_id];
							$roles_info['fields_access_schema'] = $roles_entities_fields_access[$entity_id];							
						}	
					}
				}	
			}
		}
		
		//print_r($fields_list);
		//print_r($roles_info);
		//exit();
		
		return $roles_info;
	}
	
	static function get_role_to_items($fields_id, $entities_id, $items_id, $users_id)
	{
		$role_query = db_query("select roles_id from app_user_roles_to_items where fields_id='" . $fields_id . "' and  entities_id='" . $entities_id . "' and items_id='" . $items_id . "' and users_id='" . $users_id . "'");
		if($role = db_fetch_array($role_query))
		{
			return $role['roles_id'];
		}
		else 
		{
			return false;
		}
	}
	
	static function get_choices($fields_id, $add_empty = true)
	{
		$choices = [];
		 
		if($add_empty)
		{
			$choices[''] = '';
		}
		 
		$roles_query = db_query("select * from app_user_roles where fields_id='" . db_input($fields_id) . "' order by sort_order, name");
		while($roles = db_fetch_array($roles_query))
		{
			$choices[$roles['id']] = $roles['name'];
		}
		 
		return $choices;
	}
	
	static function get_name_by_id($id)
	{
		$info_query = db_query("select * from app_user_roles where id='" . (int)$id . "'");
		if($info = db_fetch_array($info_query))
		{
			return $info['name'];
		}
		
		return '';
	}
}