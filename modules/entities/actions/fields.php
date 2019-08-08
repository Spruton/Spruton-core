<?php

switch($app_module_action)
{
  case 'set_heading_field_id':
       //reset heading
       db_query("update app_fields set is_heading=0 where entities_id ='" . db_input($_GET['entities_id']) . "'");
       
       //set new heading
       db_query("update app_fields set is_heading=1 where id='" . $_POST['heading_field_id'] . "' and entities_id ='" . db_input($_GET['entities_id']) . "'");
       
       exit(); 
    break;
   case 'set_heading_field_width':
    	entities::set_cfg('heading_width_based_content',$_POST['heading_width_based_content'],$_GET['entities_id']);
    	exit();
    	break;
  case 'set_number_fixed_field_in_listing':
      entities::set_cfg('number_fixed_field_in_listing',$_POST['number_fields'],$_GET['entities_id']);
      exit(); 
    break;
  case 'set_change_col_width_in_listing':
    	entities::set_cfg('change_col_width_in_listing',$_POST['change_col_width_in_listing'],$_GET['entities_id']);
    	exit();
    	break;
  case 'sort_fields':
        if(isset($_POST['fields_in_listing'])) 
        {
          $sort_order = 0;
          foreach(explode(',',$_POST['fields_in_listing']) as $v)
          {
            $sql_data = array('listing_status'=>1,'listing_sort_order'=>$sort_order);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('form_fields_','',$v)) . "'");
            $sort_order++;
          }
        }
        
        if(isset($_POST['fields_excluded_from_listing'])) 
        {          
          foreach(explode(',',$_POST['fields_excluded_from_listing']) as $v)
          {
            $sql_data = array('listing_status'=>0,'listing_sort_order'=>0);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('form_fields_','',$v)) . "'");            
          }
        }
      exit();
    break;
  case 'save_internal':
	  	$sql_data = array(
	  			'name'=>$_POST['name'],	  			
	  			'short_name'=>$_POST['short_name'],	  			
	  			'is_heading'=>(isset($_POST['is_heading']) ? $_POST['is_heading']:0),
	  			'sort_order'=>$_POST['sort_order'],
	  			'configuration'=> (isset($_POST['fields_configuration']) ? fields_types::prepare_configuration($_POST['fields_configuration']):''),
	  			);
	  		  	
	  	//reset heading fields, only one field can be heading
	  	if(isset($_POST['is_heading']))
	  	{
	  		db_query("update app_fields set is_heading=0 where entities_id ='" . db_input($_POST['entities_id']) . "'");
	  	}
	  	
	  	if(isset($_GET['id']))
	  	{	  	
	  		db_perform('app_fields',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");	  
	  	}
	  	
	  	redirect_to('entities/fields','entities_id=' . $_POST['entities_id']);
  	break;
  case 'save':
  	
  	  $fields_configuration = $_POST['fields_configuration'];
  	  
  	  //prepare upload
  	  if(isset($_FILES['fields_configuration']))
  	  {
  	  	$upload_configuration = array();
  	  	
  	  	foreach($_FILES['fields_configuration']['name'] as $k=>$v)
  	  	{  	
  	  		$upload_folder = '';
  	  		
  	  		//prepare upload folder
  	  		if(strstr($k,'icon_'))
  	  		{
  	  		  $upload_folder = 'icons/';	  		
  	  		}
  	  		
  	  		//check if delete file
  	  		if(isset($_POST['delete_file'][$k]))
  	  		{
  	  			unlink(DIR_WS_UPLOADS  . $upload_folder . $_POST['delete_file'][$k]);
  	  			$upload_configuration[$k] = '';
  	  		}
  	  			
  	  		//upload file
  	  		if(strlen($v))
  	  		{
	  	  		$filename = str_replace(' ','_',$v);
	  	  		if(move_uploaded_file($_FILES['fields_configuration']['tmp_name'][$k], DIR_WS_UPLOADS  . $upload_folder . $filename))
	  	  		{
	  	  			$upload_configuration[$k] = $filename;
	  	  		}
  	  		}
  	  		
  	  	}
  	  	
  	  	if(count($upload_configuration))
  	  	{
  	  		$fields_configuration = array_merge($fields_configuration,$upload_configuration);  	  		
  	  	}
  	  }
  	    	  
      $sql_data = array('forms_tabs_id'=>$_POST['forms_tabs_id'],
                        'name'=>$_POST['name'],                        
                        'type'=>$_POST['type'],
                        'short_name'=>$_POST['short_name'],
                        'notes' => strip_tags($_POST['notes']),
                        'is_heading'=>(isset($_POST['is_heading']) ? $_POST['is_heading']:0),
                        'is_required'=>(isset($_POST['is_required']) ? $_POST['is_required']:0),
                        'required_message'=>$_POST['required_message'],
                        'tooltip'=>$_POST['tooltip'],
                        'tooltip_display_as'=>(isset($_POST['tooltip_display_as']) ? $_POST['tooltip_display_as']:''),
      									'tooltip_in_item_page'=>(isset($_POST['tooltip_in_item_page']) ? $_POST['tooltip_in_item_page']:''),
      									'tooltip_item_page'=>$_POST['tooltip_item_page'],
                        'configuration'=> (isset($_POST['fields_configuration']) ? fields_types::prepare_configuration($fields_configuration):''),        
                        'entities_id'=>$_POST['entities_id']);
                        
      
      //reset heading fields, only one field can be heading                  
      if(isset($_POST['is_heading']))
      {
        db_query("update app_fields set is_heading=0 where entities_id ='" . db_input($_POST['entities_id']) . "'");
      }                   
      
      if(isset($_GET['id']))
      {        
        //check if field type changed and do action required when field type changed
        fields::check_if_type_changed($_GET['id'],$_POST['type']);
        
        db_perform('app_fields',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
        
        $fields_id = $_GET['id'];
      }
      else
      {     
        $sql_data['sort_order'] = (fields::get_last_sort_number($_POST['forms_tabs_id'])+1);
                  
        db_perform('app_fields',$sql_data);
        $fields_id = db_insert_id();
        
        entities::prepare_field($_POST['entities_id'],$fields_id,$_POST['type']);                
      }
      
      //create app_related_items_#_# table
      related_records::prepare_entities_related_items_table($_POST['entities_id'], $fields_id);
      
      if(isset($_POST['redirect_to']))
      {
        switch($_POST['redirect_to'])
        {
          case 'forms':
              redirect_to('entities/forms','entities_id=' . $_POST['entities_id']);
            break;
        }
      }
      
      redirect_to('entities/fields','entities_id=' . $_POST['entities_id']);      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = fields::check_before_delete($_GET['id']);
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = fields::get_name_by_id($_GET['id']);
          
          db_delete_row('app_fields',$_GET['id']);
          
          db_delete_row('app_fields_choices',$_GET['id'],'fields_id');
          
          db_delete_row('app_reports_filters',$_GET['id'],'fields_id');
                              
          choices_values::delete_by_field_id($_GET['entities_id'],$_GET['id']);
                              
          entities::delete_field($_GET['entities_id'],$_GET['id']);
          
          db_query("delete from app_reports_filters_templates where fields_id='" . db_input($_GET['id']) ."'");
          
          db_query("delete from app_forms_fields_rules where fields_id='" . db_input($_GET['id']) ."'");
          
          //delete approved records
          db_query("delete from app_approved_items where entities_id='" . _get::int('entities_id') . "' and fields_id='" . _get::int('id') . "'");
          
          //access rules
          db_query("delete from app_access_rules where fields_id='" . db_input($_GET['id']) ."'");
          db_query("delete from app_access_rules_fields where fields_id='" . db_input($_GET['id']) ."'");
          
          mind_map::delete_by_fields_id($_GET['entities_id'], $_GET['id']);
          
          if(is_ext_installed())
          {
          	db_delete_row('app_ext_processes_actions_fields',$_GET['id'],'fields_id');
          }	
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        if(isset($_POST['redirect_to']))
        {
          switch($_POST['redirect_to'])
          {
            case 'forms':
                redirect_to('entities/forms','entities_id=' . $_GET['entities_id']);
              break;
          }
        }
        
        redirect_to('entities/fields','entities_id=' . $_GET['entities_id']);  
      }
    break;    
  case 'get_entities_form_tabs':
      $choices = forms_tabs::get_choices($_POST['entities_id']);
      
      if(count($choices)==1)
      {
        $html = input_hidden_tag('copy_to_form_tabs_id',key($choices));
      }
      else
      {
        $html = '
         <div class="form-group">
          	<label class="col-md-4 control-label" for="type">' . TEXT_SELECT_FORM_TAB . '</label>
            <div class="col-md-8">	
          	  ' . select_tag('copy_to_form_tabs_id',$choices,'',array('class'=>'form-control')) . '        
            </div>			
          </div>        
        ';
      }
      
      echo $html;
      
      exit();
    break; 
  case 'mulitple_edit':
  	if(strlen($_POST['selected_fields']))
  	{
  		$fields_query = db_query("select * from app_fields where entities_id='" . $_GET['entities_id'] . "' and id in (" . $_POST['selected_fields'] . ")");
  		while($fields = db_fetch_array($fields_query))
  		{
  			if($_POST['is_required']=='yes')
  			{
  				db_query("update app_fields set is_required=1 where id='" . $fields['id'] . "'");
  			}
  			elseif($_POST['is_required']=='no')
  			{
  				db_query("update app_fields set is_required=0 where id='" . $fields['id'] . "'");
  			}
  		}
  	}
  	
  	redirect_to('entities/fields','entities_id=' . $_GET['entities_id']);
  	break;
  case 'copy_selected':  
    if(strlen($_POST['selected_fields'])>0 and $_POST['copy_to_entities_id']>0)
    {
            
      $fields_query = db_query("select * from app_fields where entities_id='" . $_GET['entities_id'] . "' and id in (" . $_POST['selected_fields'] . ")");
      while($fields = db_fetch_array($fields_query))
      {
        //prepare sql data
        $sql_data = $fields;
        unset($sql_data['id']);
        $sql_data['entities_id'] = $_POST['copy_to_entities_id'];
        $sql_data['forms_tabs_id'] = $_POST['copy_to_form_tabs_id'];        
        $sql_data['is_heading'] = 0;
         
        db_perform('app_fields',$sql_data);        
        $new_fields_id = db_insert_id();
        
        entities::prepare_field($_POST['copy_to_entities_id'],$new_fields_id, $fields['type']);
        
        //create app_related_items_#_# table
        related_records::prepare_entities_related_items_table($_POST['copy_to_entities_id'], $new_fields_id);
                      
        $choices_parent_id_to_replace = array();
        
        //check fields choices
        $fields_choices_query = db_query("select * from app_fields_choices where fields_id='" . $fields['id'] . "'");
        while($fields_choices = db_fetch_array($fields_choices_query))
        {
          //prepare sql data
          $sql_data = $fields_choices;
          unset($sql_data['id']);
          $sql_data['fields_id'] = $new_fields_id;
          
          db_perform('app_fields_choices',$sql_data);
          $new_fields_choices_id = db_insert_id();
          
          $choices_parent_id_to_replace[$fields_choices['id']] = $new_fields_choices_id;
        }                
        
        foreach($choices_parent_id_to_replace as $from_id=>$to_id)
        {
          db_query("update app_fields_choices set parent_id='" . $to_id . "' where parent_id='" . $from_id . "' and fields_id='" . $new_fields_id . "'");
        }
      }
      
      $alerts->add(TEXT_FIELDS_COPY_SUCCESS,'success');      
    } 
    
    redirect_to('entities/fields','entities_id=' . $_POST['copy_to_entities_id']);
    
    break;
    
  case 'import':
  	
  	//rename file (issue with HTML.php:495 if file have UTF symbols)
  	$filepath  = DIR_WS_UPLOADS . 'import_fields.xml';
  	
  	if(move_uploaded_file($_FILES['filename']['tmp_name'], $filepath))
  	{
 			
  		$data = file_get_contents($filepath);
  		$xml = simplexml_load_string($data);
  		$json = json_encode($xml);
  		$fields = json_decode($json,TRUE);
  	
  		unlink($filepath);
  		
  		//print_rr($fields);
  		//exit();
  		
  		$entities_id = _get::int('entities_id');
  		$imported_fields = 0;
  		
  		$tab_query = db_query("select forms_tabs_id from app_fields where entities_id='" . db_input($entities_id). "' and type='fieldtype_id'");
  		$tab = db_fetch_array($tab_query);
  		$default_forms_tabs_id = $tab['forms_tabs_id'];
  		
  		if(isset($fields['Field']))
  		{  			  
  			$fields_list = [];
  			if(isset($fields['Field']['forms_tabs_id']))
  			{ 
  				$fields_list[] = $fields['Field'];
  			}
  			else
  			{
  				$fields_list = $fields['Field'];
  			}
  			  			  			
  			foreach($fields_list as $field)
  			{
  				//print_rr($field);
  				
  				$sql_data = ['entities_id'=>$entities_id];
  				
  				foreach($field as $k=>$v)
  				{
  					if(!is_array($v))
  					{
  						$sql_data[$k] = $v;
  					}
  				}
  				
  				//check if tab id exist for this entity
  				$tab_query = db_query("select id from app_forms_tabs where entities_id='" . db_input($entities_id). "'  and id='" . $sql_data['forms_tabs_id'] . "'");
  				if(!$tab = db_fetch_array($tab_query))
  				{
  					$sql_data['forms_tabs_id'] = $default_forms_tabs_id;
  				}
  				
  				//print_rr($sql_data);
  				//exit();  				  			
  				
  				db_perform('app_fields',$sql_data);
  				$fields_id = db_insert_id();
  				
  				$imported_fields++;
  				
  				entities::prepare_field($sql_data['entities_id'],$fields_id,$sql_data['type']);
  				  				
  				//create app_related_items_#_# table
  				related_records::prepare_entities_related_items_table($sql_data['entities_id'], $fields_id);
  				
  				//check choices
  				if(isset($field['Choices']))
  				{
  					$choices_list = [];
  					if(isset($field['Choices']['Choice']['name']))
  					{
  						$choices_list[] = $field['Choices']['Choice'];
  					}
  					else
  					{
  						$choices_list = $field['Choices']['Choice'];
  					}
  					
  					foreach($choices_list as $choice)
  					{
  						$sql_data = ['fields_id'=>$fields_id];
  						
  						foreach($choice as $k=>$v)
  						{
  							if(!is_array($v))
  							{
  								$sql_data[$k] = $v;
  							}
  						}
  						
  						db_perform('app_fields_choices',$sql_data);
  					}
  					
  				}
  			}  			  			
  		}
  		

  		$alerts->add(sprintf(TEXT_IMPORTED_FIELDS,$imported_fields),'success');
  		redirect_to('entities/fields','entities_id=' . $_GET['entities_id']);
  		
  	}
  	else
  	{
  		$alerts->add(TEXT_FILE_NOT_LOADED,'warning');
  		redirect_to('entities/fields','entities_id=' . $_GET['entities_id']);
  	}
  	break;
        
  case 'export':
  	if(strlen($_POST['selected_fields']))
  	{
  		$writer = new XMLWriter();  		
  		$writer->openMemory();
  		$writer->setIndent(true);
  		$writer->startDocument('1.0', 'UTF-8');
  		
  		$writer->startElement('Fields');
  		$fields_query = db_query("select * from app_fields where entities_id='" . $_GET['entities_id'] . "' and id in (" . $_POST['selected_fields'] . ")");
  		while($fields = db_fetch_array($fields_query))
  		{
  			//export field data
  			$writer->startElement('Field');
  			
  			foreach($fields as $k=>$v)
  			{
  				if(in_array($k,['id','entities_id','is_heading'])) continue;
  				
  				$writer->writeElement($k, $v);  				
  			}
  			
  			//export field choices data
  			$choices_query = db_query("select * from app_fields_choices where fields_id='" . $fields['id'] . "'");
  			
  			if(db_num_rows($choices_query))
  			{
  				$writer->startElement('Choices');
  				
  				while($choices = db_fetch_array($choices_query))
  				{
  					$writer->startElement('Choice');
  					
  					foreach($choices as $k=>$v)
  					{
  						if(in_array($k,['id','fields_id','filename','parent_id'])) continue;
  					
  						$writer->writeElement($k, $v);
  					}
  					
  					$writer->endElement();
  				}
  				
  				$writer->endElement();
  			}
  			  			
  			$writer->endElement();
  		}
  		
  		$writer->endElement();
  		  		  		  		
  		$filename = str_replace(array(" ",","),"_",trim($_POST['filename']));
  		
  		header('Content-Type: application/xml; charset=utf-8');
  		header('Content-Disposition: attachment;filename="' . $filename . '.xml"');
  		header('Cache-Control: max-age=0');
  		  		
  		echo $writer->outputMemory();
  		  		  	
  		exit();
  	}
  	
  	redirect_to('entities/fields','entities_id=' . $_GET['entities_id']);
  	break;
}


require(component_path('entities/check_entities_id'));  