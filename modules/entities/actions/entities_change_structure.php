<?php

switch($app_module_action)
{
	case 'save':
		
		$entities_id = _post::int('entities_id');
		$move_to_entities_id = _post::int('move_to_entities_id');
		
		
		if(!$entities_id) redirect_to('entities/entities');
		
		//reset reports type 'entity'
		db_query("delete from app_reports where entities_id='"  . $entities_id . "' and reports_type='entity'");
		
		$entities_tree = [];
		$entities_tree[] = $entities_id;
		foreach(entities::get_tree($entities_id) as $v)
		{
			$entities_tree[] = $v['id'];
		}
		
		//print_r($entities_tree);
		//exit();
		
		//check parent reports
		$reports_query = db_query("select id, parent_id from app_reports where entities_id in ("  . implode(',',$entities_tree) . ") and reports_type!='parent'");
		while($reports = db_fetch_array($reports_query))
		{
			if($reports['parent_id']>0)
			{
				$paretn_reports = reports::get_parent_reports($reports['parent_id']);
				$paretn_reports[]=$reports['parent_id'];
				
				//remove parent reports
				db_query("delete from app_reports where id in (" . implode(',',$paretn_reports). ")");
				
				//reset paretn id
				db_query("update app_reports set parent_id=0 where id='" . $reports['id']. "'");
			}
		}
					
		//change entity parent id
		db_query("update app_entities set parent_id='" . $move_to_entities_id . "' where id='" . $entities_id . "'");
		
		//autocreate parents reports
		$reports_query = db_query("select id, parent_id from app_reports where entities_id in ("  . implode(',',$entities_tree) . ") and reports_type not in ('parent','functions')");
		while($reports = db_fetch_array($reports_query))
		{
			if($reports['parent_id']==0)
			{
				reports::auto_create_parent_reports($reports['id']);
			}
		}
		
		//move entities
		if($move_to_entities_id>0)
		{						
			//change parent item id
			if(isset($_POST['parent_item_id']))
			{
				$parent_item_id = _post::int('parent_item_id');
					
				db_query("update app_entity_" . $entities_id . " set parent_item_id='" . $parent_item_id . "'");
			}			
		}
		else
		{
			//reset paretn item id
			db_query("update app_entity_" . $entities_id . " set parent_item_id=0");
		}
						
		$alerts->add(TEXT_ENTITY_STRUCTURE_CHANGED,'success');
				
		redirect_to('entities/entities');
		break;
	
	case 'get_parent_items':
		$html = '';
		
		$entities_id = _post::int('entities_id');
		
		if($entities_id>0)
		{
			$entities_info = db_find('app_entities',$entities_id);
			
			$choices = [''=>''];
			
			$listing_sql_query = '';
			
			$listing_sql_query .= items::add_listing_order_query_by_entity_id($entities_id);
			
			$items_sql_query = "select e.* from app_entity_" . $entities_id . " e where e.id>0 " . $listing_sql_query; 
	    $items_query = db_query($items_sql_query);
	    while($items = db_fetch_array($items_query))
	    { 
				$parent_name = '';      
                
        if($entities_info['parent_id']>0)
        {                    
          $parent_name = items::get_heading_field($entities_info['parent_id'],$items['parent_item_id']) . ' > ';          
        }       
            
      	$name = items::get_heading_field($entities_id,$items['id']);
          
      	$choices[$items['id']] = $parent_name . $name;
	    }
			
			$html = '					
					<div class="form-group">
				  	<label class="col-md-3 control-label" for="name">' . TEXT_PARENT .'</label>
				    <div class="col-md-9">	
				  	  ' . select_tag('parent_item_id',$choices,'',array('class'=>'form-control input-xlarge chosen-select required')) . '
				  	  ' . tooltip_text(TEXT_MOVE_TO_PARETN_ITEM_INFO) . '
				    </div>			
				  </div>';
		}
		
		echo $html;
		exit();
		break;
}
	