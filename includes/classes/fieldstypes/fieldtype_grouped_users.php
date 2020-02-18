<?php

class fieldtype_grouped_users
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_GROUPEDUSERS_TITLE,'has_choices'=>true);
  }
  
  function get_configuration($params = array())
  {
  
    $cfg = array();
                   
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DISPLAY_USERS_AS, 
                   'name'=>'display_as',
                   'tooltip'=>TEXT_DISPLAY_USERS_AS_TOOLTIP,
                   'type'=>'dropdown',
                   'choices'=>array('dropdown'=>TEXT_DISPLAY_USERS_AS_DROPDOWN,'checkboxes'=>TEXT_DISPLAY_USERS_AS_CHECKBOXES,'dropdown_muliple'=>TEXT_DISPLAY_USERS_AS_DROPDOWN_MULTIPLE),
                   'params'=>array('class'=>'form-control input-xlarge'));  
                   
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_WIDHT, 
                   'name'=>'width',
                   'type'=>'dropdown',
                   'choices'=>array('input-small'=>TEXT_INPTUT_SMALL,'input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
                   'tooltip'=>TEXT_ENTER_WIDTH,
                   'params'=>array('class'=>'form-control input-medium'));
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DEFAULT_TEXT,
					    		 'name'=>'default_text',
					    		 'type'=>'input',
					    		 'tooltip'=>TEXT_DEFAULT_TEXT_INFO,
					    		 'params'=>array('class'=>'form-control input-medium'));
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DISABLE_NOTIFICATIONS, 'name'=>'disable_notification','type'=>'checkbox','tooltip_icon'=>TEXT_DISABLE_NOTIFICATIONS_FIELDS_INFO);
    
    //cfg global list if exist
    if(count($choices = global_lists::get_lists_choices())>0)
    {
    	$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_USE_GLOBAL_LIST,
    			'name'=>'use_global_list',
    			'type'=>'dropdown',
    			'choices'=>$choices,
    			'tooltip'=>TEXT_USE_GLOBAL_LIST_TOOLTIP,
    			'params'=>array('class'=>'form-control input-medium'));
    }
    
    $choices = [];
    $choices['listing'] = TEXT_IN_LISTING;
    $choices['info_page'] = TEXT_IN_ITEM_PAGE;
    $cfg[TEXT_USERS][] = array(
    		'title'=>TEXT_SHOW_USERS, 
    		'name'=>'show_users',
    		'type'=>'dropdown',
    		'choices'=>$choices,'params'=>['class'=>'form-control input-large chosen-select','multiple'=>'multiple'],
    		'tooltip'=>TEXT_FIELDTYPE_GROUPEDUSERS_SHOW_USERS_TIP
    );
   
    $cfg[TEXT_USERS][] = array('title'=>TEXT_SHOW_USERS_ACCESS_GROUP, 'name'=>'show_users_access_group','type'=>'checkbox');
    
    return $cfg;
  }   
    
  function render($field,$obj,$params = array())
  {                
    $attributes = array('class'=>'form-control input-medium field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
           
    $cfg = new fields_types_cfg($field['configuration']);
    
    $display_as = (strlen($cfg->get('display_as'))>0 ? $cfg->get('display_as') : 'dropdown');
        
    if($cfg->get('use_global_list')>0)
    {
    	$choices = global_lists::get_choices($cfg->get('use_global_list'),(($display_as=='dropdown' and ($field['is_required']==0 or strlen($cfg->get('default_text')))>0) ? true:false), $cfg->get('default_text'),$obj['field_' . $field['id']],true);
    	$default_id = global_lists::get_choices_default_id($cfg->get('use_global_list'));
    }
    else
    {
    	$choices = fields_choices::get_choices($field['id'],(($display_as=='dropdown' and ($field['is_required']==0 or strlen($cfg->get('default_text')))>0) ? true:false), $cfg->get('default_text'), $cfg->get('display_choices_values'),$obj['field_' . $field['id']],true);
    	$default_id = fields_choices::get_default_id($field['id']);
    }
    
    $value = $obj['field_' . $field['id']];
    $value = ($value>0 ? $value : $default_id);
    
    switch($display_as)
    {    
      case 'dropdown':
          $attributes = array('class'=>'form-control ' . $cfg->get('width') . ' field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
          
          return select_tag('fields[' . $field['id'] . ']',$choices,$value,$attributes);
        break;
      case 'checkboxes':      
          $attributes = array('class'=>'field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
          
          return '<div class="checkboxes_list ' . ($field['is_required']==1 ? ' required':'') . '">' . select_checkboxes_tag('fields[' . $field['id'] . ']',$choices,$value,$attributes) . '</div>';
        break;
      case 'dropdown_muliple':        
          $attributes = array('class'=>'form-control chosen-select ' . $cfg->get('width') . ' field_' . $field['id'] . ($field['is_required']==1 ? ' required':''),
                              'multiple'=>'multiple',
                              'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
          
          return select_tag('fields[' . $field['id'] . '][]',$choices,explode(',',$value),$attributes);
        break;
    }
  }
  
  function process($options)
  {
    global $app_send_to, $app_send_to_new_assigned;
    
    $value = (is_array($options['value']) ? implode(',',$options['value']) : $options['value']);
    
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    if($cfg->get('disable_notification')!=1)
    {
	    foreach(explode(',',$value) as $choices_id)
	    {
	    	if($cfg->get('use_global_list')>0)
	    	{
	    		$choice_query = db_query("select * from app_global_lists_choices where id='" . db_input($choices_id) . "' and lists_id = '" . db_input($cfg->get('use_global_list')) . "' and length(users)>0");
	    	}
	    	else
	    	{	
	      	$choice_query = db_query("select * from app_fields_choices where id='" . db_input($choices_id) . "' and length(users)>0");
	    	}
	      
	      if($choice = db_fetch_array($choice_query))
	      {            
	        foreach(explode(',',$choice['users']) as $id)
	        {
	          $app_send_to[] = $id;
	        }
	        
	        //check if value changed
	        if(!$options['is_new_item'])
	        {                      
	          if(!in_array($choices_id,explode(',',$options['current_field_value'])))
	          {   
	            foreach(explode(',',$choice['users']) as $id)
	            {                    
	              $app_send_to_new_assigned[] = $id;
	            }                              
	          }
	        }
	        
	      }
	    } 
    }
                           
    return $value;
  }
  
  function output($options)
  {   	  	
  	$cfg = new fields_types_cfg($options['field']['configuration']);
  	            
    $html = ($cfg->get('use_global_list')>0 ? global_lists::render_value($options['value']) : fields_choices::render_value($options['value']));       
    
    if(!isset($options['is_export']))
    	$html .= $this->show_users($options);
    
    return $html;
  } 
  
  function show_users($options)
  {
  	global $app_users_cache;
  	
  	$html =  '';
  	
  	$cfg = new fields_types_cfg($options['field']['configuration']);  	
  	
  	$show_users = (is_array($cfg->get('show_users')) ? $cfg->get('show_users'):[]);
  	 
  	if((isset($options['is_listing']) and in_array('listing',$show_users)) or (!isset($options['is_listing']) and in_array('info_page',$show_users)))
  	{
  		$users_list = [];
  		 
  		if(strlen($options['value']))
  			foreach(explode(',',$options['value']) as $choices_id)
  			{
  				if($cfg->get('use_global_list')>0)
  				{
  					$choice_query = db_query("select * from app_global_lists_choices where id='" . db_input($choices_id) . "' and lists_id = '" . db_input($cfg->get('use_global_list')) . "' and length(users)>0");
  				}
  				else
  				{
  					$choice_query = db_query("select * from app_fields_choices where id='" . db_input($choices_id) . "' and length(users)>0");
  				}
  		   
  				if($choice = db_fetch_array($choice_query))
  				{
  					foreach(explode(',',$choice['users']) as $id)
  					{
  						$users_list[] = $id;
  					}
  				}
  			}
  		 
  		if(count($users_list))
  		{
  			$current_group_name = '';
  			
  			$html .= '<div class="grouped_users_list">';
  			foreach($users_list as $users_id)
  			{
  				if(!isset($app_users_cache[$users_id])) continue;
  				
  				if($cfg->get('show_users_access_group')==1 and $current_group_name!=$app_users_cache[$users_id]['group_name'])
  				{
  					$current_group_name=$app_users_cache[$users_id]['group_name'];
  					
  					$html .= '<span class="grouped_users_group_name">' . $current_group_name . '</span><br>';
  				}
  				
  				$html .= '<span ' . users::render_publi_profile($app_users_cache[$users_id],true). '> - ' . $app_users_cache[$users_id]['name']. '</span><br>';
  			}
  		}
  		 
  		$html .= '</div>';
  	}
  	
  	return $html;
  	
  }
  
	function reports_query($options)
  {  	  
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
      
		if(strlen($filters['filters_values'])>0)
    {  
      $sql_query[] = "(select count(*) from app_entity_" . $options['entities_id'] . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($options['filters']['fields_id'])  . "' and cv.value in (" . $filters['filters_values'] . ")) " . ($filters['filters_condition']=='include' ? '>0': '=0');
    }
    
    return $sql_query;
  } 
  
  static function get_send_to($value, $cfg)
  {
  	$send_to = [];
  	
  	if(strlen($value)>0)
  	{
  		foreach(explode(',',$value) as $choices_id)
  		{
  			if($cfg->get('use_global_list')>0)
  			{
  				$choice_query = db_query("select * from app_global_lists_choices where id='" . db_input($choices_id) . "' and lists_id = '" . db_input($cfg->get('use_global_list')) . "' and length(users)>0");
  			}
  			else
  			{
  				$choice_query = db_query("select * from app_fields_choices where id='" . db_input($choices_id) . "' and length(users)>0");
  			}
  				
  			if($choice = db_fetch_array($choice_query))
  			{
  				foreach(explode(',',$choice['users']) as $id)
  				{
  					$send_to[] = $id;
  				}
  			}
  		}
  	}
  	
  	return $send_to;
  }
}