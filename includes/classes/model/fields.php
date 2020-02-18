<?php

class fields
{
	//get heading fields chace for all entities 
	public static function get_heading_fields_cache()
	{
		$cache = array();
		$fields_query = db_query("select * from app_fields where is_heading=1");
		while($fields = db_fetch_array($fields_query))
		{
			$cache[$fields['id']] = $fields;
		}
				
		return $cache;
	}
	
	public static function get_heading_fields_id_cache_by_entity()
	{
		$cache = array();
		$fields_query = db_query("select * from app_fields where is_heading=1");
		while($fields = db_fetch_array($fields_query))
		{
			$cache[$fields['entities_id']] = $fields['id'];
		}
					
		return $cache;
	}
	
	static function not_formula_fields_cache()
	{
		$cache = array();
		$fields_query = db_query("select * from app_fields where type not in ('fieldtype_formula','fieldtype_dynamic_date')");
		while($fields = db_fetch_array($fields_query))
		{
			$cache[$fields['entities_id']][] = $fields['id'];
		}
	
		return $cache;
	}
	
	static function formula_fields_cache()
	{
		$cache = array();
		$fields_query = db_query("select * from app_fields where type in ('fieldtype_formula','fieldtype_dynamic_date')");
		while($fields = db_fetch_array($fields_query))
		{
			$cache[$fields['entities_id']][] = array(
					'id' => $fields['id'],
					'name' => $fields['name'],
					'configuration' => $fields['configuration'],
			);
		}
	
		return $cache;
	}	
	
	static function get_cache()
	{
		$cache = array();
		$fields_query = db_query("select id, type, name, entities_id, configuration from app_fields");
		while($fields = db_fetch_array($fields_query))
		{
			
			$cache[$fields['entities_id']][$fields['id']] = array(
					'id' => $fields['id'],
					'type' => $fields['type'],
					'name' => (strlen($fields['name']) ? $fields['name'] : str_replace('fieldtype_','',$fields['type'])),
					'entities_id' => $fields['entities_id'],
					'configuration' => $fields['configuration'],
			);
			
			if(in_array($fields['type'], array('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by','fieldtype_parent_item_id')))
			{
				$cache[$fields['entities_id']][$fields['type']] = array(
						'id' => $fields['id'],
						'type' => $fields['type'],
						'name' => (strlen($fields['name']) ? $fields['name'] : str_replace('fieldtype_','',$fields['type'])),
						'entities_id' => $fields['entities_id'],
						'configuration' => $fields['configuration'],
				);
			}
		}
	
		return $cache;
	}
	
	public static function get_choices($entities_id)
	{
		$choices = array();
		$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_action','fieldtype_parent_item_id') and  f.entities_id='" . $entities_id . "' and  f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
		while($v = db_fetch_array($fields_query))
		{
			$choices[$v['id']] = fields_types::get_option($v['type'],'name',$v['name']);		
		}	
		
		return $choices;
	}
	
  public static function get_available_fields($entities_id,$required_types,$warn_message)
  { 
    $html = '';   
    $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in (" . $required_types . ") and f.entities_id='" . $entities_id . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
    while($fields = db_fetch_array($fields_query))
    {
      $html .= '
        <tr>
          <td>' . $fields['id'] . '</td>
          <td>' . $fields['name'] . '</td>
        </tr>
      ';
    }
    
    if(strlen($html)>0)
    {
      return '
        <table class="table">
          <tr>
            <th>' . TEXT_ID . '</th>
            <th>' . TEXT_NAME . '</th>
          </tr>
          ' . $html  . '
        </table>
      ';
    }
    else
    {
      return '<div class="alert alert-warning">' . $warn_message . '</div>'; 
    }
  }
  
  public static function check_before_delete($id)
  {     
    return '';
  }
  
  public static function get_name_by_id($id)
  {
    $obj = db_find('app_fields',$id);
    
    return $obj['name'];
  }
  
  public static function get_name_cache()
  {
    $cache = array();
    $fields_query = db_query("select * from app_fields");
    while($fields = db_fetch_array($fields_query))
    {
    	$cache[$fields['id']] = fields_types::get_option($fields['type'], 'name',$fields['name']);      
    }
    
    return $cache;
    
  }
  
  public static function get_heading_id($entity_id)
  {
  	global $app_heading_fields_id_cache;
  	    
    if(isset($app_heading_fields_id_cache[$entity_id]))
    {
      return $app_heading_fields_id_cache[$entity_id];
    }
    else
    {
      return false;
    }       
  }
  
  public static function get_last_sort_number($forms_tabls_id)
  {
    $v = db_fetch_array(db_query("select max(sort_order) as max_sort_order from app_fields where forms_tabs_id = '" . db_input($forms_tabls_id) . "'"));
    
    return $v['max_sort_order'];
  } 
  
  public static function render_required_messages($entities_id)
  {
    $html = '';
    
    $fields_query = db_query("select f.id, f.type, f.required_message, f.configuration from app_fields f where f.type not in (" . fields_types::get_reserverd_types_list(). ") and  f.entities_id='" . db_input($entities_id) . "' order by f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
    	$cfg =  new fields_types_cfg($v['configuration']);
    	
    	$attributes = [];
    	
    	if(strlen($cfg->get('min_value')))
    	{
    		$attributes[] = 'min: jQuery.validator.format("' . htmlspecialchars(TEXT_MIN_VALUE_WARNING) .  '")';
    	}
    	
    	if(strlen($cfg->get('max_value')))
    	{
    		$attributes[] = 'max: jQuery.validator.format("' . htmlspecialchars(TEXT_MAX_VALUE_WARNING) . '")';
    	}
    		    	    	
      if(strlen($v['required_message'])>0)
      {       
      	$attributes[] ='required: "' . str_replace(array("\n","\r","\n\r",'<br><br>'),"<br>",htmlspecialchars($v['required_message'])) . '"';      	     
      }
           
      if(count($attributes))
      {
      	switch($v['type'])
      	{
      		case 'fieldtype_dropdown_multiple':
      		case 'fieldtype_checkboxes':
      			$name = 'fields[' . $v['id'] . '][]';
      			break;
      		default:
      			$name = 'fields[' . $v['id'] . ']';
      			break;
      	}
      	
      	$html .='\'' . $name . '\':{' . implode(',', $attributes). '},' . "\n";
      }
            
    }
    
    return $html;
  }
  
  public static function render_required_ckeditor_ruels($entities_id)
  {
    $html = '';
    
    $fields_query = db_query("select f.* from app_fields f where f.type = 'fieldtype_textarea_wysiwyg' and is_required=1 and  f.entities_id='" . db_input($entities_id) . "' order by f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
        $html .='
          "fields[' . $v['id'] . ']": { 
            required: function(element){
              CKEDITOR_holders["fields_' . $v['id'] . '"].updateElement();              
              return true;             
            }
          },' . "\n";
    }
    
    return $html;
  }
        
  public static function get_search_feidls($entity_id)
  {
    global $app_user;
    
    $fields_access_schema = users::get_fields_access_schema($entity_id,$app_user['group_id']);
        
    $search_fields = array();
        
    $fields_query = db_query("select f.id, f.type, f.configuration, f.name, f.is_heading, t.name as tab_name from app_fields f, app_forms_tabs t where f.entities_id='" . db_input($entity_id) . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
      //check field access
      if(isset($fields_access_schema[$v['id']]))
      { 
        if($fields_access_schema[$v['id']]=='hide') continue;
      }
      
      $cfg = fields_types::parse_configuration($v['configuration']);      
      if(isset($cfg['allow_search']))
      {
        $search_fields[] = array(
        		'id'=>$v['id'],
        		'type'=> $v['type'], 
        		'name'=>fields_types::get_option($v['type'],'name',$v['name']),
        		'is_heading'=>$v['is_heading'],
        		'configuration'=> $v['configuration'],
        ); 
      }
    } 
            
    return $search_fields; 
  }
  
  public static function get_filters_choices($entity_id, $show_parent_item_fitler = true,$exclude = "")
  {
    global $app_user, $app_redirect_to;
    
    $entity_info = db_find('app_entities',$entity_id);
    
    $fields_access_schema = users::get_fields_access_schema($entity_id,$app_user['group_id']);
    
    $types_for_filters_list = fields_types::get_types_for_filters_list();
    
    $filters_panels_fields = ((isset($_GET['path']) and $app_redirect_to=='listing') ? filters_panels::get_fields_list($entity_id) : []);
    
    //include fieldtype_parent_item_id only for sub entities
    if($entity_info['parent_id']>0 and $show_parent_item_fitler)
    {
      $types_for_filters_list .= ", 'fieldtype_parent_item_id'";
    }
    
    //include special filters for Users
    if($entity_id==1)
    {
    	$types_for_filters_list .= ", 'fieldtype_user_accessgroups', 'fieldtype_user_status'";
    }
                
    $choices = array();
    $choices[''] = '';    
    $fields_query = db_query("select f.*, t.name as tab_name, if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type in (" . $types_for_filters_list . ") " . (strlen($exclude) ? " and f.type not in ({$exclude})":'') . " and f.entities_id='" . db_input($entity_id) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
      //check field access
      if(isset($fields_access_schema[$v['id']]))
      { 
        if($fields_access_schema[$v['id']]=='hide') continue;
      }
      
      //skip fields in quick filter panel
      if(in_array($v['id'],$filters_panels_fields)) continue;
        
      $choices[$v['id']] = fields_types::get_option($v['type'],'name',$v['name']); 
    } 
    
    return $choices;
  }
  
  public static function check_if_type_changed($field_id, $new_type)
  {
    $field_info_query = db_query("select * from app_fields where id='" . db_input($field_id) . "'");
    if($field_info = db_fetch_array($field_info_query))
    {
      //check if field type changed
      if($field_info['type']!=$new_type)
      {        
      	//delete index
      	$check_query = db_query("SHOW INDEX FROM app_entity_" . $field_info['entities_id'] . " WHERE KEY_NAME = 'idx_field_" . $field_info['id'] . "'");
      	if($check = db_fetch_array($check_query))
      	{     
      		db_query("ALTER TABLE app_entity_" . $field_info['entities_id'] . " DROP INDEX idx_field_" . $field_info['id']);
      	}
      	
 				//prepare db field type
 				db_query("ALTER TABLE app_entity_" . $field_info['entities_id']. " CHANGE field_" . $field_info['id'] . " field_" . $field_info['id'] . " " . entities::prepare_field_type($new_type) . " NOT NULL;");
      	
        //delete all filters for this field type since they are will not work correclty
        db_delete_row('app_reports_filters',$field_id,'fields_id');
        
        //add index
        entities::prepare_field_index($field_info['entities_id'], $field_info['id'], $new_type);
      }
    }                         
  }
  
  public static function get_items_fields_data_by_id($item, $fields_list = '',$entities_id, $fields_access_schema)
  {
    global $app_choices_cache, $app_users_cache;
    
    $data = array();
    
    if(strlen($fields_list)>0)
    {
      $fields_query = db_query("select f.* from app_fields f, app_forms_tabs t where  f.id in (" . $fields_list . ") and  f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id order by field(f.id," . $fields_list . ")");
      while($field = db_fetch_array($fields_query))
      {   
        //check field access
        if(isset($fields_access_schema[$field['id']]))
        {
          if($fields_access_schema[$field['id']]=='hide') continue;
        }
                   
        if(in_array($field['type'],fields_types::get_reserved_data_types()))
        {
          $value = $item[fields_types::get_reserved_filed_name_by_type($field['type'])];
        }
        else
        {
          $value = $item['field_' . $field['id']];
        }
      
        $output_options = array('class'=>$field['type'],
                                'value'=>$value,
                                'field'=>$field,
                                'item'=>$item,        												
                                'is_listing'=>true,
                                'is_export' => true,                                
        												'is_print' => true,
                                'redirect_to' => '',
                                'reports_id'=> 0,
                                'path'=> '');
                                
        $data[] = array(
        		'name'=> fields_types::get_option($field['type'],'name',$field['name']),
        		'value'=>fields_types::output($output_options),
        		'type'=>$field['type'],
        );
      }
    }
    
    return $data;
  } 
  
  public static function get_items_fields_fresh_data($item, $fields_list = '',$entities_id, $fields_access_schema)
  {
  	global $app_choices_cache, $app_users_cache;
  
  	$data = array();
  
  	if(strlen($fields_list)>0)
  	{
  		$fields_query = db_query("select f.* from app_fields f, app_forms_tabs t where  f.id in (" . $fields_list . ") and  f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id order by field(f.id," . $fields_list . ")");
  		while($field = db_fetch_array($fields_query))
  		{
  			//check field access
  			if(isset($fields_access_schema[$field['id']]))
  			{
  				if($fields_access_schema[$field['id']]=='hide') continue;
  			}
  			 
  			if(in_array($field['type'],fields_types::get_reserved_data_types()))
  			{
  				$value = $item[fields_types::get_reserved_filed_name_by_type($field['type'])];
  			}
  			else
  			{
  				$value = $item['field_' . $field['id']];
  			}
  
  			$output_options = array('class'=>$field['type'],
  					'value'=>$value,
  					'field'=>$field,
  					'item'=>$item,
  					'is_listing'=>true,
  					'redirect_to' => '',
  					'reports_id'=> 0,
  					'path'=> '');
  
  			$data[] = array(
  					'name'=> fields_types::get_option($field['type'],'name',$field['name']),
  					'value'=>fields_types::output($output_options),
  					'type'=>$field['type'],
  			);
  		}
  	}
  
  	return $data;
  }
  
  public static function get_field_choices_background_data($field_id)
  {
  	$data = array();
  	
  	$field_info_query = db_query("select * from app_fields where id='" . $field_id . "'");
  	if($field_info = db_fetch_array($field_info_query))
  	{
  		$cfg = new fields_types_cfg($field_info['configuration']);
  		if($cfg->get('use_global_list')>0)
  		{
  			$choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($cfg->get('use_global_list')). "' and length(bg_color)>0");
  		}
  		else
  		{
  			$choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($field_id). "' and length(bg_color)>0");
  		}
  		
  		while($choices = db_fetch_array($choices_query))
  		{
  			$rgb = convert_html_color_to_RGB($choices['bg_color']);
  			
  			if(($rgb[0]+$rgb[1]+$rgb[2])<480)
  			{
  				$data[$choices['id']]  = ['background'=>$choices['bg_color'],'color'=>'#ffffff'];  				
  			}
  			else
  			{
  				$data[$choices['id']]  = ['background'=>$choices['bg_color']];
  			}  			  			
  		}
  		
  		return $data;
  	}
  	
  }
  
  public static function get_item_info_tooltip($field)
  {
  	$text = '';
  	
  	if(strlen($field['tooltip']) and $field['tooltip_in_item_page']==1)
  	{
  		$text = $field['tooltip'];
  	}
  	elseif(strlen($field['tooltip_item_page']))
  	{
  		$text = $field['tooltip_item_page'];
  	}
  	
  	if(strlen($text))
  	{
  		return ' ' . tooltip_icon($text,'top');
  	}
  	else
  	{
  		return '';
  	}
  }
  
  static function get_fields_in_popup_choices($entities_id,$app_id = false)
  {
  	$choices = [];
  	$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where is_heading = 0 and f.type not in ('fieldtype_action','fieldtype_parent_item_id') and  f.entities_id='" . $entities_id . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
  	while($v = db_fetch_array($fields_query))
  	{
  		$choices[$v['id']] = fields_types::get_option($v['type'],'name',$v['name']) . ($app_id ? ' (#' . $v['id'] . ')':''); 
  	}
  	
  	return $choices;  	
  }
  
  static function get_fields_in_listing_choices($entities_id,$app_id = false)
  {
  	$choices = [];
  	$exclude_fields_types_sql = " and f.type not in ('fieldtype_section','fieldtype_mapbbcode','fieldtype_mind_map')";
		$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id {$exclude_fields_types_sql} order by t.sort_order, t.name, f.sort_order, f.name");
  	while($v = db_fetch_array($fields_query))
  	{
  		$choices[$v['id']] = fields_types::get_option($v['type'],'name',$v['name']) . ($app_id ? ' (#' . $v['id'] . ')':'');
  	}
  	 
  	return $choices;
  }
  
  static function get_available_fields_helper($entities_id, $template_field_id, $dropdown_title = TEXT_AVAILABLE_FIELDS, $use_fieldtypes = [], $skip_reserved = false)
  {  	
  	$entities_info = db_find('app_entities',$entities_id);
  	
  	$unique_id = $entities_id . rand(1000,9999);
  	
  	$where_sql = '';
  	if(count($use_fieldtypes))
  	{
  		array_walk($use_fieldtypes, function (&$v, $k) { $v = "'{$v}'" ; });
  		
  		$where_sql = " and f.type in (" . implode(',',$use_fieldtypes). ")";
  	}
  	 
  	$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in (" . fields_types::get_reserverd_types_list() . ") and f.entities_id='" . $entities_id . "' and f.forms_tabs_id=t.id {$where_sql} order by t.sort_order, t.name, f.sort_order, f.name");
  	 
  	if(db_num_rows($fields_query)==0) return '';
  	 
  	$html = '
  			<div class="dropdown">
				  <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				    ' . $dropdown_title  . '
				    <span class="caret"></span>
				  </button>
  			<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="max-height: 250px; overflow-y: auto">';
  	
  	
  	if(!count($use_fieldtypes) and !$skip_reserved)
  	{
	  	$html .= '  			  			
	  	    <li>
	  				<a href="#" class="insert_to_template_' . $unique_id . '" data-field="[id]">' . TEXT_FIELDTYPE_ID_TITLE . ' [id]</a>
	  	    </li>
	  	    <li>
	  	      <a href="#" class="insert_to_template_' . $unique_id . '" data-field="[date_added]">' . TEXT_FIELDTYPE_DATEADDED_TITLE . ' [date_added]</a>
	  	    </li>
	  	    <li>
	  	      <a href="#" class="insert_to_template_' . $unique_id . '" data-field="[created_by]">' . TEXT_FIELDTYPE_CREATEDBY_TITLE . ' [created_by]</a>
	  	    </li>';
			
	  	 
	  	if($entities_info['parent_id']>0)
	  	{
	  		$html .= '
	  				<li>
		  	      <a href="#" class="insert_to_template_' . $unique_id . '" data-field="[parent_item_id]">' . TEXT_FIELDTYPE_PARENT_ITEM_ID_TITLE . ' [parent_item_id]</a>
		  	    </li>';
	  	}
  	}
  	    	
  	while($v = db_fetch_array($fields_query))
  	{
  		if($v['type']=='fieldtype_dropdown_multilevel' and count($use_fieldtypes))
  		{
  			$html .= fieldtype_dropdown_multilevel::output_export_template($v);
  		}
  		else
  		{
  			$html .= '
  		    <li>
  		  		<a href="#"  class="insert_to_template_' . $unique_id . '" data-field="[' . $v['id'] . ']">' . fields_types::get_option($v['type'],'name',$v['name']) . ' [' . $v['id'] . ']</a>
  		    </li>';
  		}
  	}
  	 
  	$html .= '</ul></div>';
  	 
  	$html .= '
  			<script>
  			$(".insert_to_template_' . $unique_id . '").click(function(){
			    html = $(this).attr("data-field").trim();
  				
  				textarea_insert_at_caret("' . $template_field_id .'",html)  					
  					
			    //CKEDITOR.instances.description.insertText(html);
			  })
  			</script>
  			';
  	 
  	return $html;
  }
  
  static function get_unique_fields_list($entities_id)
  {
  	$list = [];
  	$fields_query = db_query("select id, configuration from app_fields where entities_id='" . $entities_id . "'");
  	while($fields = db_fetch_array($fields_query))
  	{
  		$cfg =  new fields_types_cfg($fields['configuration']);
  		if($cfg->get('is_unique'))
  		{
  			$list[] = $fields['id'];
  		}
  	}
  	
  	return $list;
  	
  }
  
  static function prepare_field_db_name_by_type($entities_id, $fields_id, $alias='e')
  {
  	global $app_fields_cache;
  	
  	switch($app_fields_cache[$entities_id][$fields_id]['type'])
  	{
  		case 'fieldtype_id':
  			$sql = $alias . ".id";
  			break;
  		case 'fieldtype_created_by':
  			$sql = $alias . ".created_by";
  			break;
  		case 'fieldtype_date_added':
  			$sql = $alias . ".date_added";
  			break;
  		case 'fieldtype_date_updated':
  			$sql = $alias . ".date_updated";
  			break;
  		case 'fieldtype_parent_item_id':
  			$sql = $alias . ".parent_item_id";
  			break;
  		default:
  			$sql = $alias . ".field_" . $fields_id;
  			break;
  	}
  	
  	return $sql;
  }
        
}