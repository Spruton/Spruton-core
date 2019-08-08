<?php

	chdir(substr(__DIR__,0,-5));

	define('IS_CRON',true);
	
//load core
	require('includes/application_core.php');
	
	//load app lng
	if(is_file($v = 'includes/languages/' . CFG_APP_LANGUAGE))
	{
		require($v);
	}
	
	$backup = new backup();
	
	$backup->create();