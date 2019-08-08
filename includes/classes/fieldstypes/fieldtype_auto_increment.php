<?php

class fieldtype_auto_increment
{
	public $options;

	function __construct()
	{
		$this->options = array('title' => TEXT_FIELDTYPE_AUTO_INCREMENT_TITLE);
	}

	function get_configuration()
	{
		$cfg = array();

		$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_ALLOW_SEARCH, 'name'=>'allow_search','type'=>'checkbox','tooltip_icon'=>TEXT_ALLOW_SEARCH_TIP);

		$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_WIDHT,
				'name'=>'width',
				'type'=>'dropdown',
				'choices'=>array('input-small'=>TEXT_INPTUT_SMALL,'input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
				'tooltip_icon'=>TEXT_ENTER_WIDTH,
				'params'=>array('class'=>'form-control input-medium'));
		 
		$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_FIELD_IF_EMPTY, 'name'=>'hide_field_if_empty','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_IF_EMPTY_TIP);
				
		$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_IS_UNIQUE_FIELD_VALUE, 'name'=>'is_unique','type'=>'checkbox','tooltip_icon'=>TEXT_IS_UNIQUE_FIELD_VALUE_TIP);
		$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_ERROR_MESSAGE, 'name'=>'unique_error_msg','type'=>'input','tooltip_icon'=>TEXT_UNIQUE_FIELD_VALUE_ERROR_MSG_TIP,'tooltip'=>TEXT_DEFAULT . ': ' . TEXT_UNIQUE_FIELD_VALUE_ERROR,'params'=>array('class'=>'form-control input-xlarge'));
						
		$cfg[TEXT_VALUE][] = array('title'=>TEXT_VIEW_ONLY, 'name'=>'view_only','type'=>'checkbox','tooltip_icon'=>TEXT_VALUE_VIEW_ONLY_INFO);
		$cfg[TEXT_VALUE][] = array('title'=>TEXT_DEFAULT_VALUE, 'name'=>'default_value','type'=>'input','tooltip_icon'=>TEXT_DEFAULT_VALUE_INFO . '<br>' . TEXT_DEFAULT . ': 1' ,'params'=>array('class'=>'form-control input-small number'));
		$cfg[TEXT_VALUE][] = array('title'=>TEXT_PREFIX,'name'=>'prefix','type'=>'input','params'=>array('class'=>'form-control input-small'));
		$cfg[TEXT_VALUE][] = array('title'=>TEXT_SUFFIX,'name'=>'suffix','type'=>'input','params'=>array('class'=>'form-control input-small'));
		
		$entity_info = db_find('app_entities',$_POST['entities_id']);
		if($entity_info['parent_id']>0)
		{
			$cfg[TEXT_VALUE][] = array('title'=>TEXT_FIELDTYPE_AUTO_INCREMENT_SEPARATE_NUMBERING, 'name'=>'separate_numbering','type'=>'checkbox');
		}

		return $cfg;
	}

	function render($field,$obj,$params = array())
	{
		$cfg =  new fields_types_cfg($field['configuration']);

		$attributes = array('class'=>'form-control ' . $cfg->get('width') .
				' fieldtype_input field_' . $field['id'] .
				($field['is_heading']==1 ? ' autofocus':'') .
				($field['is_required']==1 ? ' required noSpace':'') .
				($cfg->get('is_unique')==1 ? ' is-unique':'')
		);
		
		if(isset($params['is_new_item']))
		{		
			if($params['is_new_item']==1)
			{
							
				$where_sql = '';
				
				//handle separate numbering for each parent recored
				if(isset($params['parent_entity_item_id']))
				{
					if($params['parent_entity_item_id']>0 and $cfg->get('separate_numbering')==1)
					{
						$where_sql .= " where parent_item_id='" . $params['parent_entity_item_id'] . "'";
					}
				}
				
				$check_query = db_query("select (max(field_{$field['id']}+0)+1) as max_value from app_entity_{$field['entities_id']} " . $where_sql);
				$check = db_fetch_array($check_query);
				
				if((int)$check['max_value']>0)
				{					
					$obj['field_' . $field['id']] = $check['max_value'];
				}
				//handle default value
				elseif($cfg->get('default_value')>0) 
				{
					$obj['field_' . $field['id']] = $cfg->get('default_value');
				}
				else
				{
					$obj['field_' . $field['id']] = 1;
				}
				
			}
		}

		$attributes = fields_types::prepare_uniquer_error_msg_param($attributes,$cfg);

		
		if($cfg->get('view_only')==1)
		{
			return '<p class="form-control-static">' . $cfg->get('prefix') . $obj['field_' . $field['id']]  . $cfg->get('suffix') . '</p>' . input_hidden_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']]);
		}
		else 
		{
			return input_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']],$attributes);
		}
		
		
	}

	function process($options)
	{
		return db_prepare_input($options['value']);
	}

	function output($options)
	{
		$cfg = new fields_types_cfg($options['field']['configuration']);
		
		return (strlen($options['value']) ? $cfg->get('prefix') . $options['value'] . $cfg->get('suffix') : '');
	}
	
	function reports_query($options)
	{
		$filters = $options['filters'];
		$sql_query = $options['sql_query'];
	
		$sql = reports::prepare_numeric_sql_filters($filters, $options['prefix']);
	
		if(count($sql)>0)
		{
			$sql_query[] =  implode(' and ', $sql);
		}
	
		return $sql_query;
	}
}