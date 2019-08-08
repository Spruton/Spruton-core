<?php

class settings
{
	private $settings;
	
	function __construct($settings)
	{		
		$this->settings = [];
		
		if(strlen($settings))
		{
			$this->settings = json_decode($settings,true);
		}			
	}
	
	function get_settings()
	{
		return $this->settings;
	}
	
	function get($k, $default = '')
	{
		if(isset($this->settings[$k]))
		{
			return $this->settings[$k];
		}
		else
		{
			return $default;
		}
	}
}