<?php

class fieldtype_tags
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_TAGS_TITLE,'has_choices'=>true);
  }
  
  function get_configuration()
  {
    $cfg = array();

    $cfg[] = array('title'=>TEXT_WIDHT, 
                   'name'=>'width',
                   'type'=>'dropdown',
                   'choices'=>array('input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
                   'tooltip_icon'=>TEXT_ENTER_WIDTH,
                   'params'=>array('class'=>'form-control input-medium'));
    
    $cfg[] = array('title'=>TEXT_DISPLAY_AS,
    		'name'=>'display_as',
    		'type'=>'dropdown',
    		'choices'=>array('dropdown_multiple'=>TEXT_FIELDTYPE_DROPDOWN_MULTIPLE_TITLE,'dropdown'=>TEXT_FIELDTYPE_DROPDOWN_TITLE),
    		'default' => 'dropdown_multiple',
    		'params'=>array('class'=>'form-control input-xlarge'));
    
    $cfg[] = array('title'=>TEXT_AUTOMATICALLY_CREATE_TAG, 'name'=>'auto_create_tag','type'=>'checkbox','tooltip_icon'=>TEXT_AUTOMATICALLY_CREATE_TAG_TIP);
    
    $cfg[] = array('title'=>TEXT_HIDE_FIELD_IF_EMPTY, 'name'=>'hide_field_if_empty','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_IF_EMPTY_TIP);
    
    $cfg[] = array('title'=>TEXT_DISPLAY_CHOICES_VALUES, 'name'=>'display_choices_values','type'=>'checkbox','tooltip_icon'=>TEXT_DISPLAY_CHOICES_VALUES_TIP);
    
    //cfg global list if exist
    if(count($choices = global_lists::get_lists_choices())>0)
    {              
      $cfg[] = array('title'=>TEXT_USE_GLOBAL_LIST, 
                     'name'=>'use_global_list',
                     'type'=>'dropdown',
                     'choices'=>$choices,
                     'tooltip'=>TEXT_USE_GLOBAL_LIST_TOOLTIP,
                     'params'=>array('class'=>'form-control input-medium'));
    }                        
    
    return $cfg;
  }  
  
  function render($field,$obj,$params = array())
  {
  	global $app_module_path, $app_layout; 
  	
    $cfg = new fields_types_cfg($field['configuration']);
    
     
    $add_empty = ($field['is_required']==1 ? false:true);
    
    $attributes = array('class'=>'form-control ' . $cfg->get('width') . ' field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
           
    if($cfg->get('display_as')=='dropdown_multiple')
    {
    	$attributes['multiple'] = 'multiple';
    	$attributes['data-placeholder'] = TEXT_ENTER_VALUE;
    	$add_empty = false;
    	
    	$field_name = 'fields[' . $field['id'] . '][]';
    }
    else
    {
    	$field_name = 'fields[' . $field['id'] . ']';
    }
              
    //use global lists if exsit    
    if($cfg->get('use_global_list')>0)
    {
      $choices = global_lists::get_choices($cfg->get('use_global_list'),$add_empty,'',$obj['field_' . $field['id']],true);
      $default_id = global_lists::get_choices_default_id($cfg->get('use_global_list'));
    }
    else
    {                    
      $choices = fields_choices::get_choices($field['id'],$add_empty,'',$cfg->get('display_choices_values'),$obj['field_' . $field['id']],true);
      $default_id = fields_choices::get_default_id($field['id']);
    }
    
    $value = ($obj['field_' . $field['id']]>0 ? $obj['field_' . $field['id']] : ($params['form']=='comment' ? '':$default_id)); 
        
    $html = select_tag($field_name,$choices,$value,$attributes);
    
    //echo $app_module_path;
    
    $tags_flag = (in_array($app_module_path,['items/form','ext/public/form','users/registration','users/account','items/processes']) ? 1:0);    
                                   
    $html .= '
    	<script>	
    	$(function(){
	    	$("#fields_' . $field['id'] . '").select2({
		      tags: ' . $tags_flag . ',
		      width: "' . self::get_select2_width_by_class($cfg->get('width')) . '",		      
		      ' . (in_array($app_layout,['public_layout.php']) ? '':'dropdownParent: $("#ajax-modal"),') . ' 
		      ' . ($cfg->get('auto_create_tag')=='1' ? "tokenSeparators: [',', ' '],":'') . '
		      "language":{
		        "noResults" : function () { return "' . addslashes(TEXT_NO_RESULTS_FOUND) . '"; },		    				    				
		      },
		      createTag: function (params) {				
				    return {
				      id: "#"+params.term,
				      text: params.term
				    }
				  }		
	    	});
		  });
    	</script>
    ';
    
    return  $html;
  }
  
  function process($options)
  {  
  	global $process_options;
  	
  	$cfg = new fields_types_cfg($options['field']['configuration']);
  	
  	$values_id = [];
  	
  	$values_list = (is_array($options['value']) ? $options['value'] : array(0=>$options['value']));
  	
  	//print_r($values_list);
  	//exit();
  	    	
  	foreach($values_list as $value)
  	{
  		if(substr($value,0,1)=='#')
  		{
  			$value = substr($value,1);
  			
  			if(strlen($value))
  			{  				
  				$value = db_prepare_input(trim($value));
  				
  				if($cfg->get('use_global_list')>0)
  				{
  					$check_query = db_query("select id from app_global_lists_choices where lists_id='"  . $cfg->get('use_global_list') . "' and name='" . $value . "'");
  					if(!$check = db_fetch_array($check_query))
  					{
  						$sql_data = array(
  								'lists_id'=>$cfg->get('use_global_list'),  								
  								'name'=>$value,  								
  						);
  					
  						db_perform('app_global_lists_choices',$sql_data);
  						$choices_id = db_insert_id();
  					
  						$values_id[] = $choices_id;
  					}
  				}
  				else
  				{	  				
	  				$check_query = db_query("select id from app_fields_choices where fields_id='"  . $options['field']['id'] . "' and name='" . $value . "'");  				
	  				if(!$check = db_fetch_array($check_query))
	  				{
	  					$sql_data = array(
	  							'fields_id'=>$options['field']['id'],	  							
	  							'name'=>$value,	  							
	  					);
	  					
	  					db_perform('app_fields_choices',$sql_data);
	  					$choices_id = db_insert_id();
	  					
	  					$values_id[] = $choices_id;
	  				}
  				}  				
  			}
  		}
  		else
  		{
  			$values_id[] = $value;
  		}
  	}
  	
  	//update value in process option to handle $choices_values->prepare($process_options); in items.php
  	$process_options['value'] = $values_id;
  	
    return implode(',',$values_id);
  }
  
  function output($options)
  {
    $is_export = isset($options['is_export']);
    
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    //render global list value
    if($cfg->get('use_global_list')>0)
    {
      return global_lists::render_value($options['value'], $is_export);
    }
    else
    {
      return fields_choices::render_value($options['value'], $is_export);
    }
  }  
  
  function reports_query($options)
  {  	  	
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
    
    $prefix = (strlen($options['prefix']) ? $options['prefix'] : 'e');
  	        
  	if(strlen($filters['filters_values'])>0)
    {  
      $sql_query[] = "(select count(*) from app_entity_" . $options['entities_id'] . "_values as cv where cv.items_id=" . $prefix . ".id and cv.fields_id='" . db_input($options['filters']['fields_id'])  . "' and cv.value in (" . $filters['filters_values'] . ")) " . ($filters['filters_condition']=='include' ? '>0': '=0');
    }
    
    return $sql_query;
  }
  
  static function get_select2_width_by_class($class)
  {
  	if(is_mobile())
  	{
  		return '100%';
  	}
  	  	
  	switch($class)
  	{
  		case 'input-small': 
  			$width = '120px';
  			break;
  		case 'input-medium':
  			$width = '240px';
  			break;
  		case 'input-large':
  			$width = '320px';
  			break;
  		case 'input-xlarge':
  			$width = '480px';
  			break;
  		default:
  			$width = '100%';
  			break;
  	}
  	
  	return $width;
  }
}