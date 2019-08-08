<?php

class approved_items
{
	static function is_approved_by_user($entities_id, $items_id, $fields_id, $users_id)
	{
		$check_query = db_query("select id, date_added from app_approved_items where entities_id='" . $entities_id . "' and items_id='" . $items_id . "' and fields_id='" . $fields_id . "' and users_id='" . $users_id . "'");
		if($check = db_fetch_array($check_query))
		{
			return $check;
		}
		else
		{
			return false;
		}
	}
	
	static function get_approved_users_by_field($entities_id, $items_id, $fields_id)
	{
		$users = [];
		
		$check_query = db_query("select users_id,signature from app_approved_items where entities_id='" . $entities_id . "' and items_id='" . $items_id . "' and fields_id='" . $fields_id . "'");
		while($check = db_fetch_array($check_query))
		{
			$users[$check['users_id']] = ['signature'=>$check['signature']];
		}
		
		return $users;
	}
	
	static function is_all_approved($entities_id, $items_id, $fields_id)
	{
		$item_info_query = db_query("select field_{$fields_id} from app_entity_{$entities_id} where id={$items_id}");
		if($item_info = db_fetch_array($item_info_query))
		{
			$check_query = db_query("select count(*) as total from app_approved_items where entities_id='" . $entities_id . "' and items_id='" . $items_id . "' and fields_id='" . $fields_id . "'");
			$check = db_fetch_array($check_query);
			
			if($check['total'] == count(explode(',',$item_info['field_' . $fields_id])))
			{				
				return true;
			}
		}
		
		return false;
	}
	
}