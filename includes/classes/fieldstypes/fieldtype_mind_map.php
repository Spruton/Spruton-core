<?php

class fieldtype_mind_map
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_MIND_MAP_TITLE);
        
  }
  
  function get_configuration()
  {
    $cfg = array();
        
    $cfg[] = array('name'=>'hide_field_if_empty','type'=>'hidden','default'=>1);
    
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {
    $cfg =  new fields_types_cfg($field['configuration']);
    
    $attributes = array();
    
    if($obj['field_' . $field['id']]==1) $attributes['checked'] = true;
            
    return  '<p class="form-control-static">' . input_checkbox_tag('fields[' . $field['id'] . ']',1,$attributes) . '</p>';
  }
  
  function process($options)
  {
    return db_prepare_input($options['value']);
  }
  
  function output($options)
  {
  	if(isset($options['is_listing']) or isset($options['is_export']))
  	{
  		return '';
  	}
  	elseif($options['value']==1)
  	{  		
  		 return '
      		<div class="mind-map-iframe-box mind-map-iframe-box-' . $options['field']['id'] . '">
  		 			<div class="mind-map-fullscreen-action" data_field_id="' . $options['field']['id'] . '"><i class="fa fa-arrows-alt"></i></div>
      			<iframe src="' . url_for('mind_map/single','items_id=' . $options['item']['id'] . '&entities_id=' . $options['field']['entities_id'] . '&fields_id=' . $options['field']['id']) . '" class="mind-map-iframe mind-map-iframe-' . $options['field']['id'] . '" scrolling="no" frameborder="no"></iframe>
      		</div>
      		<script>

					 $(function(){						 					
						 $( window ).resize(function() {
							 resize_mind_map_iframe_field(' .  $options['field']['id'] . ')
						 });							 															 									 
					 })
					 
					</script>				
      		';
  	}
  	else
  	{
    	return '';
  	}
  }
}