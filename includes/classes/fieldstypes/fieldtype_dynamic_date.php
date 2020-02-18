<?php

class fieldtype_dynamic_date
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_DYNAMIC_DATE_TITLE);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_FORMULA . fields::get_available_fields_helper($_POST['entities_id'], 'fields_configuration_formula'), 'name'=>'formula','type'=>'textarea','tooltip_icon'=>TEXT_FORMULA_TIP_USAGE,'tooltip'=>TEXT_FORMULA_TIP,'params'=>array('class'=>'form-control'));
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DATE_FORMAT, 'name'=>'date_format','type'=>'input','tooltip'=>TEXT_DEFAULT .': ' . CFG_APP_DATE_FORMAT . ', ' . TEXT_DATE_FORMAT_IFNO, 'params'=>array('class'=>'form-control input-small'));
                
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_FIELD_IF_EMPTY, 'name'=>'hide_field_if_empty','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_IF_EMPTY_TIP);
    
    $cfg[TEXT_COLOR][] = array('title'=>TEXT_OVERDUE_DATES,
    		'name'=>'background',
    		'type'=>'colorpicker',
    		'tooltip_icon'=>TEXT_DATE_BACKGROUND_TOOLTIP);
     
    $cfg[TEXT_COLOR][] = array('title'=>TEXT_DAYS_BEFORE_DATE,
    		'name'=>'day_before_date',
    		'type'=>'input-with-colorpicker',
    		'tooltip_icon'=>TEXT_DAYS_BEFORE_DATE_TIP);
    
    $cfg[TEXT_COLOR][] = array('title'=>TEXT_DAYS_BEFORE_DATE . ' 2',
    		'name'=>'day_before_date2',
    		'type'=>'input-with-colorpicker',
    		'tooltip_icon'=>TEXT_DAYS_BEFORE_DATE_TIP);
    
    
    $choices = [''=>''];
    
    $fields_query = db_query("select * from app_fields where type in ('fieldtype_stages','fieldtype_dropdown','fieldtype_radioboxes','fieldtype_dropdown_multiple','fieldtype_tags','fieldtype_checkboxes') and entities_id='" . db_input($_POST['entities_id']) . "'");
    while($fields = db_fetch_array($fields_query))
    {
    	$choices[$fields['id']] = $fields['name'];
    }
    
    
    $cfg[TEXT_COLOR][] = array('title'=>TEXT_DISABLE_COLOR ,
    		'name'=>'disable_color_by_field',
    		'type'=>'dropdown',
    		'choices' => $choices,
    		'tooltip_icon'=>TEXT_DISABLE_COLOR_BY_FIELD_TIP,
    		'params'=>array('class'=>'form-control input-large','onChange'=>'fields_types_ajax_configuration(\'disable_color_by_field_values\',this.value)'),
    );
    
    $cfg[TEXT_COLOR][] = array('name'=>'disable_color_by_field_values','type'=>'ajax','html'=>'<script>fields_types_ajax_configuration(\'disable_color_by_field_values\',$("#fields_configuration_disable_color_by_field").val())</script>');
     
    
                                     
    return $cfg;
  } 
  
  function get_ajax_configuration($name, $value)
  {
  	$cfg = array();
  	 
  	switch($name)
  	{
  		case 'disable_color_by_field_values':
  			if(strlen($value))
  			{
  				$field_query = db_query("select id, name, configuration from app_fields where id='" . $value . "'");
  				if($field = db_fetch_array($field_query))
  				{
  					$field_cfg = new fields_types_cfg($field['configuration']);
  						
  					if($field_cfg->get('use_global_list')>0)
  					{
  						$choices = global_lists::get_choices($field_cfg->get('use_global_list'),false);
  					}
  					else
  					{
  						$choices = fields_choices::get_choices($field['id'],false);
  					}
  						
  					$cfg[] = array(
  							'title'=>$field['name'],
  							'name'=>'disable_color_by_field_choices',
  							'type'=>'dropdown',
  							'choices'=>$choices,
  							'params'=>array('class'=>'form-control input-large chosen-select','multiple'=>'multiple'),
  					);
  				}
  			}
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
  	return $options['value'];  	   
  }
  
  function output($options)
  {
  	$cfg = new fields_types_cfg($options['field']['configuration']);
  	
    if(isset($options['is_export']) and strlen($options['value'])>0 and $options['value']!=0)
    {
      return format_date($options['value'],$cfg->get('date_format'));
    }
    elseif(strlen($options['value'])>0 and $options['value']!=0)
    {   
    	$html = format_date($options['value'],$cfg->get('date_format'));
    	
    	//return simple value if color is disabled
    	if(strlen($cfg->get('disable_color_by_field')))
    	{
    		if(isset($options['item']['field_' . $cfg->get('disable_color_by_field')]))
    		{
    			if(is_array($cfg->get('disable_color_by_field_choices')))
    				foreach($cfg->get('disable_color_by_field_choices') as $choices_id)
    				{
    					if(in_array($choices_id,explode(',',$options['item']['field_' . $cfg->get('disable_color_by_field')])))
    					{
    						return $html;
    					}
    				}
    		}
    	}
    	
      //highlight field if overdue date    
      if((date('Y-m-d',$options['value'])==date('Y-m-d') or $options['value']<time()) and strlen($cfg->get('background'))>0)
      {                      
        $html = render_bg_color_block($cfg->get('background'),format_date($options['value'],$cfg->get('date_format')));
      }
      
      //highlight field before due date
      if(strlen($cfg->get('day_before_date'))>0 and strlen($cfg->get('day_before_date_color'))>0 and $options['value']>time())
      {
        if($options['value']<strtotime('+'.$cfg->get('day_before_date') . ' day'))
        {
          $html =  render_bg_color_block($cfg->get('day_before_date_color'),format_date($options['value'],$cfg->get('date_format'))); 
        }
      }
      
      //highlight 2 field before due date
      if(strlen($cfg->get('day_before_date2'))>0 and strlen($cfg->get('day_before_date2_color'))>0 and $options['value']>time())
      {
      	if($options['value']<strtotime('+'.$cfg->get('day_before_date2') . ' day'))
      	{
      		$html = render_bg_color_block($cfg->get('day_before_date2_color'),format_date($options['value'],$cfg->get('date_format')));
      	}
      }
          
      //return single value      
      return $html;
            
    }
    else
    {
      return '';
    }
  }
  
  function reports_query($options)
  {
  	global $sql_query_having;
  	
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
    
             
    $sql = reports::prepare_dates_sql_filters($filters,false);
        
    if(count($sql)>0)
    {
      $sql_query_having[$options['entities_id']][] =  implode(' and ', $sql);
    }
              
    return $sql_query;
  }
  
  
  //function to update item if there are any not dinamic query
  public static function update_items_fields($entities_id, $items_id)
  {
  	global $app_fields_cache;
  	 
  	$update_fields = array();
  	 
  	if(isset($app_fields_cache[$entities_id]))
  	{
  		foreach($app_fields_cache[$entities_id] as $fields)
  		{  			  			  
  			if($fields['type']=='fieldtype_dynamic_date')
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