<?php

class fieldtype_boolean_checkbox
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_BOOLEAN_CHECKBOX_TITLE);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
    $cfg[] = array('title'=>TEXT_NOTIFY_WHEN_CHANGED, 'name'=>'notify_when_changed','type'=>'checkbox','tooltip_icon'=>TEXT_NOTIFY_WHEN_CHANGED_TIP);
                 
    $cfg[] = array(
    		'title'=>TEXT_DEFAULT_VALUE,
    		'name'=>'default_value',
    		'type'=>'dropdown',
    		'choices'=>array(''=>'','true'=>TEXT_BOOLEAN_TRUE,'false'=>TEXT_BOOLEAN_FALSE),
    		'params'=>array('class'=>'form-control input-small'));
    
    $cfg[] = array('title'=>TEXT_BOOLEAN_TRUE_VALUE, 
                   'name'=>'text_boolean_true',
                   'type'=>'input',                   
                   'tooltip_icon'=>TEXT_BOOLEAN_TRUE_VALUE_INFO,
                   'params'=>array('class'=>'form-control input-small'));
    
    $cfg[] = array('title'=>TEXT_BOOLEAN_FALSE_VALUE, 
                   'name'=>'text_boolean_false',
                   'type'=>'input',                   
                   'tooltip_icon'=>TEXT_BOOLEAN_FALSE_VALUE_INFO,
                   'params'=>array('class'=>'form-control input-small'));
                                 
    
    return $cfg;
  }  
  
  function render($field,$obj,$params = array())
  {
    $cfg = new fields_types_cfg($field['configuration']);
            
    $attributes = array('class'=>'single-checkbox field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
           
    if(!strlen($obj['field_' . $field['id']])) $obj['field_' . $field['id']] = $cfg->get('default_value');
    
    if($obj['field_' . $field['id']]=='true') $attributes['checked'] = 'checked';
    
    return '<div class="form-control-static"><div class="select_checkboxes_tag single-checkbox-fields_' . $field['id'] . '">' . input_checkbox_tag('fields[' . $field['id'] . ']',1,$attributes) . '</div></div>';
  }
    
  static function get_boolean_value($field, $value)
  {
    $cfg = new fields_types_cfg($field['configuration']);
    
    switch($value)
    {
      case 'true': 
          return (strlen($cfg->get('text_boolean_true'))>0 ? $cfg->get('text_boolean_true') : TEXT_BOOLEAN_TRUE);
        break;
      case 'false':
          return (strlen($cfg->get('text_boolean_true'))>0 ? $cfg->get('text_boolean_false') : TEXT_BOOLEAN_FALSE);
        break;
      default:
          return '';
        break;  
        
    }
  }
  
  function process($options)
  {
    global $app_changed_fields, $app_choices_cache;
    
    $options['value'] = ($options['value']==1 ? 'true':'false');
    
    if(!$options['is_new_item'])
    {
      $cfg = new fields_types_cfg($options['field']['configuration']);
      
      if($options['value']!=$options['current_field_value'] and $cfg->get('notify_when_changed')==1)
      {
        $app_changed_fields[] = array(
        		'name'=>$options['field']['name'],
        		'value'=>self::get_boolean_value($options['field'], $options['value']),
        		'fields_id'=>$options['field']['id'],
        		'fields_value'=>$options['value'],
        );
      }
    }
  
    return $options['value'];
  }
  
  function output($options)
  {                
    return self::get_boolean_value($options['field'], $options['value']);    
  }  
  
  function reports_query($options)
  {
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
    
    $prefix = (strlen($options['prefix']) ? $options['prefix'] : 'e');
  
    $sql_query[] = $prefix . '.field_' . $filters['fields_id'] .  ($filters['filters_condition']=='include' ? ' = ': ' != ') . "'" . $filters['filters_values'] . "'";
    
    return $sql_query;
  }
}