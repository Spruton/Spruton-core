<?php

class fieldtype_users_approve
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_USERS_APPROVE_TITLE);
  }
  
  function get_configuration($params = array())
  {
  	$entity_info = db_find('app_entities',$params['entities_id']);
  	
    $cfg = array();
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DISPLAY_USERS_AS, 
                   'name'=>'display_as',                   
                   'type'=>'dropdown',
                   'params'=>array('class'=>'form-control input-xlarge'),
                   'choices'=>array('dropdown'=>TEXT_DISPLAY_USERS_AS_DROPDOWN,'checkboxes'=>TEXT_DISPLAY_USERS_AS_CHECKBOXES,'dropdown_muliple'=>TEXT_DISPLAY_USERS_AS_DROPDOWN_MULTIPLE));
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DEFAULT, 'name'=>'users_by_default','type'=>'dropdown', 'choices'=>users::get_choices(), 'params'=>array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'));
            
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DISABLE_NOTIFICATIONS, 'name'=>'disable_notification','type'=>'checkbox','tooltip_icon'=>TEXT_DISABLE_NOTIFICATIONS_FIELDS_INFO);
    
    if($entity_info['parent_id']>0)
    {
    	$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DISABLE_USERS_DEPENDENCY, 'name'=>'disable_dependency','type'=>'checkbox','tooltip_icon'=>TEXT_DISABLE_USERS_DEPENDENCY_INFO);
    }       
    
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_ADMIN, 'name'=>'hide_admin','type'=>'checkbox');
    $cfg[TEXT_SETTINGS][] = array('title'=>TEXT_HIDE_FIELD_NAME, 'name'=>'hide_field_name','type'=>'checkbox','tooltip_icon'=>TEXT_HIDE_FIELD_NAME_TIP);
    
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_BUTTON_TITLE, 'name'=>'button_title','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_DEFAULT . ': ' . TEXT_APPROVE);
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_ICON, 'name'=>'button_icon','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip'=>TEXT_MENU_ICON_TITLE_TOOLTIP);
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_COLOR, 'name'=>'button_color','type'=>'colorpicker');
           
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_CONFIRMATION_WINDOW, 'name'=>'confirmation_window','type'=>'dropdown','choices'=>['0'=>TEXT_NO,'1'=>TEXT_YES],'params'=>array('class'=>'form-control input-small'));
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_CONFIRMATION_TEXT, 'name'=>'confirmation_text','type'=>'textarea','params'=>array('class'=>'form-control textarea-small'),'tooltip_icon'=>TEXT_DEFAULT . ': ' . TEXT_ARE_YOU_SURE);
    
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_ADD_COMMENT, 'name'=>'add_comment','type'=>'dropdown','choices'=>['0'=>TEXT_NO,'1'=>TEXT_YES],'params'=>array('class'=>'form-control input-small'));
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_COMMENT_TEXT, 'name'=>'comment_text','type'=>'textarea','params'=>array('class'=>'form-control textarea-small'),'tooltip_icon'=>TEXT_DEFAULT . ': ' . TEXT_APPROVED);
    
    $cfg[TEXT_SIGNATURE][] = array('title'=>TEXT_SIGNATURE, 'name'=>'use_signature','type'=>'dropdown','choices'=>['0'=>TEXT_NO,'1'=>TEXT_YES],'params'=>array('class'=>'form-control input-small'));
    $cfg[TEXT_SIGNATURE][] = array('title'=>TEXT_DESCRIPTION, 'name'=>'signature_description','type'=>'textarea','params'=>array('class'=>'form-control textarea-small'));
    $cfg[TEXT_SIGNATURE][] = array('title'=>TEXT_WIDTH_IN_ITEM_PAGE, 'name'=>'signature_width_item_page','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_WIDTH_IN_ITEM_PAGE_INFO);
    $cfg[TEXT_SIGNATURE][] = array('title'=>TEXT_WIDTH_IN_PRINT_PAGE, 'name'=>'signature_width_print_page','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_WIDTH_IN_PRINT_PAGE_INFO);
    
    $choices = [];
    $choices[0] = '';
    
    if(is_ext_installed())
    {
    	$processes_query = db_query("select id, name from app_ext_processes where entities_id='" . $params['entities_id'] . "' order by sort_order, name");
    	while($processes = db_fetch_array($processes_query))
    	{
    		$choices[$processes['id']] = $processes['name'];
    	}
    }
    
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_ALL_USERS_APPROVED, 'name'=>'run_process','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-large'),'tooltip'=>TEXT_ALL_USERS_APPROVED_INFO);
            
    return $cfg;
  }  
  
  static function get_choices($field, $params, $value='')
  {
  	global $app_users_cache, $app_user;
  	
  	$cfg = new fields_types_cfg($field['configuration']);
  	
  	$entities_id = $field['entities_id'];
  	
  	//get access schema
  	$access_schema = users::get_entities_access_schema_by_groups($entities_id);
  	
  	//check if parent item has users fields and if users are assigned
  	$has_parent_users = false;
  	$parent_users_list = array();
  	
  	if(isset($params['parent_entity_item_id']) and $cfg->get('disable_dependency')!=1)
  	{
  		if($params['parent_entity_item_id']>0)
  		{
  			$entity_info = db_find('app_entities',$entities_id);
  			$parent_entity_id = $entity_info['parent_id'];
  			
  			$path_array = items::get_path_array($parent_entity_id, $params['parent_entity_item_id']);
  			
  			//print_rr($path_array);
  			
  			foreach($path_array as $path_info)
  			{
  				$parent_users_fields = array();
  				$parent_fields_query = db_query("select f.* from app_fields f where f.type in ('fieldtype_users') and  f.entities_id='" . db_input($path_info['entities_id']) . "'");
  				while($parent_field = db_fetch_array($parent_fields_query))
  				{
  					$has_parent_users = true;
  	
  					$parent_users_fields[] = $parent_field['id'];
  				}
  				
  				if($has_parent_users)
  				{	  	
	  				$parent_item_info = db_find('app_entity_' . $path_info['entities_id'],$path_info['items_id']);
	  	
	  				foreach($parent_users_fields as $id)
	  				{
	  					$parent_users_list = array_merge(explode(',',$parent_item_info['field_' . $id]),$parent_users_list);
	  				}
  				}
  	
  				//cancel check if has user field
  				if($has_parent_users) break;
  			}
  		}
  	}
  	  	  	
  	//get users choices
  	//select all active users or already assigned users
  	$where_sql = (strlen($value) ? "(u.field_5=1 or u.id in (" . $value ."))" : "u.field_5=1");
  	
  	//hide administrators
  	if($cfg->get('hide_admin')==1)
  	{
  		$where_sql .= " and u.field_6>0 ";
  	}
  	
  	$choices = array();
  	$order_by_sql = (CFG_APP_DISPLAY_USER_NAME_ORDER=='firstname_lastname' ? 'u.field_7, u.field_8' : 'u.field_8, u.field_7');
  	$users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 where {$where_sql} order by group_name, " . $order_by_sql);
  	while($users = db_fetch_array($users_query))
  	{
  		if(!isset($access_schema[$users['field_6']]))
  		{
  			$access_schema[$users['field_6']] = array();
  		}
  	
  		if($users['field_6']==0 or in_array('view',$access_schema[$users['field_6']]) or in_array('view_assigned',$access_schema[$users['field_6']]))
  		{
  			//check parent users and check already assigned
  			if($has_parent_users and !in_array($users['id'],$parent_users_list) and !in_array($users['id'],explode(',',$value))) continue;
  	
  			$group_name = (strlen($users['group_name'])>0 ? $users['group_name'] : TEXT_ADMINISTRATOR);
  			$choices[$group_name][$users['id']] = $app_users_cache[$users['id']]['name'];
  		}
  	}
  	
  	return $choices;  	
  }
  
  function render($field,$obj,$params = array())
  {
    global $app_users_cache, $app_user;
    
    $cfg = new fields_types_cfg($field['configuration']);
     
    $entities_id = $field['entities_id'];
    
    if($params['is_new_item']==1)
    {
    	$value = (is_array($cfg->get('users_by_default')) ? implode(',',$cfg->get('users_by_default')) : '');
    }
    else
    {
    	$value = (strlen($obj['field_' . $field['id']]) ? $obj['field_' . $field['id']] : '');
    }
    
    $choices = self::get_choices($field, $params,$value);    
         
    if($cfg->get('display_as')=='dropdown')
    {
    	//add empty value for comment form
    	$choices = ($params['form']=='comment' ? array(''=>'')+$choices:$choices);
    	
      $attributes = array('class'=>'form-control chosen-select input-large field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
      
      return select_tag('fields[' . $field['id'] . ']',array(''=>TEXT_NONE)+$choices,$value,$attributes);
    }
    elseif($cfg->get('display_as')=='checkboxes')
    {
      $attributes = array('class'=>'field_' . $field['id'] . ($field['is_required']==1 ? ' required':''));
      
      return '<div class="checkboxes_list ' . ($field['is_required']==1 ? ' required':'') . '">' . select_checkboxes_tag('fields[' . $field['id'] . ']',$choices,$value,$attributes) . '</div>';
    }
    elseif($cfg->get('display_as')=='dropdown_muliple')
    {      
      $attributes = array('class'=>'form-control input-xlarge chosen-select field_' . $field['id'] . ($field['is_required']==1 ? ' required':''),
                          'multiple'=>'multiple',
                          'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
      return select_tag('fields[' . $field['id'] . '][]',$choices,explode(',',$value),$attributes);
    }
    
  }
  
  function process($options)
  {
    global $app_send_to,$app_send_to_new_assigned;
    
    $cfg = new fields_types_cfg($options['field']['configuration']);
    
    if($cfg->get('disable_notification')!=1)
    {
	    if(is_array($options['value']))
	    {
	      $app_send_to = array_merge($options['value'],$app_send_to);
	    }
	    else
	    {
	      $app_send_to[] = $options['value'];
	    }
    }
    
    $value = (is_array($options['value']) ? implode(',',$options['value']) : $options['value']);
    
    //check if value changed
    if($cfg->get('disable_notification')!=1)
    {
	    if(!$options['is_new_item'])
	    {            
	      if($value!=$options['current_field_value'])
	      {
	        foreach(array_diff(explode(',',$value),explode(',',$options['current_field_value'])) as $v)
	        {
	          $app_send_to_new_assigned[] = $v;
	        }                      
	      }
	    } 
    }
    
    //reset approved user in no users assigned
    if(!$options['is_new_item'])
    {
    	if(!strlen($value))
    	{	    		     		
    		db_query("delete from app_approved_items where entities_id='" . $options['field']['entities_id'] . "' and items_id='" . $options['item']['id'] . "' and fields_id='" . $options['field']['id'] . "'");    		
    	}
    	else
    	{
    		db_query("delete from app_approved_items where entities_id='" . $options['field']['entities_id'] . "' and items_id='" . $options['item']['id'] . "' and fields_id='" . $options['field']['id'] . "' and users_id not in (" . $value . ")");
    	}
    }
            
    return $value;
  }
  
  function output($options)
  {
    global $app_users_cache, $app_user, $app_path, $app_module_path;
    
    if(!strlen($options['value'])) return '';
    
    $cfg = new fields_types_cfg($options['field']['configuration']);
                    
    if(isset($options['is_print']) and $cfg->get('use_signature')==1)
    {
    	$html = '';
    	 
    	$approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);
    	 
    	$current_user_approved = false;
    	 
    	$users_list = array();
    	foreach(explode(',',$options['value']) as $id)
    	{
    		if(isset($app_users_cache[$id]))
    		{    			     			    			 
    			$signagure_html = '';
    			 
    			if(isset($approved_users[$id]))
    			{    				    	
    				if(strlen($approved_users[$id]['signature']))
    				{
    					$signagure_html = '<img src="' . $approved_users[$id]['signature'] . '" width="' . (strlen($cfg->get('signature_width_print_page')) ? (int)$cfg->get('signature_width_print_page') : 150) . '">';
    				}
    			}
    			     			 
    			$html .= '
        			<tr>
        				
        				<td>' . $app_users_cache[$id]['name'] . '</td>
        				<td style="padding-left: 5px;">' . $signagure_html . '</td>
        			</tr>';
    	
    		}
    	}
    	
    	if(strlen($html))
    	{
    		$html = '
      			<table>
      				' . $html . '
      			</table>
      			';
    	}
    	
    	return $html;
    }	
    elseif(isset($options['is_export']) or isset($options['is_email']))
    {
      $users_list = array(); 
      foreach(explode(',',$options['value']) as $id)
      {
        if(isset($app_users_cache[$id]))
        {              
          $users_list[] = $app_users_cache[$id]['name'];
        }
      }
      
      if(isset($options['is_email']))
      {
      	return implode('<br>',$users_list);
      }
      else
      {
      	return implode(', ',$users_list);
      }
    }
    else
    {
    	$html = '';
    	
    	$approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);
    	
    	$current_user_approved = false;
    	
      $users_list = array(); 
      foreach(explode(',',$options['value']) as $id)
      {
        if(isset($app_users_cache[$id]))
        { 
        	
        	$icon = '<i class="fa fa-minus"></i>';
        	
        	$signagure_html = '';
        	
        	if(isset($approved_users[$id]))
        	{
        		$icon = '<i class="fa fa-check fa-success"></i>';
        		
        		if($id==$app_user['id']) $current_user_approved = true;
        		        		        		
        		if(strlen($approved_users[$id]['signature']))
        		{        			        			
        			$signagure_html = '<img src="' . $approved_users[$id]['signature'] . '" width="' . (strlen($cfg->get('signature_width_item_page')) ? (int)$cfg->get('signature_width_item_page') : 150) . '">';
        		}
        	}
        	
        	
        	$html .= '
        			<tr>
        				<td style="padding-right: 5px;">' . $icon . '</td>
        				<td><span class="user-name" ' . users::render_publi_profile($app_users_cache[$id],true). '>' . $app_users_cache[$id]['name'] . '</span></td>
        				<td style="padding-left: 5px;">' . $signagure_html . '</td>		
        			</tr>'; 
                            
        }
      }
      
      if(strlen($html))
      {
      	$html = '
      			<table>
      				' . $html . '
      			</table>
      			';
      	
      	if(in_array($app_user['id'],explode(',',$options['value'])) and !$current_user_approved and $this->check_button_filter($options))
      	{
      		$button_title = (strlen($cfg->get('button_icon')) ? app_render_icon($cfg->get('button_icon')) . ' ' : '') . (strlen($cfg->get('button_title')) ? $cfg->get('button_title') : TEXT_APPROVE);
      		
      		$btn_css = 'btn-color-' . $options['field']['id'];
      		
      		$path_info = items::get_path_info($options['field']['entities_id'], $options['item']['id'],$options['item']);
      		
      		
      		$redirect_to = '&redirect_to=items';
      		
      		if(isset($options['redirect_to']))
      		{
      			if(strlen($options['redirect_to'])>0)
      			{
      				$redirect_to = '&redirect_to=' . $options['redirect_to'];
      			}
      		}
      		elseif($app_module_path=='items/info')
      		{
      			$redirect_to = '&redirect_to=items_info';
      		}
      		
      		//print_rr($options);      		
      		
      		$redirect_to .= (isset($_POST['page']) ? '&gotopage[' . $options['reports_id'] . ']=' . $_POST['page'] :'');
      		
      		if($cfg->get('confirmation_window')==1 or $cfg->get('use_signature')==1)
      		{
      			$button_html = button_tag($button_title,url_for('items/approve','fields_id=' . $options['field']['id'] . '&path=' . $path_info['full_path'] . $redirect_to),true,['class'=>'btn btn-primary btn-sm ' . $btn_css]);
      		}
      		else
      		{
      			$button_html =button_tag($button_title,url_for('items/approve','action=approve&fields_id=' . $options['field']['id'] . '&path=' . $path_info['full_path'] . $redirect_to),false,['class'=>'btn btn-primary btn-sm prevent-double-click ' . $btn_css]);
      		}
      		
      		$html .= '<div style="padding-top: 5px;">' . $button_html . app_button_color_css($cfg->get('button_color'), $btn_css) . '</div>';
      	}
      }
      
      return $html;
    }
  }  
  
  function check_button_filter($options)
  {
  	global $sql_query_having;
  	
  	$field_id = $options['field']['id'];
  	$entities_id = $options['field']['entities_id'];
  	  	
  	$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($entities_id). "' and reports_type='fieldfilter" . $field_id . "'");
  	if($reports_info = db_fetch_array($reports_info_query))
  	{
  		$reports_fileds = [];
  		$filtes_query = db_query("select fields_id from app_reports_filters where reports_id='" . $reports_info['id'] . "'");
  		while($filtes = db_fetch_array($filtes_query))
  		{
  			$reports_fileds[] = $filtes['fields_id'];
  		}
  		  		  		
  		$listing_sql_query = "e.id='" . $options['item']['id'] . "'";  		  	
  		$listing_sql_query_having = '';
  		  		
  		$listing_sql_select = fieldtype_formula::prepare_query_select($reports_info['entities_id'],'',false,['fields_in_query'=>implode(',',$reports_fileds)]);
  			
  		$listing_sql_query = reports::add_filters_query($reports_info['id'],$listing_sql_query);
  		 
  		//prepare having query for formula fields
  		if(isset($sql_query_having[$reports_info['entities_id']]))
  		{
  			$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$reports_info['entities_id']]);
  		}
  		
  		$listing_sql = "select  e.* " . $listing_sql_select . " from app_entity_" . $reports_info['entities_id'] . " e where " . $listing_sql_query . $listing_sql_query_having;
  		$items_query = db_query($listing_sql, false);
  		if($item = db_fetch_array($items_query))
  		{
  			return true;
  		}
  		else
  		{
  			return false;
  		}
  		 
  	}
  	
  	return true;
  }
  
  function reports_query($options)
  {  	  	
  	global $app_user;
  	
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
           
  	if(strlen($filters['filters_values'])>0)
    {  
    	$filters['filters_values'] = str_replace('current_user_id',$app_user['id'],$filters['filters_values']);
    	
      $sql_query[] = "(select count(*) from app_entity_" . $options['entities_id'] . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($options['filters']['fields_id'])  . "' and cv.value in (" . $filters['filters_values'] . ")) " . ($filters['filters_condition']=='include' ? '>0': '=0');
    }
                
    return $sql_query;
  }  
}