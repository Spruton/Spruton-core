<?php
  
  $msg = '';   
   
  if($current_entity_id==1 and $_GET['id']==$app_logged_users_id)
  {
    $msg = TEXT_ERROR_USER_DELETE;
  } 
  
  if(!users::has_access('delete'))
  {
    $msg = TEXT_NO_ACCESS;
  }
  
 
  $item_info = db_find('app_entity_' . $_GET['entity_id'],$_GET['id']);    
  $heading_field_id = fields::get_heading_id($_GET['entity_id']);    
  $name = ($heading_field_id>0 ? items::get_heading_field_value($heading_field_id,$item_info) : $item_info['id']);
            
  $heading = TEXT_HEADING_DELETE; 
  $content =  sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,$name);
  $button_title = TEXT_BUTTON_DELETE;
  
  if(entities::has_subentities($current_entity_id))
  {
  	$show_delete_confirm = false;
  	$entities_query = db_query("select id from app_entities where parent_id='" . $current_entity_id . "'");
  	while($entities = db_fetch_array($entities_query))
  	{
  		$items_query = db_query("select id from app_entity_" . $entities['id'] . " limit 1");
  		if($items = db_fetch_array($items_query))
  		{
  			$show_delete_confirm = true;
  			break;
  		}
  	
  	}
  	
  	if($show_delete_confirm)
  	{
  		$content .= '<div style="margin-top: 15px;" class="alert alert-warning">' . sprintf(TEXT_WARNING_ITEM_HAS_SUB_ITEM,$app_entities_cache[$current_entity_id]['name']). '</div><div class="single-checkbox"><label>' . input_checkbox_tag('delete_confirm',1,['class'=>'required']) . ' ' . TEXT_CONFIRM_DELETE . '</label></div>';
  	}
  }
  
  