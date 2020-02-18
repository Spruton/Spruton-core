<?php

class fieldtype_years_difference
{
	public $options;

	function __construct()
	{
		$this->options = array('title' => TEXT_FIELDTYPE_YEARS_DIFFERENCE_TITLE);
	}

	function get_configuration()
	{
		$cfg = array();
			
		$cfg[] = array('title'=>tooltip_icon(TEXT_FIELDTYPE_DAYS_DIFFERENCE_DINAMIC_INFO) . TEXT_FIELDTYPE_MYSQL_QUERY_DINAMIC_QUERY, 'name'=>'dinamic_query','type'=>'dropdown','choices'=>['0'=>TEXT_NO,'1'=>TEXT_YES],'params'=>array('class'=>'form-control input-small'));

		$choices = array();
		$choices['today'] = '[' . TEXT_CURRENT_DATE .']';
		$choices['date_added'] = '[' . TEXT_DATE_ADDED .']';
		$fields_query = db_query("select * from app_fields where type in ('fieldtype_input_date','fieldtype_input_datetime','fieldtype_dynamic_date') and entities_id='" . db_input($_POST['entities_id']) . "'");
		while($fields = db_fetch_array($fields_query))
		{
			$choices[$fields['id']] = $fields['name'];
		}

		$cfg[] = array('title'=>TEXT_START_DATE,'name'=>'start_date','default'=>'','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-large chosen-select required'));
		$cfg[] = array('title'=>TEXT_END_DATE,'name'=>'end_date','default'=>'','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-large chosen-select required'));
		
		$cfg[] = array('title'=>TEXT_CALCULATE_DIFFERENCE_DAYS, 'name'=>'calclulate_diff_days','type'=>'checkbox');
		
		$cfg[] = array('title'=>tooltip_icon(TEXT_CALCULATE_TOTALS_INFO) . TEXT_CALCULATE_TOTALS, 'name'=>'calclulate_totals','type'=>'checkbox');
		$cfg[] = array('title'=>TEXT_CALCULATE_AVERAGE_VALUE, 'name'=>'calculate_average','type'=>'checkbox');

		$cfg[] = array('title'=>TEXT_PREFIX,'name'=>'prefix','type'=>'input','params'=>array('class'=>'form-control input-small'));
		$cfg[] = array('title'=>TEXT_SUFFIX,'name'=>'suffix','type'=>'input','params'=>array('class'=>'form-control input-small'));

		self::prepare_procedure();

		return $cfg;
	}

	function render($field,$obj,$params = array())
	{
		return false;
	}

	function process($options)
	{
		return false;
	}

	function output($options)
	{
		$cfg = new fields_types_cfg($options['field']['configuration']);

		$value =  $options['value'];

		//add prefix and sufix
		$value = (strlen($value) ? $cfg->get('prefix') . $value . $cfg->get('suffix') : '');

		return $value;
	}

	function reports_query($options)
	{
		global $sql_query_having;

		$filters = $options['filters'];
		$sql_query = $options['sql_query'];

		$sql = reports::prepare_numeric_sql_filters($filters,'');

		if(count($sql)>0)
		{
			$sql_query_having[$options['entities_id']][] =  implode(' and ', $sql);
		}

		return $sql_query;
	}

	static function prepare_procedure()
	{

		$sql = "
CREATE FUNCTION `spruton_years_diff`(`start_date` INT, `end_date` INT, `inc_days` TINYINT(1)) RETURNS int(11)
BEGIN  				
  DECLARE years_diff INT;  
  SET years_diff=0;
	
	IF inc_days=1 THEN			
		SET years_diff = TIMESTAMPDIFF(YEAR, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0),INTERVAL start_date SECOND),'%Y-%m-%d') , DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0),INTERVAL end_date SECOND),'%Y-%m-%d') );
	ELSE
		SET years_diff = YEAR(DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0),INTERVAL end_date SECOND),'%Y-%m-%d')) -  DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0),INTERVAL start_date SECOND),'%Y-%m-%d');		
	END IF;
				  
  RETURN years_diff;
END;";
				
		$is_function = false;
		$check_query = db_query("SHOW FUNCTION STATUS WHERE Db = '" . DB_DATABASE . "'");
		while($check = db_fetch_array($check_query))
		{
			if($check['Name']=='spruton_years_diff')
			{
				$is_function = true;
			}
		}

		if(!$is_function)
		{
			db_query($sql);
		}

	}

	public static function prepare_query_select($entities_id,$listing_sql_query_select, $prefix = 'e')
	{
		global $app_fields_cache;

		if(isset($app_fields_cache[$entities_id]))
		{
			foreach($app_fields_cache[$entities_id] as $fields)
			{
				if($fields['type']=='fieldtype_years_difference')
				{
					$cfg = new fields_types_cfg($fields['configuration']);
						
					//skip dynamic query
					if(isset($cfg->cfg['dinamic_query']) and $cfg->get('dinamic_query')!=1) continue;

					//array to calculate totals in listing
					if(is_array($listing_sql_query_select))
					{
						$listing_sql_query_select[] = self::prepare_query($fields,$prefix);
					}
					else
					{
						$listing_sql_query_select .= ',' . self::prepare_query($fields,$prefix);
					}
				}
			}
		}
			
		return $listing_sql_query_select;
	}

	public static function prepare_query($fields,$prefix = 'e', $single_select = false, $force_query = false)
	{
		$cfg = new fields_types_cfg($fields['configuration']);
		
		//skip dynamic query
		if(isset($cfg->cfg['dinamic_query']) and $cfg->get('dinamic_query')!=1 and !$force_query) return 'e.field_' . $fields['id'];

		$start_date_field = ($cfg->get('start_date')=='today' ? time() : ($cfg->get('start_date')=='date_added' ? $prefix . '.date_added' : $prefix . '.field_' . $cfg->get('start_date')));
		$end_date_field = ($cfg->get('end_date')=='today' ? time() : ($cfg->get('end_date')=='date_added' ? $prefix . '.date_added' : $prefix . '.field_' . $cfg->get('end_date')));		
		
		if($single_select)
		{
			$mysql_query =  "(spruton_years_diff(" . $start_date_field . "," . $end_date_field . "," . $cfg->get('calclulate_diff_days',0). "))";
		}
		else
		{
			$mysql_query =  "spruton_years_diff(" . $start_date_field . "," . $end_date_field . "," . $cfg->get('calclulate_diff_days',0). ") as field_" . $fields['id'];
		}

		return $mysql_query;
	}

	public static function update_items_fields($entities_id, $items_id)
	{
		global $app_fields_cache;

		if(isset($app_fields_cache[$entities_id]))
		{
			foreach($app_fields_cache[$entities_id] as $fields)
			{
				if($fields['type']=='fieldtype_years_difference')
				{
					$cfg = new fields_types_cfg($fields['configuration']);

					//skip dynamic query
					if(isset($cfg->cfg['dinamic_query']) and $cfg->get('dinamic_query')!=1)
					{
						$item_info_query = db_query("select " . self::prepare_query($fields,'e',false,true) . " from app_entity_{$entities_id} e where e.id={$items_id}");
						$item_info = db_fetch_array($item_info_query);

						$fields_id = $fields['id'];

						db_query("update app_entity_{$entities_id} set field_{$fields_id}='" . $item_info['field_' . $fields_id] . "' where id='" . db_input($items_id) . "'");
					}

				}
			}
		}
	}

}