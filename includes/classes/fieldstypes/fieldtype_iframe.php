<?php

class fieldtype_iframe
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_IFRAME_TITLE);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
        
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_WIDHT, 
                   'name'=>'input_width',
                   'type'=>'dropdown',
                   'choices'=>array('input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
                   'tooltip_icon'=>TEXT_ENTER_WIDTH,
                   'params'=>array('class'=>'form-control input-medium'));
                         
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_FIELD_IF_EMPTY, 'name'=>'hide_field_if_empty','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_IF_EMPTY_TIP);
    
    $cfg['Iframe'][] = array(
    		'title'=>TEXT_WIDHT,
    		'name'=>'width',
    		'type'=>'input',    		
    		'params'=>array('class'=>'form-control input-small'));
    
    $cfg['Iframe'][] = array(
    		'title'=>TEXT_HEIGHT,
    		'name'=>'height',
    		'type'=>'input',    		
    		'params'=>array('class'=>'form-control input-small'));
    
    $cfg['Iframe'][] = array('title'=>TEXT_SCROLL_BAR,
    		'name'=>'scrolling',
    		'type'=>'dropdown',
    		'choices'=>array('auto'=>TEXT_AUTOMATIC,'no'=>TEXT_NO,'yes'=>TEXT_YES),
    		'tooltip_icon'=>TEXT_ENTER_WIDTH,
    		'params'=>array('class'=>'form-control input-medium'));
    
    $cfg['Iframe'][] = array(
    		'title'=>TEXT_EXTRA_PARAMS,
    		'name'=>'extra_params',
    		'type'=>'input',
    		'tooltip_icon' => TEXT_FIELDTYPE_IFRAME_EXTRA_PARAMS_TIP,
    		'params'=>array('class'=>'form-control input-xlarge'));
    
        
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {
    $cfg =  new fields_types_cfg($field['configuration']);
    
    $attributes = array('class'=>'form-control ' . $cfg->get('input_width') . 
                                 ' fieldtype_iframe field_' . $field['id'] .     														 
                                 ($field['is_required']==1 ? ' required noSpace':'')                                  
                                );
    
    $attributes = fields_types::prepare_uniquer_error_msg_param($attributes,$cfg);
    
    return input_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']],$attributes);
  }
  
  function process($options)
  {
    return db_prepare_input($options['value']);
  }
  
  function output($options)
  {
  	$value = trim($options['value']); 
  	if(isset($options['is_export']))
  	{
  		return $value;
  	}
  	elseif(strlen($value))
  	{
  		$cfg = new fields_types_cfg($options['field']['configuration']);
  		
  		return '<iframe  src="' . $value . '" width="' . $cfg->get('width'). '"  height="' . $cfg->get('height'). '" scrolling="' . $cfg->get('scrolling') . '" ' .  $cfg->get('extra_params') . '></iframe>';
  	}
  	else
  	{
  		return '';
  	}
    
  }
}