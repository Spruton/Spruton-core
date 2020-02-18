<?php


$stages_field_value_id = _get::int('value_id');

//check if field exit
//only fieldtype_stages can be changed.
$stages_field_info_query = db_query("select id, name, configuration from app_fields where id=" . _get::int('field_id') . " and type='fieldtype_stages'");
if(!$stages_field_info = db_fetch_array($stages_field_info_query))
{
	redirect_to('items/info','path=' . $app_path);
}

//check if action is allowed
$cfg = new fields_types_cfg($stages_field_info['configuration']);
if(!strlen($cfg->get('click_action')))
{
	redirect_to('items/info','path=' . $app_path);
}

//check field access
$fields_access_schema = users::get_fields_access_schema($current_entity_id,$app_user['group_id']);
if(isset($fields_access_schema[$stages_field_info['id']]))
{
	redirect_to('items/info','path=' . $app_path);
}

switch($app_module_action)
{
	case 'update':
		
		$entity_cfg = new entities_cfg($current_entity_id);
		
		$item_id = $current_item_id;
		
		$item_info_query = db_query("select * from app_entity_" . $current_entity_id . " where id='" . db_input($item_id) . "'");
		$item_info = db_fetch_array($item_info_query);
						
		//update item
		db_query("update app_entity_{$current_entity_id} set field_{$stages_field_info['id']}='" . $stages_field_value_id . "', date_updated = " . time() . " where id={$item_id}");
		
		//autoupdate all field types
		fields_types::update_items_fields($current_entity_id, $item_id);
		
		if($cfg->get('notify_when_changed')==1)
		{
			$sql_data = [
					'entities_id'=>$current_entity_id,
					'items_id'=>$current_item_id,
					'date_added' => time(),
					'created_by' => $app_user['id'], 
			];
			
			if(isset($_POST['description']))
			{
				$description = db_prepare_html_input($_POST['description']);
				$sql_data['description'] = ($entity_cfg->get('use_editor_in_comments')==1 ? $description : nl2br($description));
			}
			
			if(isset($_POST['fields']['attachments']))
			{
				$attachments = (isset($_POST['fields']['attachments']) ? $_POST['fields']['attachments'] : '');
				$sql_data['attachments'] = fields_types::process(array('class'=>'fieldtype_attachments','value'=>$attachments));
			}
						 
			db_perform('app_comments',$sql_data);
			
			$comments_id = db_insert_id();
			
			$updated_fields[$stages_field_info['id']] = $stages_field_value_id;
			 
			//insert comment history
			db_perform('app_comments_history',array('comments_id'=>$comments_id,'fields_id'=>$stages_field_info['id'],'fields_value'=>$stages_field_value_id));
			
			//send notificaton
			app_send_new_comment_notification($comments_id,$current_item_id,$current_entity_id);
		}
		
		if(is_ext_installed())
		{			
			//check public form notification
			//using $item_info as item with previous values
			public_forms::send_client_notification($current_entity_id, $item_info);
			 
			//sending sms
			$modules = new modules('sms');
			$sms = new sms($current_entity_id, $item_id);
			$sms->send_to = items::get_send_to($current_entity_id, $item_id);
			$sms->send_edit_msg($item_info);
			 						
			//email rules
			$email_rules = new email_rules($current_entity_id, $item_id);
			$email_rules->send_edit_msg($item_info);
			
			if(isset($comments_id))
			{
				$email_rules->send_comments_msg($item_info);
				
				$log = new track_changes($current_entity_id, $item_id);
				$log->log_comment($comments_id,$updated_fields);
			}
			
			if(($process_id = $cfg->get('run_process_for_choice_' . $stages_field_value_id))>0)
			{
				redirect_to('items/processes','action=run&id=' . $process_id . '&path=' . $app_path . '&redirect_to=items_info');
			}
			
		}
		
		redirect_to('items/info','path=' . $app_path);
		break;
}