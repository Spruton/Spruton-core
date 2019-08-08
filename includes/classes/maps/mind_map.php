<?php

class mind_map
{
	private $entities_id, $items_id, $fields_id, $fields_access_schema;
	
	function __construct($entities_id, $items_id, $fields_id)
	{
		global $app_user;
		
		$this->entities_id = $entities_id;
		$this->items_id = $items_id;
		$this->fields_id = $fields_id;
		
		$this->fields_access_schema = users::get_fields_access_schema($this->entities_id,$app_user['group_id']);
	}
	
	function is_report()
	{
		return false;
	}
	
	function has_access()
	{
		global $app_user;
		
		if($app_user['group_id']==0)
		{
			return true;
		}
		
		if(isset($this->fields_access_schema[$this->fields_id]))
		{
			if($this->fields_access_schema[$this->fields_id]=='hide')
			{
				return false;
			}
		}
		
		return true;
		
	}
	
	function is_editable()
	{
		global $app_user;
		
		if($app_user['group_id']==0)
		{
			return 1;
		}
		else
		{												
			if(isset($this->fields_access_schema[$this->fields_id]))
			{
				return 0;
			}
			else
			{
				return 1;
			}
		}
	}
	
	function save($data)
	{		
		$this->save_root($data);
		
		$this->save_children($data);
		
		$this->prepare_deleted_items($data);
	}
	
	function prepare_deleted_items($data)
	{
		$chilider_list = $this->get_childer_list($data);
							
		$mm_query = db_query("select id from app_mind_map where " . (count($chilider_list) ? " mm_id not in (" . implode(',',$chilider_list). ") and  length(mm_parent_id)>0 and " : '') . " entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "'");
		while($mm = db_fetch_array($mm_query))
		{
			db_query("delete from app_mind_map where id='" . $mm['id']. "'");				
		}
		
	}
	
	function get_childer_list($data, $chilider_list = array())
	{
		if(isset($data['children']))
		{
			foreach($data['children'] as $children)
			{
				$chilider_list[] = "'" . $children['id'] . "'";
	
				$chilider_list = $this->get_childer_list($children,$chilider_list);
			}
		}
	
		return $chilider_list;
	}
	
	function save_root($data)
	{
		$sql_data = array(				
				'mm_id' => $data['id'],
				'mm_text' => strip_tags($data['text']),
				'mm_layout' => $data['layout'],
				'mm_shape' => (isset($data['shape']) ? $data['shape'] : ''),
				'mm_icon' => (isset($data['icon']) ? $data['icon'] : ''),
				'mm_color' => (isset($data['color']) ? $data['color'] : ''),
				'mm_value' => (isset($data['value']) ? $data['value'] : ''),
		);
		
		$mm_query = db_query("select id from app_mind_map where mm_id='" . $data['id']. "' and entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "'");
		if($mm = db_fetch_array($mm_query))
		{
			db_perform('app_mind_map', $sql_data,'update',"id='" . $mm['id'] . "'");
		}
		else
		{
			$sql_data['entities_id'] = $this->entities_id;
			$sql_data['items_id'] = $this->items_id;
			$sql_data['fields_id'] = $this->fields_id;
			
			db_perform('app_mind_map', $sql_data);
		}
	}
	
	function save_children($data)
	{
		if(isset($data['children']))
		{
			$sort_order = 0;
			foreach($data['children'] as $children)
			{
				$sql_data = array(
						'mm_id' => $children['id'],
						'mm_parent_id' => $data['id'],
						'mm_text' => strip_tags($children['text']),
						'mm_layout' => (isset($children['layout']) ? $children['layout'] : ''),						
						'mm_shape' => (isset($children['shape']) ? $children['shape'] : ''),
						'mm_side' => (isset($children['side']) ? $children['side'] : ''),
						'mm_icon' => (isset($children['icon']) ? $children['icon'] : ''),
						'mm_color' => (isset($children['color']) ? $children['color'] : ''),
						'mm_value' => (isset($children['value']) ? $children['value'] : ''),
						'mm_collapsed' => (isset($children['collapsed']) ? $children['collapsed'] : ''),
						'sort_order' => $sort_order,
				);
				
				$mm_query = db_query("select id from app_mind_map where mm_id='" . $children['id']. "' and entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "'");
				if($mm = db_fetch_array($mm_query))
				{
					db_perform('app_mind_map', $sql_data,'update',"id='" . $mm['id'] . "'");
				}
				else
				{
					$sql_data['entities_id'] = $this->entities_id;
					$sql_data['items_id'] = $this->items_id;
					$sql_data['fields_id'] = $this->fields_id;
						
					db_perform('app_mind_map', $sql_data);
				}
				
				$sort_order++;
				
				$this->save_children($children);				
			}
		}
	}
	
	function get_json()
	{
		if(count($tree = $this->get_tree()))
		{						
			return json_encode($tree, JSON_NUMERIC_CHECK );
		}
		else
		{
			return '';
		}
	}
	
	function get_tree($data = array(), $mm_parent_id='')
	{
		$count = 0;
		
		$mm_query = db_query("select * from app_mind_map where mm_parent_id='" . $mm_parent_id. "' and entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "' order by sort_order");
		while($mm = db_fetch_array($mm_query))
		{
			if(strlen($mm_parent_id))
			{
				$data[$count] = array(
						'id' => $mm['mm_id'],
						'text' => str_replace(array("'",'"'),array('&apos;','&quot;'),$mm['mm_text']),						
				);
				
				if(strlen($mm['mm_layout']))
				{
					$data[$count]['layout'] = $mm['mm_layout'];
				}
				
				if(strlen($mm['mm_shape']))
				{
					$data[$count]['shape'] = $mm['mm_shape'];
				}
				
				if(strlen($mm['mm_side']))
				{
					$data[$count]['side'] = $mm['mm_side'];
				}
				
				if(strlen($mm['mm_icon']))
				{
					$data[$count]['icon'] = $mm['mm_icon'];
				}
				
				if(strlen($mm['mm_color']))
				{
					$data[$count]['color'] = $mm['mm_color'];
				}
				
				if(strlen($mm['mm_collapsed']))
				{
					$data[$count]['collapsed'] = $mm['mm_collapsed'];
				}
				
				if(strlen($mm['mm_value']))
				{
					$data[$count]['value'] = $mm['mm_value'];
				}
				
				$check_query = db_query("select id from app_mind_map where mm_parent_id='" . $mm['mm_id']. "' and entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "' limit 1");
				if($check = db_fetch_array($check_query))
				{
					$data[$count]['children'] = $this->get_tree(array(),$mm['mm_id']);
				}
												
				$count++;
			}
			else
			{
				
				$data['root'] = array(
						'id' => $mm['mm_id'],						
						'text' => str_replace(array("'",'"'),array('&apos;','&quot;'),$mm['mm_text']),
						'layout' => $mm['mm_layout'],
				);
				
				if(strlen($mm['mm_shape']))
				{
					$data['root']['shape'] = $mm['mm_shape'];
				}
				
				if(strlen($mm['mm_icon']))
				{
					$data['root']['icon'] = $mm['mm_icon'];
				}
				
				if(strlen($mm['mm_color']))
				{
					$data['root']['color'] = $mm['mm_color'];
				}
				
				if(strlen($mm['mm_value']))
				{
					$data[$count]['value'] = $mm['mm_value'];
				}
				
				$check_query = db_query("select id from app_mind_map where mm_parent_id='" . $mm['mm_id']. "' and entities_id='" . $this->entities_id . "' and items_id='" . $this->items_id . "' and fields_id='" . $this->fields_id . "' limit 1");
				if($check = db_fetch_array($check_query))
				{
					$data['root']['children'] = $this->get_tree(array(),$mm['mm_id']);					
				}
			}
		}
		
		return $data;
	}
	
	static function delete($entities_id,$items_id)
	{
		db_query("delete from app_mind_map where entities_id='" . $entities_id . "' and items_id='" . $items_id . "'");
	}
	
	static function delete_by_fields_id($entities_id,$fields_id)
	{
		db_query("delete from app_mind_map where entities_id='" . $entities_id . "' and fields_id='" . $fields_id . "'");
	}
	
}