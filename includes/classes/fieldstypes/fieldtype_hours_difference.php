<?php

class fieldtype_hours_difference
{
	public $options;

	function __construct()
	{
		$this->options = array('title' => TEXT_FIELDTYPE_HOURS_DIFFERENCE_TITLE);
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
		
		$choices = array();
		for($i = 0;$i<24;$i++)
		{
			$choices[$i] = (strlen($i)==1 ? '0' . $i : $i) . ':00';
		}
		$cfg[] = array('title'=>TEXT_WORKING_HOURS,'name'=>'include_hours','tooltip_icon'=>TEXT_WORKING_HOURS_INFO,'default'=>'','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));				
		$cfg[] = array('title'=>TEXT_EXCLUDE_WEEK_DAYS,'name'=>'exclude_days','default'=>'','type'=>'dropdown','choices'=>app_get_mysql_days_choices(),'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));
		$cfg[] = array('title'=>TEXT_EXCLUDE_HOLIDAYS, 'name'=>'exclude_holidays','type'=>'checkbox');
		
		$cfg[] = array('title'=>tooltip_icon(TEXT_CALCULATE_TOTALS_INFO) . TEXT_CALCULATE_TOTALS, 'name'=>'calclulate_totals','type'=>'checkbox');
		$cfg[] = array('title'=>TEXT_CALCULATE_AVERAGE_VALUE, 'name'=>'calculate_average','type'=>'checkbox');
		
		$cfg[] = array('title'=>tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT, 'name'=>'number_format','type'=>'input','params'=>array('class'=>'form-control input-small input-masked','data-mask'=>'9/~/~'), 'default'=>CFG_APP_NUMBER_FORMAT);
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
				
		if(strlen($cfg->get('number_format'))>0 and strlen($options['value'])>0)
		{
			$format = explode('/',str_replace('*','',$cfg->get('number_format')));
		
			$value = number_format($options['value'],$format[0],$format[1],$format[2]);
		}
		else 
		{	
			$value = $options['value'];
		}		
		
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
CREATE FUNCTION `spruton_hours_diff`(`start_date` INT, `end_date` INT, `include_hours` VARCHAR(255), `exclude_days` VARCHAR(64), `exclude_holidays` TINYINT(1)) RETURNS FLOAT
BEGIN
  DECLARE minutes_diff INT;
  DECLARE inc_minue TINYINT;		
	DECLARE minutes_start TINYINT;
	DECLARE use_minutes_start TINYINT;
	DECLARE minutes_end TINYINT;
										  
  IF start_date>0 and end_date>0 and end_date>start_date THEN  
		
			#skip while if no restriction
			IF length(exclude_days)=0 and length(include_hours)=0 and exclude_holidays!=1 THEN
				RETURN (end_date-start_date)/3600;
			END IF;
		
			SET minutes_diff = 0;
			SET use_minutes_start = 0;
		
			SET minutes_start = MINUTE(FROM_UNIXTIME(start_date,'%Y-%m-%d %H:%i'));
			SET minutes_end = MINUTE(FROM_UNIXTIME(end_date,'%Y-%m-%d %H:%i'));
		
			SET start_date = start_date-(minutes_start*60);
		
  	  WHILE start_date<end_date  DO 
      	SET inc_minue=1;
      					
        #exclude day of week
      	IF find_in_set(DAYOFWEEK(FROM_UNIXTIME(start_date,'%Y-%m-%d')),exclude_days) THEN
      		SET inc_minue=0;  
      	END IF;
		
				#exclude not working hours
				IF find_in_set(HOUR(FROM_UNIXTIME(start_date,'%Y-%m-%d  %H:%i')),include_hours)=0 and length(include_hours)>0 THEN
      		SET inc_minue=0;					
      	END IF;
                        
        #exclude holidays
        IF exclude_holidays=1 THEN
        	SET @start_date_var = FROM_UNIXTIME(start_date,'%Y-%m-%d'); 
        	SET @is_holiday = (select count(*) from app_holidays h where h.start_date<= @start_date_var and h.end_date>=@start_date_var);
            if @is_holiday!=0 THEN
            SET inc_minue=0;  
            END if;
        END IF;
        
      	IF inc_minue=1 THEN
      		SET minutes_diff =minutes_diff+60; 							
      	END IF;
		
				#handle first hour minute
				IF inc_minue=1 and use_minutes_start=0 THEN
					SET use_minutes_start=1;
				ELSEIF use_minutes_start=0 THEN
					SET use_minutes_start=2;
				END IF;
        
      	SET start_date = start_date+3600;      
        
      END WHILE;
		
		#handle start minutes
		IF use_minutes_start=1 THEN
			SET minutes_diff =minutes_diff-minutes_start;
		END IF;
		
		#handle end minutes
		IF minutes_end>0 and inc_minue=1 THEN
			SET minutes_diff =(minutes_diff-60)+minutes_end;
		END IF;
		
  END IF;
					
	IF minutes_diff>0 THEN	
  	RETURN minutes_diff/60;
	ELSE
		RETURN 0;
	END IF;	
		
END;";
		
		$is_function = false;
		$check_query = db_query("SHOW FUNCTION STATUS WHERE Db = '" . DB_DATABASE . "'");
		while($check = db_fetch_array($check_query))
		{
			if($check['Name']=='spruton_hours_diff')
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
				if($fields['type']=='fieldtype_hours_difference')
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
		
		$start_date_field = ($cfg->get('start_date')=='today' ? time() : ($cfg->get('start_date')=='date_added' ? $prefix . '.date_added' :  $prefix . '.field_' . $cfg->get('start_date')));
		$end_date_field = ($cfg->get('end_date')=='today' ? time() : ($cfg->get('end_date')=='date_added' ? $prefix . '.date_added' :  $prefix . '.field_' . $cfg->get('end_date')));
		$exclude_days = (is_array($cfg->get('exclude_days')) ? implode(',',$cfg->get('exclude_days')):'');
		$include_hours = (is_array($cfg->get('include_hours')) ? implode(',',$cfg->get('include_hours')):'');
		
		if($single_select)
  	{
  		$mysql_query =  "(spruton_hours_diff(" . $start_date_field . "," . $end_date_field . ",'" . $include_hours . "','" . $exclude_days . "','" . $cfg->get('exclude_holidays') . "'))";  		
  	}
  	else
  	{
  		$mysql_query =  "spruton_hours_diff(" . $start_date_field . "," . $end_date_field . ",'" . $include_hours . "','" . $exclude_days . "','" . $cfg->get('exclude_holidays') . "') as field_" . $fields['id'];
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
				if($fields['type']=='fieldtype_hours_difference')
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