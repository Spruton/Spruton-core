<?php

class fieldtype_mysql_query
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_MYSQL_QUERY_TITLE);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
    $cfg[] = array('title'=>tooltip_icon(TEXT_FIELDTYPE_MYSQL_QUERY_DINAMIC_QUERY_INFO) . TEXT_FIELDTYPE_MYSQL_QUERY_DINAMIC_QUERY, 'name'=>'dinamic_query','type'=>'checkbox');
    
    $cfg[] = array(
    		'title'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_ENTITY,
    		'name'=>'entity_id',
    		'tooltip_icon'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_ENTITY_TOOLTIP,
    		'type'=>'dropdown',
    		'choices'=>entities::get_choices(),
    		'params'=>array('class'=>'form-control input-medium'));
    
    $cfg[] = array('title'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_QUERY, 'name'=>'select_query','type'=>'textarea','tooltip_icon'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_QUERY_TIP,'params'=>array('class'=>'form-control textarea-small required'));
    
    $cfg[] = array('title'=>TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY, 'name'=>'where_query','type'=>'textarea','tooltip_icon'=>TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY_TIP,'params'=>array('class'=>'form-control textarea-small required'));
    
    $cfg[] = array('title'=>tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT, 'name'=>'number_format','type'=>'input','params'=>array('class'=>'form-control input-small input-masked','data-mask'=>'9/~/~'), 'default'=>CFG_APP_NUMBER_FORMAT);
    $cfg[] = array('title'=>tooltip_icon(TEXT_CALCULATE_TOTALS_INFO) . TEXT_CALCULATE_TOTALS, 'name'=>'calclulate_totals','type'=>'checkbox');
    $cfg[] = array('title'=>TEXT_CALCULATE_AVERAGE_VALUE, 'name'=>'calculate_average','type'=>'checkbox');
    
    $cfg[] = array('title'=>TEXT_PREFIX,'name'=>'prefix','type'=>'input','params'=>array('class'=>'form-control input-small'));
    $cfg[] = array('title'=>TEXT_SUFFIX,'name'=>'suffix','type'=>'input','params'=>array('class'=>'form-control input-small'));
    
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {
    return '<p class="form-control-static">' . $obj['field_' . $field['id']]  .'</p>' . input_hidden_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']]);
  }
  
  function process($options)
  { 
    return $options['value'];
  }
  
  function output($options)
  {
  	//return non-formated value if export
  	if(isset($options['is_export']) and !isset($options['is_print']))
  	{
  		return $options['value'];
  	}
  	
    $value = $options['value'];
    
    //just return value if not numeric (not numeric values can be returned using IF operator)
    if(!is_numeric($value))
    {
    	return $value;
    }
    
    //return value using number format
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    if(strlen($cfg->get('number_format'))>0 and strlen($value)>0)
    {
      $format = explode('/',str_replace('*','',$cfg->get('number_format')));
                  
            
      $value = number_format($value,$format[0],$format[1],$format[2]);
    }
    elseif(strstr($value,'.'))
    {
      $value = number_format((float)$value,2,'.','');
    }
    
    //add prefix and sufix
    $value = (strlen($value) ? $cfg->get('prefix') . $value . $cfg->get('suffix') : '');
            
    return $value;
  }
  
  function reports_query($options)
  {
  	global $sql_query_having, $app_fields_cache;
  	  	  	
  	$cfg = new fields_types_cfg($app_fields_cache[$options['entities_id']][$options['filters']['fields_id']]['configuration']);
  	
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
                
    $sql = reports::prepare_numeric_sql_filters($filters,'');
    
    if(count($sql)>0 and $cfg->get('dinamic_query')==1)
    {
      $sql_query_having[$options['entities_id']][] =  implode(' and ', $sql);
    }
    elseif(count($sql)>0)
    {
      $sql_query[] =  implode(' and ', $sql);      
    }
    
    return $sql_query;
  } 
    
  static function get_fields_cache()
  {
  	$cache = array();
  	$fields_query = db_query("select * from app_fields where type in ('fieldtype_mysql_query')");
  	while($fields = db_fetch_array($fields_query))
  	{
  		$cache[$fields['entities_id']][] = $fields;
  	}
  
  	return $cache;
  }
  
  public static function prepare_query_select($entities_id,$listing_sql_query_select, $prefix = 'e')
  {
  	global $app_mysql_query_fields_cache, $fieldtype_mysql_query_force;
  	
  	if(isset($app_mysql_query_fields_cache[$entities_id]))
  	{
  		foreach($app_mysql_query_fields_cache[$entities_id] as $fields)
  		{
  			$cfg = new fields_types_cfg($fields['configuration']);
  			
  			//skip query if not dinamic
  			if($cfg->get('dinamic_query')!=1 and $fieldtype_mysql_query_force!=true) continue;
  			
  			//array to calculate totals in listing
  			if(is_array($listing_sql_query_select))
  			{
  				$listing_sql_query_select[] = self::prepare_query($fields,$prefix);
  			}
  			else
  			{
  				$listing_sql_query_select .= ',' . self::prepare_query($fields,$prefix);
  			}  			
  		}
  	}
  	
  	return $listing_sql_query_select;
  }
  
  public static function prepare_query($fields,$prefix = 'e', $single_select = false)
  {
  	global $app_not_formula_fields_cache, $fieldtype_mysql_query_force, $app_fields_cache, $app_formula_fields_cache,$app_user;
  	
  	$cfg = new fields_types_cfg($fields['configuration']);
  	
  	//skip query if not dinamic
  	if($cfg->get('dinamic_query')!=1 and $fieldtype_mysql_query_force!=true)
  	{
  		return $prefix.'.field_' . $fields['id'];
  	}
  	 
  	//single select to include direclty in formula
  	if($single_select)
  	{
  		$mysql_query =  "(select "  . $cfg->get('select_query')  . " from app_entity_"  . $cfg->get('entity_id') . " msq where " . $cfg->get('where_query') . " limit 1)";  		
  	}
  	else
  	{
  		$mysql_query =  "IFNULL((select "  . $cfg->get('select_query')  . " from app_entity_"  . $cfg->get('entity_id') . " msq where " . $cfg->get('where_query') . " limit 1),0) as field_" . $fields['id'];
  	}
  	
  	//prepare formulas
  	$formulas_fields = array();
  	 
  	if(isset($app_formula_fields_cache[$cfg->get('entity_id')]))
  	{
	  	foreach($app_formula_fields_cache[$cfg->get('entity_id')] as $formula_field)
	  	{
	  		$formula_cfg = fields_types::parse_configuration($formula_field['configuration']);
	  		 
	  		if(strlen($formula_cfg['formula']))
	  		{
	  			$formulas_fields[$formula_field['id']] = '(' . $formula_cfg['formula'] . ')';
	  		}
	  	}
  	}
  	  	  	
  	$mysql_query = fieldtype_formula::prepare_formula_fields($formulas_fields, $mysql_query);
  	
  	  	 
  	//prepare [TODAY]
  	$mysql_query = str_replace('[TODAY]',get_date_timestamp(date('Y-m-d')),$mysql_query);
  	$mysql_query = str_replace('[current_user_id]',$app_user['id'],$mysql_query);
  	  	
  	//prepare fields entity query
  	foreach($app_not_formula_fields_cache[$cfg->get('entity_id')] as $fields_id)
  	{
  		$fields_type = isset($app_fields_cache[$cfg->get('entity_id')][$fields_id]['type']) ? $app_fields_cache[$cfg->get('entity_id')][$fields_id]['type']:'';
  		if(in_array($fields_type,array('fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_js_formula')))
  		{	
  		  $mysql_query = str_replace('[' . $fields_id . ']','(msq.field_' . $fields_id . '+0)',$mysql_query);
  		}
  		else
  		{
  			$mysql_query = str_replace('[' . $fields_id . ']','msq.field_' . $fields_id ,$mysql_query);
  		}
  	}
  	  	
  	//handle get_vallue()
  	$mysql_query = fieldtype_formula::perpare_choices_get_value_function($cfg->get('entity_id'), $mysql_query,'msq');
  	  	
  	//prepare fields for current entity
  	foreach($app_not_formula_fields_cache[$fields['entities_id']] as $fields_id)
  	{
  		$fields_type = isset($app_fields_cache[$fields['entities_id']][$fields_id]['type']) ? $app_fields_cache[$fields['entities_id']][$fields_id]['type']:'';
  		if(in_array($fields_type,array('fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_js_formula')))
  		{
  			$mysql_query = str_replace('[' . $fields_id . ']','(' .$prefix.'.field_' . $fields_id.'+0)',$mysql_query);
  		}
  		else
  		{	
  			$mysql_query = str_replace('[' . $fields_id . ']',$prefix.'.field_' . $fields_id,$mysql_query);
  		}
  	}
  	    	
  	return $mysql_query;
  }
    
  //function to update item if there are any not dinamic query
  public static function update_items_fields($entities_id, $items_id)
  {
  	global $app_mysql_query_fields_cache;
  	
  	$update_fields = array();
  	
  	if(isset($app_mysql_query_fields_cache[$entities_id]))
  	{
  		foreach($app_mysql_query_fields_cache[$entities_id] as $fields)
  		{
  			$cfg = new fields_types_cfg($fields['configuration']);
  				
  			if($cfg->get('dinamic_query')!=1)
  			{
  				$update_fields[] = $fields['id'];
  			}  			
  		}
  	}
  	
  	if(count($update_fields))
  	{
  		$item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($entities_id,'') . " from app_entity_" . $entities_id . " e where e.id='" . db_input($items_id) . "'");
  		$item_info = db_fetch_array($item_info_query);
  		//print_r($item_info);
  		//exit();
  		
  		foreach($update_fields as $fields_id)
  		{
  			db_query("update app_entity_{$entities_id} set field_{$fields_id}='" . $item_info['field_' . $fields_id] . "' where id='" . db_input($items_id) . "'");
  		}
  	}
  }

}