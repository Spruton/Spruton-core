<?php

if(!is_ext_installed())
{
	redirect_to('dashboard/page_not_found');
}

if(!global_search::has_access())
{
	redirect_to('dashboard/access_forbidden');
}

$app_title = app_set_title(TEXT_SEARCH);

switch($app_module_action)
{
	case 'listing':
		
		$search_queries = [];
		
		$entities_cfg_holder = [];
		
		//print_rr($_POST);
		
		if(strlen($_POST['keywords']))
		{
			
			$where_sql = (isset($_POST['search_by_entities']) ? " and gs.entities_id in (" . implode(',',$_POST['search_by_entities']). ")":"");
			
			$entities_query = db_query("select gs.*, e.name from app_ext_global_search_entities gs, app_entities e where gs.entities_id=e.id {$where_sql} order by gs.sort_order,gs.id");							
			while($entities = db_fetch_array($entities_query))
			{
				if(!users::has_users_access_name_to_entity('view',$entities['entities_id'])) continue;
				
				$entity_cfg = new entities_cfg($entities['entities_id']);
											
				$heading_field_id = fields::get_heading_id($entities['entities_id']);
				
				if(!$heading_field_id) continue;
				
				//prepare entities hodler
				$entities_cfg_holder[$entities['entities_id']] = $entities;
				$entities_cfg_holder[$entities['entities_id']]['cfg'] = $entity_cfg;
				$entities_cfg_holder[$entities['entities_id']]['heading_field_id'] = $heading_field_id;
																
				$fields_in_listing = "e.id, " . fields::prepare_field_db_name_by_type($entities['entities_id'],$heading_field_id) . " as title, ({$entities['entities_id']}) as entities_id"; 
												
				//preapre fields for search
				$fields_for_search = [];
				if(strlen($entities['fields_for_search']))
				{
					$fields_for_search = explode(',',$entities['fields_for_search']); 
				}
				
				$fields_for_search[] = $heading_field_id;
				
				$fields_for_search = array_unique($fields_for_search);
				
				//print_rr($fields_for_search);
							
				$where_sql = [];
				if(count($fields_for_search))
				{
					$fields_query = db_query("select id,configuration,type from app_fields where id in (" . implode(',',$fields_for_search) . ")");
					while($fields = db_fetch_array($fields_query))
					{
						$field = [
								'fields_id' => $fields['id'],
								'type' => $fields['type'],
								'filters_values' => $_POST['keywords'],
								'filters_condition' => ($_POST['search_type_match']=='true' ? 'search_type_match':''),
						];
											
						
						$where_sql = reports::add_search_qeury($field, $entities['entities_id'], $where_sql);
					}
				}
				
				//print_rr($where_sql);
				
				$sql = '';
				
				if(count($where_sql))
				{
					$sql = "select {$fields_in_listing} from app_entity_{$entities['entities_id']} e where " . (implode(' or ',$where_sql));
																
					if($_POST['search_in_comments']=='true' and $entity_cfg->get('use_comments')==1)
					{
						$sql.= " or (select count(*) as total from app_comments as ec where ec.entities_id='" . $entities['entities_id'] . "' and ec.items_id=e.id";
						
						$sql .= " and (";
						for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ )
						{
							switch ($search_keywords[$i])
							{
								case '(':
								case ')':
									$sql .= " " . $search_keywords[$i] . " ";
									break;
								case 'and':
								case 'or':
									$search_type = ($_POST['search_type_match']=='true' ? 'and' : $search_keywords[$i]);
									$sql .= " " . $search_type . " ";
									break;
								default:
									$keyword = $search_keywords[$i];
									$sql .= "ec.description like '%" . db_input($keyword) . "%'";
									break;
							}
						}
						$sql .= "))>0";
					}
					
					//check view assigned only access
					$sql = items::add_access_query($entities['entities_id'],$sql);
					
					//include access to parent records
					$sql .= items::add_access_query_for_parent_entities($entities['entities_id']);
				}
				
				if(strlen($sql))
				{
					$search_queries[] = $sql;
				}
			}	
		}
		
		//print_rr($search_queries);
				
		if(count($search_queries))
		{
			
			$listing_sql = implode(' UNION ', $search_queries);
			
			$listing_split = new split_page($listing_sql,'search_result','query_num_rows', CFG_GLOBAL_SEARCH_ROWS_PER_PAGE);
			
			$items_query = db_query($listing_split->sql_query,false);
			
			if(is_mobile())
			{
				require(component_path('global_search/listing_mobile'));
			}
			else
			{
				require(component_path('global_search/listing'));
			}
			
		}
		else
		{
			$html = '		
				<div class="table-scrollable">	
			    <table class="table table-striped table-bordered table-hover">
			      <thead>
			        <tr>								
								<th width="100%">' . TEXT_TITLE . '</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td >' . TEXT_NO_RECORDS_FOUND . '</td>
							</tr>										
						</tbldy>
					</table>
				</div>						
				';
			
		}
		
		echo $html;
		
		db_dev_log();
		
		exit();
		break;
}