<?php

//to include menus from plugins
  $app_plugin_menu = array();
  
//include available plugins  
  if(defined('AVAILABLE_PLUGINS'))
  {  	  	
    foreach(explode(',',AVAILABLE_PLUGINS) as $plugin)
    {            
      //include language file                  
      if(is_file($v = 'plugins/' . $plugin .'/languages/' . (isset($app_user['language']) ? $app_user['language'] : CFG_APP_LANGUAGE)))
      {          
        require($v);
      }
                   
      //include plugin
      if(is_file('plugins/' . $plugin .'/application_top.php'))
      {
        require('plugins/' . $plugin .'/application_top.php');        
      }      
    }
  }
  	