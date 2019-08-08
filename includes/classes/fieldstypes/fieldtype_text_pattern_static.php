<?php

class fieldtype_text_pattern_static
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_TEXT_PATTERN_STATIC);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
    $cfg[] = array('title'=>TEXT_PATTERN . fields::get_available_fields_helper($_POST['entities_id'], 'fields_configuration_pattern'), 
                   'name'=>'pattern',
                   'type'=>'textarea',    
                   'tooltip'=>TEXT_ENTER_TEXT_PATTERN_INFO,
                   'params'=>array('class'=>'form-control'));  
    
    $cfg[] = array('title'=>TEXT_ALLOW_SEARCH, 'name'=>'allow_search','type'=>'checkbox','tooltip_icon'=>TEXT_ALLOW_SEARCH_TIP);
    
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {        
    return '';
  }
  
  function process($options)
  {
    return '';
  }
  
	function output($options)
	{
		return $options['value'];
	}
	
	static function set($entities_id, $items_id)
	{
		global $sql_query_having, $app_changed_fields, $app_choices_cache;
		 
		$fields_query = db_query("select * from app_fields where entities_id='" . db_input($entities_id) . "' and type='fieldtype_text_pattern_static'");
		while($fields = db_fetch_array($fields_query))
		{
			$item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($entities_id, '')  . fieldtype_related_records::prepare_query_select($entities_id, ''). " from app_entity_" . $entities_id . " e where e.id='" . db_input($items_id) . "'");
			if($item_info = db_fetch_array($item_info_query))
			{
				$options = array(
						'field'=>$fields,
						'item'=>$item_info,
						'path' => $entities_id . '-' . $items_id,
				);
				
				$fieldtype_text_pattern = new fieldtype_text_pattern;
				$value = $fieldtype_text_pattern->output($options);
				
				db_query("update app_entity_" . $entities_id . " set field_" . $fields['id'] . " = '" . db_input($value) . "' where id='" . $items_id . "'");
			}
		}
	}  
}