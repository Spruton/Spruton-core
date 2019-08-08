<?php

if(!IS_AJAX)
{
	exit();
}	

//check if field exist
$field_query = db_query("select * from app_fields where id='" . _get::int('field_id') . "' and type='fieldtype_related_records'");
if(!$field = db_fetch_array($field_query))
{
	exit();
}

$cfg = new fields_types_cfg($field['configuration']);

//check entity access;
if($cfg->get('entity_id')!=_get::int('entity_id'))
{
	exit();
}	


switch($app_module_action)
{
	case 'select_items':
				
		$parent_entity_item_id = _get::int('parent_entity_item_id');
		 				 
		$entity_info = db_find('app_entities',$cfg->get('entity_id'));
		$field_entity_info = db_find('app_entities',$field['entities_id']);
		 
		$choices = array();
		 
		 
		$listing_sql_query = 'e.id>0 ';
		$listing_sql_query_order = '';
		$listing_sql_query_join = '';
		$listing_sql_query_having = '';
		$listing_sql_select = '';
		 
		$parent_entity_item_is_the_same = false;
		 
		//if parent entity is the same then select records from paretn items only
		if($parent_entity_item_id>0 and $entity_info['parent_id']>0 and $entity_info['parent_id']==$field_entity_info['parent_id'])
		{
			$parent_entity_item_is_the_same = true;
				
			$listing_sql_query .= " and e.parent_item_id='" . db_input($parent_entity_item_id) . "'";
		}
		//if paretn is different then check level branch
		elseif($parent_entity_item_id>0 and $entity_info['parent_id']>0 and $entity_info['parent_id']!=$field_entity_info['parent_id'])
		{
			$listing_sql_query = $listing_sql_query . fieldtype_entity::prepare_parents_sql($parent_entity_item_id,$entity_info['parent_id'],$field_entity_info['parent_id']);
		}
		
		if(isset($_GET['search']))
		{
			$items_search = new items_search($entity_info['id']);
			$items_search->set_search_keywords($_GET['search']);
			
			if(is_array($cfg->get('fields_for_search')))
			{
				$search_fields = [];
				foreach($cfg->get('fields_for_search') as $id)
				{
					$search_fields[] = ['id'=>$id];
				}
				$items_search->search_fields = $search_fields;
			}
			
			$listing_sql_query .= $items_search->build_search_sql_query();
		}
		 
				
		$listing_sql_query_order .= " order by e.id";
					
		
		//prepare formula query
		if(strlen($heading_template = $cfg->get('heading_template')))
		{	
			if(preg_match_all('/\[(\d+)\]/',$heading_template,$matches))
			{					
				$listing_sql_select = fieldtype_formula::prepare_query_select($cfg->get('entity_id'), '',false,array('fields_in_listing'=>implode(',',$matches[1])));
			}
		}
		
		$results = [];
		$listing_sql = "select  e.* " . $listing_sql_select . " from app_entity_" . $cfg->get('entity_id') . " e "  . $listing_sql_query_join . " where " . $listing_sql_query . $listing_sql_query_having . $listing_sql_query_order;
		
		$listing_split = new split_page($listing_sql,'','query_num_rows', 30);
		$items_query = db_query($listing_split->sql_query, false);
		while($item = db_fetch_array($items_query))
		{
			$template = fieldtype_entity_ajax::render_heading_template($item, $entity_info, $field_entity_info, $cfg);
			
			$results[] = ['id'=>$item['id'],'text'=>$template['text'],'html'=>$template['html']];
		}
		
		$response = ['results'=>$results];
		
		if($listing_split->number_of_pages!=$_GET['page'] and $listing_split->number_of_pages>0)
		{
			$response['pagination']['more'] = 'true';
		}
		
		echo json_encode($response);
		
		exit();
		
		break;
	
}