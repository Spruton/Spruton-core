<?php

if(!IS_AJAX)
{
	exit();
}	

switch($app_module_action)
{
	case 'select_items':
								 				
		$entity_info = db_find('app_entities',_get::int('entity_id'));
				
		$listing_sql_query = 'e.id>0 ';
		$listing_sql_query_order = '';
		$listing_sql_query_join = '';
		$listing_sql_query_having = '';
		$listing_sql_select = '';
		 		
		if(isset($_GET['search']))
		{
			$items_search = new items_search($entity_info['id']);
			$items_search->set_search_keywords($_GET['search']);
									
			$listing_sql_query .= $items_search->build_search_sql_query();
		}
		
		//check view assigned only access
		$listing_sql_query = items::add_access_query($entity_info['id'],$listing_sql_query);
		
		//include access to parent records
		$listing_sql_query .= items::add_access_query_for_parent_entities($entity_info['id']);
		 								
		$listing_sql_query_order .= items::add_listing_order_query_by_entity_id($entity_info['id']);
					
				
		$results = [];
		$listing_sql = "select  e.* " . $listing_sql_select . " from app_entity_" . $entity_info['id'] . " e "  . $listing_sql_query_join . " where " . $listing_sql_query . $listing_sql_query_having . $listing_sql_query_order;
		
		$listing_split = new split_page($listing_sql,'','query_num_rows', 30);
		$items_query = db_query($listing_split->sql_query, false);
		while($item = db_fetch_array($items_query))
		{
			$path_info = items::get_path_info($entity_info['id'],$item['id']);
      
      //print_r($path_info);
            
      $parent_name = '';
      if(strlen($path_info['parent_name'])>0)
      {
        $parent_name = str_replace('<br>',' / ',$path_info['parent_name']) . ' / '; 
      }
      
      $text = $parent_name . items::get_heading_field($entity_info['id'],$item['id'],$item);
			
			$results[] = ['id'=>$item['id'],'text'=>$text,'html'=>'<div>' . $text . '</div>'];
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