<?php

if(!users::has_access('import') or !strlen($app_path))
{
	redirect_to('dashboard/access_forbidden');
}

if (!app_session_is_registered('import_fields'))
{
	$import_fields = array();
	app_session_register('import_fields');
}

switch($app_module_action)
{
	case 'import':
		$worksheet = json_decode(stripslashes($_POST['worksheet']),true);
		$entities_id = $current_entity_id;
		$parent_item_id = $parent_entity_item_id;
		
		$entity_info = db_find('app_entities',$entities_id);
		
		if($entity_info['parent_id']>0)
		{
			$parent_item_query = db_query("select * from app_entity_" . $entity_info['parent_id'] . " where id='" . db_input($parent_item_id) . "'");
		
			if($parent_item = db_fetch_array($parent_item_query))
			{
				$path_info = items::get_path_info($entity_info['parent_id'],$parent_item['id']);
		
				$redirect_path = $path_info['full_path'] . '/' . $entities_id;
			}
		}
		else
		{
			$redirect_path =  $entities_id;
		}

		//check if any fields are binded
		if(count($import_fields)==0)
		{
			$alerts->add(TEXT_IMPORT_BIND_FIELDS_ERROR,'error');
			redirect_to('items/items','path=' . $redirect_path);
		}

		//check required fields for users entity
		if($entities_id==1)
		{
			if(!in_array(7,$import_fields) or !in_array(8,$import_fields) or !in_array(9,$import_fields))
			{
				$alerts->add(TEXT_IMPORT_BIND_USERS_FIELDS_ERROR,'error');
				redirect_to('items/items','path=' . $redirect_path);
			}
			 
			$hasher = new PasswordHash(11, false);
		}
		
		//multilevel import
		$multilevel_import = _get::int('multilevel_import');
		
		$import_entities_list = [];
		$import_entities_list[] = $current_entity_id;
		
		if($multilevel_import>0)
		{
			$import_entities_list = [];
			$import_entities_list[] = $multilevel_import;
			
			foreach(entities::get_parents($multilevel_import) as $entity_id)
			{
				$import_entities_list[] = $entity_id;
		
				if($entity_id == $current_entity_id) break;
			}		
			
			$import_entities_list = array_reverse($import_entities_list);
			
			//print_rr($import_entities_list);
			//exit();
			
			//check heading
			foreach($import_entities_list as $id)
			{
				$check = false;
				$heading_field_id = fields::get_heading_id($id);				
				foreach($import_fields as $c=>$v)
				{
					if($v==$heading_field_id)
					{
						$check = true;
					}
				}
				
				if(!$check)
				{
					$alerts->add(sprintf(TEXT_MULTI_LEVEL_IMPORT_HEADING_ERROR,entities::get_name_by_id($id)),'error');
					redirect_to('items/items','path=' . $app_path);
				}
			}
			
		}

		//check if import first row
		$first_row  = (isset($_POST['import_first_row']) ? 0:1);

		//use when import users
		$already_exist_username = array();

		$count_items_added = 0;
		$count_items_updated = 0;

		//create chocies cahce to reduce sql queries
		$choices_names_to_id = array();
		$global_choices_names_to_id = array();
		$choices_parents_to_id = array();
		$global_choices_parents_to_id = array();
		
		$unique_fields = fields::get_unique_fields_list($entities_id);

		//start import
		for ($row = $first_row; $row < count($worksheet); ++$row)
		{
			$import_entity_parent_item_id = $parent_item_id;
			
			foreach($import_entities_list as $import_entity_level=>$import_entity_id)
			{
				$entities_id = $import_entity_id;
				
				//start build item sql data
				$sql_data = Array();
	
				$choices_values = array();
				 
				$email_username = '';
				$import_username = '';
				
				$is_unique_item = true;
	
				for ($col = 0; $col <= count($worksheet[$row]); ++$col)
				{
					if(isset($import_fields[$col]) and strlen($worksheet[$row][$col])>0)
					{
						$field_id = $import_fields[$col];
						
						//skip field import if field ID not the uses Entity
						if(!isset($app_fields_cache[$entities_id][$field_id])) continue;
																		
						$filed_info_query = db_query("select * from app_fields where id='" . db_input($field_id). "'");
						if($filed_info = db_fetch_array($filed_info_query))
						{
							 
							$cfg = new fields_types_cfg($filed_info['configuration']);
							 
							switch($filed_info['type'])
							{
								case 'fieldtype_user_email':
									$value = trim($worksheet[$row][$col]);
									$email_username = substr($value,0,strpos($value,'@'));
									 
									$sql_data['field_' . $field_id] = $value;
									break;
								case 'fieldtype_user_username':
									$value = trim($worksheet[$row][$col]);
									$import_username = $value;
									 
									$sql_data['field_' . $field_id] = $value;
									break;									
								case 'fieldtype_entity':
								case 'fieldtype_entity_ajax':
								case 'fieldtype_entity_multilevel':
									$values_list = array();
									$value = trim($worksheet[$row][$col]);
	
									if($heading_id = fields::get_heading_id($cfg->get('entity_id')))
									{
										$heading_field_info = db_find('app_fields',$heading_id);
										if(in_array($heading_field_info['type'],['fieldtype_input','fieldtype_input_masked','fieldtype_text_pattern_static','fieldtype_input_url']))
										{
											$value_array = [];
											if($filed_info['type']=='fieldtype_entity_multilevel' or $cfg->get('display_as')=='dropdown')
											{
												$value_array[] = $value;
											}
											else
											{
												$value_array = explode(',',$value);
											}
											
											foreach($value_array as $value_name)
											{
												$item_query = db_query("select id from app_entity_" . $cfg->get('entity_id') . " where field_" . $heading_id . "='" . db_input($value_name). "'");
												if($item = db_fetch_array($item_query))
												{
													$values_list[] =  $item['id'];												
												}
												else
												{
													
													if(($parent_entities_id = $app_entities_cache[$cfg->get('entity_id')]['parent_id'])>0)
													{
														$check_query = db_query("select id from app_entity_" . $cfg->get('entity_id'));
														if($check = db_fetch_array($check_query))
														{
															$parent_entities_item_id = $check['id'];
														}
													}
													else
													{
														$parent_entities_item_id = 0;
													}
													
													$item_sql_data = array();
													$item_sql_data['field_' . $heading_id] = trim($value_name);
													$item_sql_data['date_added'] = time();
													$item_sql_data['created_by'] = $app_logged_users_id;
													$item_sql_data['parent_item_id'] = $parent_entities_item_id;
		
													db_perform('app_entity_' . $cfg->get('entity_id'),$item_sql_data);
		
													$item_id = db_insert_id();
														
													$values_list[] = $item_id;
												}												
											}
											
											//prepare choices values
											$choices_values[$field_id] = $values_list;
											
											$sql_data['field_' . $field_id] = implode(',',$values_list);
										}
									}
									break;
								case 'fieldtype_dropdown':
								case 'fieldtype_radioboxes':
									$value = trim($worksheet[$row][$col]);
	
									if($cfg->get('use_global_list')>0)
									{
										if(isset($global_choices_names_to_id[$cfg->get('use_global_list')][$value]))
										{
											$sql_data['field_' . $field_id] = $global_choices_names_to_id[$cfg->get('use_global_list')][$value];
										}
										else
										{
											$fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input($value) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
											if($fields_choices_info = db_fetch_array($fields_choices_info_query))
											{
												$sql_data['field_' . $field_id] = $fields_choices_info['id'];
												 
												$global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $fields_choices_info['id'];
											}
											else
											{
												$field_sql_data = array('lists_id'=>$cfg->get('use_global_list'),
														'parent_id'=>0,
														'name'=>$value);
												db_perform('app_global_lists_choices',$field_sql_data);
	
												$item_id = db_insert_id();
	
												$sql_data['field_' . $field_id] = $item_id;
												 
												$global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $item_id;
											}
										}
									}
									else
									{
										if(isset($choices_names_to_id[$field_id][$value]))
										{
											$sql_data['field_' . $field_id] = $choices_names_to_id[$field_id][$value];
										}
										else
										{
											$fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input($value) . "' and fields_id='" . db_input($field_id) . "'");
											if($fields_choices_info = db_fetch_array($fields_choices_info_query))
											{
												$sql_data['field_' . $field_id] = $fields_choices_info['id'];
												 
												$choices_names_to_id[$field_id][$value] = $fields_choices_info['id'];
											}
											else
											{
												$field_sql_data = array('fields_id'=>$field_id,
														'parent_id'=>0,
														'name'=>$value);
												db_perform('app_fields_choices',$field_sql_data);
	
												$item_id = db_insert_id();
	
												$sql_data['field_' . $field_id] = $item_id;
												 
												$choices_names_to_id[$field_id][$value] = $item_id;
											}
										}
									}
	
									//prepare choices values
									$choices_values[$field_id][] = $sql_data['field_' . $field_id];
	
									break;
								case 'fieldtype_dropdown_multilevel':
									$values_list = array();
									$value = trim($worksheet[$row][$col]);
									 
									if(strlen($value))
									{
										$value_id = 0;
			        
										if($cfg->get('use_global_list')>0)
										{
											if(isset($global_choices_names_to_id[$cfg->get('use_global_list')][$value]))
											{
												$value_id = $global_choices_names_to_id[$cfg->get('use_global_list')][$value];
											}
											else
											{
												$fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input(trim($value)) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
												if($fields_choices_info = db_fetch_array($fields_choices_info_query))
												{
													$value_id = $fields_choices_info['id'];
													$global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $value_id;
												}
											}
										}
										else
										{
											if(isset($choices_names_to_id[$field_id][$value]))
											{
												$value_id = $choices_names_to_id[$field_id][$value];
											}
											else
											{
												$fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input(trim($value)) . "' and fields_id='" . db_input($field_id) . "'");
												if($fields_choices_info = db_fetch_array($fields_choices_info_query))
												{
													$value_id = $fields_choices_info['id'];
													$choices_names_to_id[$field_id][$value] = $value_id;
												}
											}
										}
			        
										if($value_id>0)
										{
											if($cfg->get('use_global_list'))
											{
												if(isset($global_choices_parents_to_id[$value_id]))
												{
													$value_array = $global_choices_parents_to_id[$value_id];
												}
												else
												{
													$value_array =  global_lists::get_paretn_ids($value_id);
	
													$global_choices_parents_to_id[$value_id] = $value_array;
												}
											}
											else
											{
												if(isset($choices_parents_to_id[$field_id][$value_id]))
												{
													$value_array = $choices_parents_to_id[$field_id][$value_id];
												}
												else
												{
													$value_array = fields_choices::get_paretn_ids($value_id);
	
													$choices_parents_to_id[$field_id][$value_id] = $value_array;
												}
											}
	
											$values_list = array_reverse($value_array);
	
											//prepare choices values
											$choices_values[$field_id] = $values_list;
	
											$sql_data['field_' . $field_id] = implode(',',$values_list);
										}
									}
	
									break;
								case 'fieldtype_grouped_users':	
								case 'fieldtype_dropdown_multiple':
								case 'fieldtype_checkboxes':
								case 'fieldtype_tags':
									$values_list = array();
									$value = trim($worksheet[$row][$col]);
	
									if($cfg->get('use_global_list')>0)
									{
										foreach(explode(',',$value) as $value_name)
										{
											$fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input(trim($value_name)) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
											if($fields_choices_info = db_fetch_array($fields_choices_info_query))
											{
												$values_list[] = $fields_choices_info['id'];
											}
											else
											{
												$field_sql_data = array('lists_id'=>$cfg->get('use_global_list'),
														'parent_id'=>0,
														'name'=>trim($value_name));
												db_perform('app_global_lists_choices',$field_sql_data);
	
												$item_id = db_insert_id();
	
												$values_list[] = $item_id;
											}
										}
									}
									else
									{
										foreach(explode(',',$value) as $value_name)
										{
											$fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input(trim($value_name)) . "' and fields_id='" . db_input($field_id) . "'");
											if($fields_choices_info = db_fetch_array($fields_choices_info_query))
											{
												$values_list[] = $fields_choices_info['id'];
											}
											else
											{
												$field_sql_data = array('fields_id'=>$field_id,
														'parent_id'=>0,
														'name'=>trim($value_name));
												db_perform('app_fields_choices',$field_sql_data);
	
												$item_id = db_insert_id();
	
												$values_list[] = $item_id;
											}
										}
									}
	
									//prepare choices values
									$choices_values[$field_id] = $values_list;
	
									$sql_data['field_' . $field_id] = implode(',',$values_list);
	
									break;
								case 'fieldtype_input_date':
								case 'fieldtype_input_datetime':
									$sql_data['field_' . $field_id] = strtotime($worksheet[$row][$col]);
									break;
								default:
									$sql_data['field_' . $field_id] = $worksheet[$row][$col];
									break;
							}
							
							//check uniques
							if(in_array($filed_info['id'],$unique_fields))
							{
								$check_query = db_query("select id from app_entity_{$entities_id} where field_{$field_id}='" . db_input($sql_data['field_' . $field_id]) . "' limit 1");
								if($check = db_fetch_array($check_query))
								{
									$is_unique_item = false;
								}
							}
							
						}
					}
				}
										
				//if import users then set required fields for users entity
				if($entities_id==1 and $_POST['import_action']=='import')
				{
					$sql_data['field_6'] = $_POST['users_group_id'];
					$sql_data['field_5'] = 1;
					$sql_data['field_13'] = CFG_APP_LANGUAGE;
					$sql_data['field_14'] = 'default';
	
					if(strlen($import_username)==0)
					{
						$sql_data['field_12'] = $email_username;
					}
	
					if(isset($_POST['set_pwd_as_username']))
					{
						$password = (strlen($import_username)>0 ? $import_username:$email_username);
					}
					else
					{
						$password = users::get_random_password();
					}
	
					$sql_data['password'] = $hasher->HashPassword($password);
	
					$check_query = db_query("select count(*) as total from app_entity_1 where field_12='" . db_input($sql_data['field_12']) . "'");
					$check = db_fetch_array($check_query);
					if($check['total']>0)
					{
						$already_exist_username[] = $sql_data['field_12'];
						continue;
					}
	
				}
				elseif($entities_id==1 and isset($_POST['set_pwd_as_username']))
				{
					$password = (strlen($import_username)>0 ? $import_username:$email_username);
					$sql_data['password'] = $hasher->HashPassword($password);
				}
				
				
				//prepare multilevel import
				if($multilevel_import>0)
				{
					if($_POST['import_action']=='import')
					{
						$_POST['import_action'] = 'update_import';
					}
					
					$heading_field_id = fields::get_heading_id($entities_id);
					$_POST['update_by_field'] = $heading_field_id;
										
					foreach($import_fields as $c=>$v)
					{
						if($v==$heading_field_id)
						{
							if(in_array($app_fields_cache[$entities_id][$heading_field_id]['type'],['fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel']) and isset($sql_data['field_' . $heading_field_id]))
							{
								$_POST['update_use_column'] =  'data:' . $sql_data['field_' . $heading_field_id];
							}
							else 
							{
								$_POST['update_use_column'] = $c;
							}
							
						}						
					}
					

				}
	
				//do update
				$item_id = false;
				$item_has_updated = false;
				if($_POST['import_action']=='update' or $_POST['import_action']=='update_import')
				{
					$field_info = db_find('app_fields',$_POST['update_by_field']);
					 
					$use_column_value = (substr($_POST['update_use_column'],0,5)=='data:' ? substr($_POST['update_use_column'],5) : $worksheet[$row][$_POST['update_use_column']]);
	
					if($field_info['type']=='fieldtype_id')
					{
						$where_sql = " where id='" . db_input($use_column_value) . "'";
					}
					else
					{
						$where_sql = " where field_" . $field_info['id'] . "='" . db_input($use_column_value) . "'";
					}
					
					$where_sql .= " and parent_item_id = '" . $import_entity_parent_item_id . "'";
	
					$item_query = db_query("select id from app_entity_" . $entities_id . $where_sql);
					if($item = db_fetch_array($item_query) and count($sql_data))
					{
						db_perform('app_entity_' . $entities_id,$sql_data,'update',"id=" . $item['id']);
						$item_has_updated = true;
						 
						$count_items_updated++;
						
						$item_id = $item['id'];
						
						$import_entity_parent_item_id = $item_id;
					}
				}
				 
				//do insert
				if(!$item_has_updated and ($_POST['import_action']=='import' or $_POST['import_action']=='update_import'))
				{
					//skip not unique items
					if(!$is_unique_item) continue;
					
					//set other values
					$sql_data['date_added'] = time();
					$sql_data['created_by'] = $app_logged_users_id;
					$sql_data['parent_item_id'] = (int)$import_entity_parent_item_id;
		    
					
					//print_rr($sql_data);
					//exit();
		    
					db_perform('app_entity_' . $entities_id,$sql_data);
		    
					$item_id = db_insert_id();
					
					$import_entity_parent_item_id = $item_id;
	
					$count_items_added++;
		    				
				}
				
				//print_rr($choices_values);
				//exit();
				
				//insert choices values if exist
				if(count($choices_values)>0 and $item_id)
				{
					//reset current choices values if action is "update"
					if($_POST['import_action']!='import')
					{
						db_query("delete from app_entity_" . $entities_id . "_values where items_id = '" . $item_id . "' and fields_id='" . $field_id . "'");
					}
					
					foreach($choices_values as $field_id=>$values)
					{
						foreach($values as $value)
						{
							db_query("INSERT INTO app_entity_" . $entities_id . "_values (items_id, fields_id, value) VALUES ('" . $item_id . "', '" . $field_id . "', '" . $value . "');");
						}
							
					}
				}

			}
		}


		//exit();

		if(count($already_exist_username)>0)
		{
			$alerts->add(TEXT_USERS_IMPORT_ERROR . ' ' . implode(', ',$already_exist_username),'warning');
		}

		switch($_POST['import_action'])
		{
			case 'import':
				$alerts->add(TEXT_COUNT_ITEMS_ADDED . ' ' . $count_items_added,'success');
				break;
			case 'update':
				$alerts->add(TEXT_COUNT_ITEMS_UPDATED . ' ' . $count_items_updated,'success');
				break;
			case 'update_import':
				$alerts->add(TEXT_COUNT_ITEMS_UPDATED . ' ' . $count_items_updated . '. ' . TEXT_COUNT_ITEMS_ADDED . ' ' . $count_items_added,'success');
				break;
		}

		//reset import fields session
		$import_fields = array();
				
		redirect_to('items/items','path=' . $redirect_path);

		exit();
		break;
	case 'bind_field':
		$col = $_POST['col'];
		$filed_id = $_POST['filed_id'];
		
		$multilevel_import = _get::int('multilevel_import');

		if($filed_id>0)
		{
			$import_fields[$col] = $filed_id;

			$v = db_find('app_fields',$filed_id);
			
			if($multilevel_import>0)
			{
				echo '<small style="font-weight: normal">' . entities::get_name_by_id($v['entities_id']) . ':</small><br>';
			}

			echo fields_types::get_option($v['type'],'name',$v['name']);
		}
		elseif(isset($import_fields[$col]))
		{
			unset($import_fields[$col]);
			echo '';
		}

		exit();
		break;

}