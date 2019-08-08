<?php


switch($app_module_action)
{
	case 'set_listing_col_width':
		$reports_id = _get::int('reports_id');
		db_query("update app_reports set listing_col_width='" . $_POST['listing_col_width']. "' where id={$reports_id}");
		exit();
		break;
}


//check access to reports
if(!users::has_reports_access())
{
	redirect_to('dashboard/access_forbidden');
}

$app_title = app_set_title(TEXT_HEADING_REPORTS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array('name'=>db_prepare_input($_POST['name']),
                      'entities_id'=>$_POST['entities_id'],
                      'reports_type'=>'standard',
                      'menu_icon'=>$_POST['menu_icon'],                                              
                      'in_menu'=>(isset($_POST['in_menu']) ? $_POST['in_menu']:0),
                      'in_dashboard'=>(isset($_POST['in_dashboard']) ? $_POST['in_dashboard']:0),
                      'in_dashboard_counter'=>(isset($_POST['in_dashboard_counter']) ? $_POST['in_dashboard_counter']:0),
                      'in_dashboard_icon'=>(isset($_POST['in_dashboard_icon']) ? $_POST['in_dashboard_icon']:0),
                      'in_dashboard_counter_color'=>$_POST['in_dashboard_counter_color'],
                      'in_dashboard_counter_fields'=>(isset($_POST['in_dashboard_counter_fields']) ? implode(',',$_POST['in_dashboard_counter_fields']):''),
                      'dashboard_counter_sum_by_field'=>$_POST['dashboard_counter_sum_by_field'],
                      'dashboard_counter_hide_count'=>(isset($_POST['dashboard_counter_hide_count']) ? 1:0),                                                                  
                      'in_header'=>(isset($_POST['in_header']) ? $_POST['in_header']:0),
                      'in_header_autoupdate'=>(isset($_POST['in_header_autoupdate']) ? $_POST['in_header_autoupdate']:0),                      
                      'created_by'=>$app_logged_users_id,
                      'notification_days'=>(isset($_POST['notification_days']) ? implode(',',$_POST['notification_days']):''),
                      'notification_time'=>(isset($_POST['notification_time']) ? implode(',',$_POST['notification_time']):''),
                      'listing_type' => (isset($_POST['listing_type']) ? $_POST['listing_type']:''),
                      );
        
    if(isset($_GET['id']))
    {        
      
      $report_info = db_find('app_reports',$_GET['id']);
      
      //check reprot entity and if it's changed remove report filters and parent reports
      if($report_info['entities_id']!=$_POST['entities_id'])
      {
        db_query("delete from app_reports_filters where reports_id='" . db_input($_GET['id']) . "'");
        
        //delete paretn reports
        reports::delete_parent_reports($_GET['id']);
        $sql_data['parent_id']=0;
      }
      
      db_perform('app_reports',$sql_data,'update',"id='" . db_input($_GET['id']) . "' and created_by='" . $app_logged_users_id . "'");       
    }
    else
    {                     
      db_perform('app_reports',$sql_data);   
      
      $insert_id = db_insert_id();
      
      reports::auto_create_parent_reports($insert_id);               
    }
        
    redirect_to('reports/');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        $report_info_query = db_query("select * from app_reports where id='" . db_input($_GET['id']). "' and created_by='" . db_input($app_logged_users_id) . "'");
        if($report_info = db_fetch_array($report_info_query))
        {          
          reports::delete_reports_by_id($report_info['id']);
                           
          $alerts->add(TEXT_WARN_DELETE_REPORT_SUCCESS,'success');
        }
        else
        {
        
        }
                     
        redirect_to('reports/');  
      }
    break; 
  case 'get_numeric_fields':
  	
  	$fields_access_schema = users::get_fields_access_schema(_post::int('entities_id'),$app_user['group_id']);
  	  	  	
  	$choices = array();
  	$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_formula','fieldtype_js_formula','fieldtype_mysql_query') and f.entities_id='" . _post::int('entities_id') . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
  	while($fields = db_fetch_array($fields_query))
  	{
  		if(isset($fields_access_schema[$fields['id']]))
  		{
  			if($fields_access_schema[$fields['id']] == 'hide') continue;
  		}
  		
  		$choices[$fields['id']] = $fields['name'];
  	}
  	
  	$html = '';
  	
  	if(count($choices))
  	{
  	 
  	$obj = db_find('app_reports',_get::int('id'));
  	 
  	$html = '
  				
  			<div class="form-group">
			  	<label class="col-md-4 control-label" for="dashboard_counter_sum_by_field">' .  tooltip_icon(TEXT_COUNTER_SUM_BY_FIELD_INFO) . TEXT_SUM_BY_FIELD  . '</label>
			    <div class="col-md-8">' .  select_tag('dashboard_counter_sum_by_field',[''=>'']+$choices,$obj['dashboard_counter_sum_by_field'],array('class'=>'form-control input-large'))  . '
			    </div>
			  </div>
			    		
  			<div class="form-group">
			  	<label class="col-md-4 control-label" for="in_dashboard_counter_fields">' .  tooltip_icon(TEXT_DASHBOARD_REPORT_EXTRA_FIELDS_INFO) . TEXT_EXTRA_FIELDS  . '</label>
			    <div class="col-md-8">' .  select_tag('in_dashboard_counter_fields[]',$choices,$obj['in_dashboard_counter_fields'],array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'))  . '
			    </div>
			  </div>
			    		
			  <div class="form-group">
			  	<label class="col-md-4 control-label" for="dashboard_counter_hide_count">' .  TEXT_HIDE_COUNT_OF_RECORDS  . '</label>
			    <div class="col-md-8"><p class="form-control-static">' .  input_checkbox_tag('dashboard_counter_hide_count',1,array('checked'=>$obj['dashboard_counter_hide_count']))  . '</p>
			    </div>
			  </div>  				  			  
  		';
  	}
  	
  	echo $html;
  	exit();
  	break;
  case 'get_listing_fields':
  	$fields_access_schema = users::get_fields_access_schema(_post::int('entities_id'),$app_user['group_id']);
  	 
  	
  	$obj = db_find('app_reports',_get::int('id'));
  	
  	$order_by = (strlen($obj['fields_in_listing']) ? 'field(f.id,' . $obj['fields_in_listing'] . '),':'');
  	
  	$choices = array();
  	$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_section','fieldtype_mapbbcode','fieldtype_mind_map') and f.entities_id='" . _post::int('entities_id') . "' and f.forms_tabs_id=t.id order by {$order_by} t.sort_order, t.name, f.sort_order, f.name",false);
  	while($fields = db_fetch_array($fields_query))
  	{
  		if(isset($fields_access_schema[$fields['id']]))
  		{
  			if($fields_access_schema[$fields['id']] == 'hide') continue;
  		}
  	
  		$choices[$fields['id']] = fields_types::get_option($fields['type'],'name',$fields['name']) . ' (#' . $fields['id'] . ')';
  	}
  	 
  	$html = '';
  	 
  	if(count($choices))
  	{
  	  		  	
  		$html = '
  	
  			<div class="form-group">
			  	<label class="col-md-3 control-label" for="dashboard_counter_sum_by_field">' .   TEXT_FIELDS_IN_LISTING  . '</label>
			    <div class="col-md-9">' .  select_tag('fields_in_listing[]',$choices,$obj['fields_in_listing'],array('class'=>'form-control chosen-select chosen-sortable','multiple'=>'multiple'))  . '
			    </div>
			  </div>
			    			
  		';
  	}
  	
  	echo $html;
  	exit();
  	
  	break;
  	
  case 'get_listing_types':
  	if(count($choices = listing_types::get_choices(_post::int('entities_id')))>1)
  	{
  		$obj = db_find('app_reports',_get::int('id'));
  		
  	  echo '<div class="form-group">
  	  	<label class="col-md-4 control-label" for="name">' . TEXT_TYPE . '</label>
  	    <div class="col-md-8">	
  	  	  ' . select_tag('listing_type',$choices,$obj['listing_type'],array('class'=>'form-control input-medium')) . '
  	    </div>			
  	  </div>';
  	} 
  	  
  	exit;
  	break;  	
}