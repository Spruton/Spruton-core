<?php

class fieldtype_input_protected
{
	public $options;

	function __construct()
	{
		$this->options = array('title' => TEXT_FIELDTYPE_INPUT_PROTECTED_TITLE);
	}

	function get_configuration()
	{
		$cfg = array();
		
		$cfg[] = array('title'=>TEXT_REPLACE_WITH_SYMBOL, 'name'=>'replace_symbol','type'=>'input','tooltip'=>TEXT_DEFAULT . ': X','params'=>array('class'=>'form-control input-small'));
		
		$cfg[] = array('title'=>TEXT_DISCLOSE_NUMBER_FIRST_LETTERS, 'name'=>'count_first_letters','type'=>'input','params'=>array('class'=>'form-control input-small number'));
		$cfg[] = array('title'=>TEXT_DISCLOSE_NUMBER_LAST_LETTERS, 'name'=>'count_last_letters','type'=>'input','params'=>array('class'=>'form-control input-small number'));
		
		
		$choices = [];
		$choices[0] = TEXT_ADMINISTRATOR;
		
		$groups_query = db_fetch_all('app_access_groups','','sort_order, name');
		while($groups = db_fetch_array($groups_query))
		{
			$entities_access_schema = users::get_entities_access_schema($_POST['entities_id'],$groups['id']);
		
			if(!in_array('view',$entities_access_schema) and  !in_array('view_assigned',$entities_access_schema)) continue;
			
			$choices[$groups['id']] = $groups['name'];
		}
		
		$cfg[] = array('title'=>TEXT_USERS_GROUPS,
				'name'=>'users_groups',
				'type'=>'dropdown',
				'choices'=>$choices,
				'tooltip_icon'=>TEXT_FIELDTYPE_INPUT_PROTECTED_USERS_GROUPS_TIP,
				'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));
		
		$cfg[] = array('title'=>TEXT_ALLOW_SEARCH, 'name'=>'allow_search','type'=>'checkbox','tooltip_icon'=>TEXT_ALLOW_SEARCH_TIP);

		$cfg[] = array('title'=>TEXT_WIDHT,
				'name'=>'width',
				'type'=>'dropdown',
				'choices'=>array('input-small'=>TEXT_INPTUT_SMALL,'input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE),
				'tooltip_icon'=>TEXT_ENTER_WIDTH,
				'params'=>array('class'=>'form-control input-medium'));
		 
		$cfg[] = array('title'=>TEXT_HIDE_FIELD_IF_EMPTY, 'name'=>'hide_field_if_empty','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_IF_EMPTY_TIP);

		$cfg[] = array('title'=>TEXT_IS_UNIQUE_FIELD_VALUE, 'name'=>'is_unique','type'=>'checkbox','tooltip_icon'=>TEXT_IS_UNIQUE_FIELD_VALUE_TIP);
		$cfg[] = array('title'=>TEXT_ERROR_MESSAGE, 'name'=>'unique_error_msg','type'=>'input','tooltip_icon'=>TEXT_UNIQUE_FIELD_VALUE_ERROR_MSG_TIP,'tooltip'=>TEXT_DEFAULT . ': ' . TEXT_UNIQUE_FIELD_VALUE_ERROR,'params'=>array('class'=>'form-control input-xlarge'));

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

		$attributes = fields_types::prepare_uniquer_error_msg_param($attributes,$cfg);

		return input_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']],$attributes);
	}

	function process($options)
	{
		return db_prepare_input($options['value']);
	}

	function output($options)
	{					
		global $app_user;
		
		if(!strlen($options['value'])) return '';
						
		$cfg = new fields_types_cfg($options['field']['configuration']);
		
		$users_groups = $cfg->get('users_groups');
		
		if(is_array($users_groups))
		{
			if(in_array($app_user['group_id'],$users_groups))
			{
				return $options['value'];
			}
		}
				
		$value_array = str_split($options['value']);
		$value_str = '';
		
		$replace_symbol = strlen($cfg->get('replace_symbol')) ? $cfg->get('replace_symbol') : 'X';
		$count_first_letters = strlen($cfg->get('count_first_letters')) ? (int)$cfg->get('count_first_letters') : 0;
		$count_last_letters = strlen($cfg->get('count_last_letters')) ? (int)$cfg->get('count_last_letters') : 0;
		
		for($i=0;$i<count($value_array);$i++)
		{
			if(($i<$count_first_letters and $count_first_letters>0) or ($i>=(count($value_array)-$count_last_letters) and $count_last_letters>0))
			{
				$value_str .= $value_array[$i];
			}
			else
			{
				$value_str .= $replace_symbol;
			}
		}
				
		return $value_str;
	}
}