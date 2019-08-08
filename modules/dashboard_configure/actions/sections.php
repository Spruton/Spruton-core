<?php 

$app_title = app_set_title(TEXT_SECTIONS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(    
    	'name'	=> $_POST['name'],    	
    	'grid'	=> $_POST['grid'],
    	'sort_order' => $_POST['sort_order'],
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_dashboard_pages_sections',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
    }
    else
    {                     
      db_perform('app_dashboard_pages_sections',$sql_data);                             
    }
        
    redirect_to('dashboard_configure/sections');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_dashboard_pages_sections where id='" . _get::int('id') . "'");
        db_query("update app_dashboard_pages set sections_id=0 where sections_id='" . _get::int('id') . "'");
                     
        redirect_to('dashboard_configure/sections');  
      }
    break; 

}