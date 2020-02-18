<?php

class fieldtype_date_updated
{
	public $options;

	function __construct()
	{
		$this->options = array('name'=>TEXT_FIELDTYPE_DATE_UPDATED_TITLE, 'title'=>TEXT_FIELDTYPE_DATE_UPDATED_TITLE);
	}

	function output($options)
	{
		return ($options['value']>0 ? format_date_time($options['value']) : '');
	}

	function reports_query($options)
	{
		$filters = $options['filters'];
		$sql_query = $options['sql_query'];

		$sql = reports::prepare_dates_sql_filters($filters,$options['prefix']);

		if(count($sql)>0)
		{
			$sql_query[] =  implode(' and ', $sql);
		}

		return $sql_query;
	}
}