<?php

class listing_types
{
	
	static function get_types()
	{
		return ['table','list','grid','mobile'];
	}
	
	static function get_type_title($type)
	{
		switch($type)
		{
			case 'table':
				$title = TEXT_TABLE;
				break;
			case 'list':
				$title = TEXT_LIST;
				break;
			case 'grid':
				$title = TEXT_GRID;
				break;
			case 'mobile':
				$title = TEXT_MOBILE;
				break;
		}
		
		return $title;
	}
	
	//autocreate listing types if not exist	
	static function prepare_types($entities_id)
	{
		foreach(self::get_types() as $type)
		{
			$check_query = db_query("select * from app_listing_types where entities_id='" . $entities_id . "' and type='" . $type . "'");
			if(!$check = db_fetch_array($check_query))
			{
				$sql_data = [						
						'entities_id' => $entities_id,
						'type' => $type,
						'is_default' => $type=='table' ? 1: 0,
						'is_active' => $type=='table' ? 1: 0,
				];
				
				db_perform('app_listing_types', $sql_data);
			}
		}
	}
	
	static function get_default($entities_id)
	{
		$info_query = db_query("select * from app_listing_types where entities_id='" . $entities_id . "' and is_default=1 and is_active=1");
		if($info = db_fetch_array($info_query))
		{
			return $info['type'];
		}
		else
		{
			return 'table';
		}
	}
	
	static function has_mobile($entities_id)
	{
		$check_query = db_query("select id from app_listing_types where is_active=1 and type='mobile' and entities_id='" . $entities_id . "'");
		if($check = db_fetch_array($check_query))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	static function get_sections_next_order($listing_types_id)
	{
		$info_query = db_query("select max(sort_order) as max_sort_order from app_listing_sections where listing_types_id={$listing_types_id}");
		$info = db_fetch_array($info_query);
						
		return $info['max_sort_order']+1;
	}
	
	static function get_sections_align_choices()
	{
		$choices = [
				'left' => TEXT_ALIGN_LEFT,
				'center' => TEXT_ALIGN_CENTER,
				'right' => TEXT_ALIGN_RIGHT,
		];
		
		return $choices;
	}
	
	static function get_sections_display_choices()
	{
		$choices = [
				'list' => TEXT_TABLE,
				'inline' => TEXT_INLINE_LIST,				
		];
		
		return $choices;
	}
	
	static function get_sections_align_icon($align)
	{
		switch($align)
		{
			case 'left':
				$icon = '<i class="fa fa-align-left"></i>';
				break;
			case 'right':
				$icon = '<i class="fa fa-align-right"></i>';
				break;
			case 'center':
				$icon = '<i class="fa fa-align-center"></i>';
				break;
		}
		
		return $icon;
	}
	
	static function render_switches($reports_info,$listing_type)
	{
				
		$choices = [];
		$info_query = db_query("select type from app_listing_types where is_active=1 and type!='mobile' and entities_id='" . $reports_info['entities_id'] . "'");
		while($info = db_fetch_array($info_query))
		{
			$choices[] = $info['type'];
		}
				
						
		$html = '';
		
		if(count($choices)>1)
		{
			
			$html .= '<ul class="list-inline listing-types-switches">';
						
			foreach($choices as $type)
			{
				$icon = '';
				switch($type)
				{
					case 'table': 
						$icon = '<i class="fa fa-table"></i>';
						break;
					case 'list':
						$icon = '<i class="fa fa-list"></i>';
						break;
					case 'grid':
						$icon = '<i class="fa fa-th"></i>';
						break;
				}
				
				if(isset($_GET['path']))
				{
					$url = url_for('items/items','path=' . $_GET['path'] . '&action=set_listing_type&type=' . $type . '&reports_id=' .  $reports_info['id']);
				}
				else
				{
					$url = url_for('reports/view','action=set_listing_type&type=' . $type . '&reports_id=' .  $reports_info['id']);
				}
				
				$html .= '<li><a href="' . $url . '" class="btn btn-xs btn-default ' . ($listing_type==$type ? 'active':''). '">' . $icon . '</a></li>';
			}
			
			$html .= '</ul>';
						
		}
		
		return $html;
	}
	
	static function has_action_field($type,$entities_id)
	{
		$info_query = db_query("select id from app_listing_types where is_active=1 and type='" . $type . "' and entities_id='" . $entities_id . "'");
		if($info = db_fetch_array($info_query))
		{				
			$fields = array();
			$listing_sections_query = db_query("select fields from app_listing_sections where listing_types_id={$info['id']} order by sort_order, name");
			while($listing_sections = db_fetch_array($listing_sections_query))
			{
				if(strlen($listing_sections['fields']))
				{
					$fields = array_merge($fields,explode(',',$listing_sections['fields']));
				}
			}
			
			
			if(count($fields))
			{
				$check_query = db_query("select id from app_fields where id in (" . implode(',',$fields) . ") and type='fieldtype_action'");
				if($check = db_fetch_array($check_query))
				{					
					return true;
				}
			}
		}
					
		return false;
	}
	
	static function get_choices($entities_id)
	{
		$choices = [''=>TEXT_DEFAULT];
		$info_query = db_query("select type from app_listing_types where is_active=1 and type!='mobile' and entities_id='" . $entities_id . "'");
		while($info = db_fetch_array($info_query))
		{
			$choices[$info['type']] = self::get_type_title($info['type']);
		}
		
		return $choices;
	}
}