<?php

class fieldtype_related_records
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_RELATED_RECORDS_TITLE);
  }
  
  function get_configuration($params = array())
  {  
    $entity_info = db_find('app_entities',$params['entities_id']);
    
    $cfg = array();
            
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_SELECT_ENTITY, 
                   'name'=>'entity_id',
                   'tooltip'=>TEXT_FIELDTYPE_RELATED_RECORDS_SELECT_ENTITY_TOOLTIP . ' ' . $entity_info['name'],
                   'type'=>'dropdown',
                   'choices'=>entities::get_choices(),
                   'params'=>array('class'=>'form-control input-medium'),
    							 'onChange'=>'fields_types_ajax_configuration(\'fields_for_search_box\',this.value)'
    );
       
    /*
    $cfg[TEXT_SETTINGS][] = array('title'=>tooltip_icon(TEXT_ROWS_PER_PAGE_IF_NOT_SET) . TEXT_ROWS_PER_PAGE,
    		'name'=>'rows_per_page',
    		'type'=>'input',
    		'params'=>array('class'=>'form-control input-xsmall'));
    */
    
    $cfg[TEXT_SETTINGS][] = array('title'=>tooltip_icon(TEXT_DISPLAY_IN_MAIN_COLUMN_INFO) . TEXT_DISPLAY_IN_MAIN_COLUMN, 'name'=>'display_in_main_column','type'=>'checkbox');
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_FIELD_IF_NO_RECORDS, 'name'=>'hide_field_without_records','type'=>'checkbox');
    
    $cfg[TEXT_SETTINGS][] = array(
    		'title'=>TEXT_HIDE_BUTTONS,
    		'name'=>'hide_controls',
    		'type'=>'dropdown',
    		'choices'=>['add'=>TEXT_BUTTON_ADD,'bind'=>TEXT_BUTTON_BIND,'with_selected'=>TEXT_WITH_SELECTED],
    		'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));
      
    $cfg[TEXT_SETTINGS][] = array(
    		'title'=>TEXT_DISPLAY_IN_LISTING,
    		'name'=>'display_in_listing',    		
    		'type'=>'dropdown',
    		'choices'=>['count'=>TEXT_COUNT_RELATED_ITEMS,'list'=>TEXT_LIST_RELATED_ITEMS],
    		'params'=>array('class'=>'form-control input-medium'));
    
    /*
    $cfg[TEXT_SETTINGS][] = array(
    		'title'=>tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_HEADING_PATTER_IN_LINSING,
    		'tooltip' => TEXT_HEADING_TEMPLATE_INFO,
    		'name'=>'heading_template',
    		'type'=>'textarea',
    		'params'=>array('class'=>'form-control input-xlare textarea-small'));
    */
                   
    $cfg[TEXT_SETTINGS][] = array('name'=>'fields_in_listing','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'fields_in_popup','type'=>'hidden');
    
    $cfg[TEXT_SETTINGS][] = array('name'=>'create_related_comment','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'create_related_comment_text','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'delete_related_comment','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'delete_related_comment_text','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'create_related_comment_to','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'create_related_comment_to_text','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'delete_related_comment_to','type'=>'hidden');
    $cfg[TEXT_SETTINGS][] = array('name'=>'delete_related_comment_to_text','type'=>'hidden');
    
    //TEXT_FIELDS
    $cfg[TEXT_LINK_RECORD][] = array('name'=>'fields_for_search_box','type'=>'ajax','html'=>'<script>fields_types_ajax_configuration(\'fields_for_search_box\',$("#fields_configuration_entity_id").val())</script>');
                                  
    return $cfg;
  }  
  
  function get_ajax_configuration($name, $value)
  {
  	$cfg = array();
  	 
  	switch($name)
  	{
  		case 'fields_for_search_box':
  			$entities_id = $value;
  			  			  			
  			//search by fields
  			$choices = [];
  
  			$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in (" . fields_types::get_types_for_search_list(). ") and  f.entities_id='" . $entities_id . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
  			while($fields = db_fetch_array($fields_query))
  			{
  				$choices[$fields['id']] = fields_types::get_option($fields['type'],'name',$fields['name']);
  			}
  
  			$cfg[] = array('title'=>TEXT_SEARCH_BY_FIELDS,
  					'name'=>'fields_for_search',
  					'type'=>'dropdown',
  					'choices'=>$choices,
  					'tooltip_icon'=>TEXT_SEARCH_BY_FIELDS_INFO,
  					'params'=>array('class'=>'form-control chosen-select input-xlarge','multiple' =>'multiple'));
  				

  			//dorpdown template
  			$cfg[] = array('title'=>TEXT_HEADING_TEMPLATE . fields::get_available_fields_helper($entities_id, 'fields_configuration_heading_template'), 'name'=>'heading_template','type'=>'textarea','tooltip_icon'=>TEXT_HEADING_TEMPLATE_INFO,'tooltip'=>TEXT_ENTER_TEXT_PATTERN_INFO,'params'=>array('class'=>'form-control input-xlarge'));
  
  			break;
  	}
  
  	return $cfg;
  }  
  
  function render($field,$obj,$params = array())
  {
    return false;
  }
  
  function process($options)
  {        
    return false;
  }
  
  function output($options)
  {
    global $current_path_array, $current_entity_id, $current_item_id, $current_path,$app_user;
            
    //output count of related items 
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    if($cfg->get('display_in_listing')=='list')
    {    	
    	$related_records = new related_records($options['field']['entities_id'], $options['item']['id']);
    	$related_records->set_related_field($options['field']['id']);
    	    	    	
    	return $related_records->render_list_in_listing($options);    	
    }
    else
    {    
    	return $options['value'];
    }
  }  
  
  function reports_query($options)
  {
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
  
    $sql = array();
    
    if(strlen($filters['filters_values'])>0)
    {
    
      $field = db_find('app_fields',$filters['fields_id']);
      
      $cfg = new fields_types_cfg($field['configuration']);
                  
      $table_info = related_records::get_related_items_table_name($options['entities_id'],$cfg->get('entity_id'));
      
      
      //if quick filters panels then use search function
      if($filters['filters_condition']=='include')
      {
      	$where_sql = '';
	      
	      if(strlen($table_info['sufix'])>0)
	      {
	      	$where_sql = " or ri.entity_" . $options['entities_id'] . $table_info['sufix'] . "_items_id=e.id ";
	      }
	      
	      $search_sql = " and ri.entity_" . $cfg->get('entity_id') . "_items_id in 
	      		(select rie.id from app_entity_" . $cfg->get('entity_id') . " rie where " . (fields::get_heading_id($cfg->get('entity_id')) ? "rie.field_" . fields::get_heading_id($cfg->get('entity_id')) . " like '%" . db_input($filters['filters_values']) . "%'" : "rie.id='" . db_input($filters['filters_values']) . "'") . ")";
	      
	      $sql = "(select count(*) as total from " . $table_info['table_name'] . " ri where (ri.entity_" . $options['entities_id'] . "_items_id=e.id {$where_sql}) {$search_sql})";
	      
	      //echo $sql;
	      
	      $sql_query[] =  $sql . ">0";
      }	
      else
      {
	      $where_sql = '';
	      
	      if(strlen($table_info['sufix'])>0)
	      {
	      	$where_sql = " or ri.entity_" . $options['entities_id'] . $table_info['sufix'] . "_items_id=e.id ";
	      }
	      
	      $sql = "(select count(*) as total from " . $table_info['table_name'] . " ri where ri.entity_" . $options['entities_id'] . "_items_id=e.id {$where_sql})";
	      
	                                          
	      $sql_query[] = ($filters['filters_values']=='include' ? $sql . ">0" : $sql . "=0");
      }
    }
                      
    return $sql_query;
  }
  
  static function prepare_query_select($entities_id,$listing_sql_query_select, $reports_info=array())
  {
  	if(!isset($reports_info['fields_in_listing'])) $reports_info['fields_in_listing'] = '';
  	
    $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_related_records') and " . (strlen($reports_info['fields_in_listing']) ? "find_in_set(f.id,'" . $reports_info['fields_in_listing'] . "')":"f.listing_status=1"). " and f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id  order by t.sort_order, t.name, f.sort_order, f.name");
    while($field = db_fetch_array($fields_query))
    {    	
      $cfg = new fields_types_cfg($field['configuration']);
      
      if($cfg->get('display_in_listing','count')!='count') continue;
      
      $table_info = related_records::get_related_items_table_name($entities_id,$cfg->get('entity_id'));
      
      $where_sql = '';
      
      if(strlen($table_info['sufix'])>0)
      {
      	$where_sql = " or ri.entity_" . $entities_id . $table_info['sufix'] . "_items_id=e.id ";
      }
      
      $listing_sql_query_select .= ", (select count(*) as total from " . $table_info['table_name'] . " ri where ri.entity_" . $entities_id . "_items_id=e.id {$where_sql}) as field_" .$field['id'];                                            
    }
    
    return $listing_sql_query_select;
  }  
}