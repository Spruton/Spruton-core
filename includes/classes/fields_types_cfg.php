<?php

class fields_types_cfg
{
  public $cfg;
  
  function __construct($configuration)
  {
    if(strlen($configuration)>0)
    {
      $this->cfg = json_decode($configuration,true);
    }
    else
    {
      $this->cfg = array();
    }
  }
  
  function has($key)
  {
    if(isset($this->cfg[$key]))
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
  function get($key, $defautl = '')
  {
    if(isset($this->cfg[$key]))
    {
      return $this->cfg[$key];
    }
    else
    {
      return $defautl;
    }
  }
}


class fields_types_options_cfg
{
	public $cfg;

	function __construct($options)
	{
		$this->cfg = $options;
	}

	function has($key)
	{
		if(isset($this->cfg[$key]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get($key, $defautl = '')
	{
		if(isset($this->cfg[$key]))
		{
			return $this->cfg[$key];
		}
		else
		{
			return $defautl;
		}
	}
}