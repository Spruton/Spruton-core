<?php

class fieldtype_image_map
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_IMAGE_MAP_TITLE,'has_choices'=>true);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
        
    $cfg[] = array('title'=>TEXT_DEFAULT_TEXT, 
                   'name'=>'default_text',
                   'type'=>'input',                   
                   'tooltip_icon'=>TEXT_DEFAULT_TEXT_INFO,
                   'params'=>array('class'=>'form-control input-medium'));
    
    $cfg[] = array('title'=>TEXT_WIDHT, 
                   'name'=>'width',
                   'type'=>'dropdown',
                   'choices'=>array('input-small'=>TEXT_INPTUT_SMALL,'input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
                   'tooltip_icon'=>TEXT_ENTER_WIDTH,
                   'params'=>array('class'=>'form-control input-medium'));
           
    $cfg[] = array('title'=>TEXT_MAP_SETTINGS,'type'=>'section');
           
    $choices = [    		
    		'6' => '1%',
    		'5' => '3%',
    		'4' => '6%',
    		'3' => '12%',
    		'2' => '25%',
    		'1' => '50%',    		
    		'0' => '100%',    		
    ];
    
    $cfg[] = array('title'=>TEXT_SCALE,'name'=>'scale','default'=>3,'type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-small'));    
    $cfg[] = array('title'=>TEXT_WIDHT,'name'=>'map_width','type'=>'input','params'=>array('class'=>'form-control input-small'));
    $cfg[] = array('title'=>TEXT_HEIGHT,'name'=>'map_height','type'=>'input','params'=>array('class'=>'form-control input-small'));
    
    //fields in popup   
  	$exclude_types = array("'fieldtype_action'","'fieldtype_parent_item_id'","'fieldtype_related_records'","'fieldtype_mapbbcode'","'fieldtype_section'","'fieldtype_image_map'");
    $choices = array();       
    $fields_query = db_query("select * from app_fields where type not in (" . implode(",",$exclude_types) . ") and entities_id='" . db_input($_POST['entities_id']) . "'");
    while($fields = db_fetch_array($fields_query))
    {
     	$choices[$fields['id']] = $fields['name'];
    }
        
    $cfg[] = array('title'=>TEXT_FIELDS_IN_POPUP,'name'=>'fields_in_popup','default'=>'','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));
    
    //background
    $choices = array();
    $choices[''] = '';
    $fields_query = db_query("select * from app_fields where type in ('fieldtype_dropdown','fieldtype_radioboxes') and entities_id='" . db_input($_POST['entities_id']) . "'");
    while($fields = db_fetch_array($fields_query))
    {
    	$choices[$fields['id']] = $fields['name'];
    }
     
    if(count($choices)>1)
    {
    	$cfg[] = array('title'=>TEXT_BACKGROUND_COLOR ,'name'=>'background','tooltip_icon'=>TEXT_FIELDTYPE_IMAGE_MAP_BACKGROUND_COLOR_INFO,'type'=>'dropdown','choices'=>$choices,
    								 'params'=>array('class'=>'form-control input-medium','onChange'=>'fields_types_ajax_configuration(\'background_icons\',this.value)'));
        
    	$cfg[] = array('name'=>'background_icons','type'=>'ajax','html'=>'<script>fields_types_ajax_configuration(\'background_icons\',$("#fields_configuration_background").val())</script>');
    }    
                                 
    return $cfg;
  }  
  
  function get_ajax_configuration($name, $value)
  {
  	$cfg = array();
  	
  	switch($name)
  	{
  		case 'background_icons':
  			if(strlen($value))
  			{
  				$choices = fields_choices::get_choices($value,false);
  				if(count($choices))
  				{
	  				$cfg[] = array('title'=>TEXT_ICONS,'type'=>'section','html'=>'<p class="form-section-description">' . TEXT_FIELDTYPE_IMAGE_MAP_ICONS_TIP . '</p>');
	  				
	  				foreach($choices as $k=>$v)
	  				{
	  					$cfg[] = array('title'=>$v,'name'=>'icon_' . $k,'type'=>'file','params'=>array('class'=>'form-control input-large'));
	  				}
  				}
  			}
  			break;
  	}
  	
  	return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {
    $cfg = new fields_types_cfg($field['configuration']);
            
    $attributes = array('class'=>'form-control ' . $cfg->get('width') . ' field_' . $field['id'] . ($field['is_required']==1 ? ' required':'') . ($cfg->get('use_search')==1 ? ' chosen-select':''),
                        'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
                                 
    $choices = [];
    
    if($field['is_required']==0 or strlen($cfg->get('default_text')))
    {
    	$choices[''] = $cfg->get('default_text');
    }
    
    $choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($field['id']). "' and parent_id=0 order by sort_order, name");        
    while($v = db_fetch_array($choices_query))
    {
    	$choices[$v['id']] = $v['name'];
    }
        
    $default_id = fields_choices::get_default_id($field['id']); 
        
    $value = ($obj['field_' . $field['id']]>0 ? $obj['field_' . $field['id']] : ($params['form']=='comment' ? '':$default_id)); 
    
    return select_tag('fields[' . $field['id'] . ']',$choices,$value,$attributes);
  }
  
  function process($options)
  {
    global $app_changed_fields, $app_choices_cache, $app_global_choices_cache;
      
    return $options['value'];
  }
  
  function output($options)
  {    
  	global $app_choices_cache;
  	
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    //check if value exist
    if($options['value']==0 or !isset($app_choices_cache[$options['value']])) return '';
            
    if(isset($options['is_listing']) or isset($options['is_export']))
    {
    	return fields_choices::render_value($options['value']);
    }
    else 
    {    	    	  
    	$width_css = (strlen($cfg->get('map_width')) ? 'style="max-width: ' . $cfg->get('map_width') . 'px"':'');
    	$height_css = (strlen($cfg->get('map_height')) ? 'style="height: ' . $cfg->get('map_height') . 'px"':'');
    	
      return '
      		<div class="image-map-iframe-box" ' . $width_css . '>
      			<iframe src="' . url_for('image_map/single','path=' . $options['path'] . '&map_id=' . $options['value'] . '&fields_id=' . $options['field']['id']) . '" class="image-map-iframe" scrolling="no" frameborder="no" ' . $height_css . '></iframe>
      		</div>';
    }
    
  }  
  
  function reports_query($options)
  {
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
    
    $prefix = (strlen($options['prefix']) ? $options['prefix'] : 'e');
  
    $sql_query[] = $prefix . '.field_' . $filters['fields_id'] .  ($filters['filters_condition']=='include' ? ' in ': ' not in ') .'(' . $filters['filters_values'] . ') ';
    
    return $sql_query;
  }
  
  static function upload_map_filename($choices_id)
  {  	  	
  	//upload
  	if(isset($_FILES['filename']['name']))
  	if(strlen($_FILES['filename']['name'])>0)
  	{  		
  		$map_dir = DIR_WS_UPLOADS  . 'maps/' .$choices_id;
  		
  		//check dir
  		if(!is_dir($map_dir))
  		{
  			mkdir($map_dir);
  		}
  		
  		//delete exist files in map dir
  		foreach(glob($map_dir .'/*') as $file)
  		{
  			if(is_file($file)) unlink($file);
  		}
  		
  		$filename = str_replace(' ','_',$_FILES['filename']['name']); 
  		$upload_filepath = $map_dir . '/' . $filename;
  		
  		if(move_uploaded_file($_FILES['filename']['tmp_name'], $upload_filepath))
  		{  	
  			//update db
  			db_query("update app_fields_choices set filename = '" . db_input($filename) . "' where id = '" . db_input($choices_id). "'");
  			
  			require('includes/libs/openzoom/GdThumb.php');
  			require('includes/libs/openzoom/OzDeepzoomImageCreator.php');
  			require('includes/libs/openzoom/OzDeepzoomDescriptor.php');
  			 
  			//prepare image
  			$mapCreator = @new Flexphperia_OzDeepzoomImageCreator($upload_filepath, $map_dir);
  			@$mapCreator->create();
  			
  		}
  	}  	  	
  }
  
  static function delete_map_files($choices_id)
  {
  	$map_dir = DIR_WS_UPLOADS  . 'maps/' .$choices_id;
  	
  	if(is_dir($map_dir))
  	{
	  	//delete exist files in map dir
	  	foreach(glob($map_dir .'/*') as $file)
	  	{
	  		if(is_file($file)) unlink($file);
	  	}
	  	
	  	rmdir($map_dir);
  	}
  	
  	//remove markers
  	db_query("delete from app_image_map_markers where map_id='" . $choices_id . "'");
  }
  
}