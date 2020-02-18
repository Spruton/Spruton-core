<?php

class stages_panel
{	
	static function get_type_choices()
	{
		return ['trianlge'=>TEXT_TRIANGLE,'rectangle'=>TEXT_RECTANGLE,'dot'=>TEXT_DOT,'circle'=>TEXT_CIRCLE];	
	}
	
	static function render($entities_id, $item_info)
	{
		global $app_fields_cache, $app_path, $app_user;
		 
		$fields_access_schema = users::get_fields_access_schema($entities_id,$app_user['group_id']);
		
		$html = '';
		 
		foreach($app_fields_cache[$entities_id] as $field)
		{
			//check field types
			if(!in_array($field['type'],['fieldtype_stages','fieldtype_autostatus'])) continue;
			
			//check field access
			if(isset($fields_access_schema[$field['id']]))
			{							
				if($fields_access_schema[$field['id']]=='hide') continue;
			}
							
			$cfg = new fields_types_cfg($field['configuration']);
			
			//check if panel type is enabled
			if(!strlen($cfg->get('panel_type'))) continue;
	
			if($cfg->get('use_global_list')>0)
			{
				$choices = global_lists::get_choices($cfg->get('use_global_list'),false);
			}
			else
			{
				$choices = fields_choices::get_choices($field['id'],false);
			}
	
			switch($cfg->get('panel_type'))
			{
				case 'trianlge':
					$panel_type = 'cd-breadcrumb triangle';
					break;
				case 'rectangle':
					$panel_type = 'cd-multi-steps text-center';
					break;
				case 'dot':
					$panel_type = 'cd-multi-steps text-top';
					break;
				case 'circle':
					$panel_type = 'cd-multi-steps text-bottom count';
					break;				
			}
	
	
			$html .= '
				<div class="prolet-body-actions">	
					<ol class="stages-panel-' . $field['id'] . ' '. $panel_type . '">';
	
			$current_choice_id = $item_info['field_' . $field['id']];
	
			$has_current = (isset($choices[$current_choice_id]) ? false : true);
			$count_after_current = 0;
			foreach($choices as $choice_id => $choice_name)
			{
				$li_class = '';
				
				if($has_current) $count_after_current++;
				
				//hanlde lie css class
				if($current_choice_id==$choice_id)
				{
					$li_class = 'class="current"';
					$has_current = true;
				}
				elseif(!$has_current)
				{
					$li_class = 'class="visited"';
				}
				
				//handle click action
				$click_url = '#';
				$click_action = 'onClick="return false"';	
				
				//check if has process url
				$process_url = '';
				$process_access = false;
				if(($process_id = (int)$cfg->get('run_process_for_choice_' . $choice_id))>0)
				{
					$buttons_query = db_query("select * from app_ext_processes where id='" . $process_id . "' and is_active=1");
					if($buttons = db_fetch_array($buttons_query))
					{					
						$processes = new processes($entities_id);
						
						//get manually url
						if($processes->has_enter_manually_fields($process_id))
						{
							$process_url = url_for('items/processes','id=' . $process_id . '&path=' . $app_path . '&redirect_to=items_info');														
						}
						
						//check if has access (is there is button in list)
						$buttons_list = $processes->get_buttons_list();
							
						foreach($buttons_list as $button)
						{
							if($button['id']==$process_id)
							{
								$process_access = true;
							}
						}
					}
				}
				
				if($cfg->get('click_action')=='change_value')
				{
					$click_action = 'onClick="open_dialog(\'' . (strlen($process_url) ? $process_url : url_for('items/stages','path=' . $app_path . '&field_id=' . $field['id'] . '&value_id=' . $choice_id)) . '\')" class="clickable"';				
				}
				elseif($cfg->get('click_action')=='change_value_next_step' and $count_after_current==1)
				{
					$click_action = 'onClick="open_dialog(\'' . (strlen($process_url) ? $process_url : url_for('items/stages','path=' . $app_path . '&field_id=' . $field['id'] . '&value_id=' . $choice_id)) . '\')" class="clickable"';
				}
				
				//resed edit action if now edit access
				if(isset($fields_access_schema[$field['id']]))
				{
					if($fields_access_schema[$field['id']]=='view')
					{
						$click_url = '#';
						$click_action = 'onClick="return false"';
					}
				}
				
				//reset edit action if no access to process
				if($process_id>0 and !$process_access)
				{
					$click_url = '#';
					$click_action = 'onClick="return false"';
				}
	
	
				$html .= '<li ' . $li_class . '><a href="' . $click_url . '" ' . $click_action . '>' . $choice_name . '</a></li>';
			}
	
			$html .= '
					</ol>
				</div>';
			
			$html .= self::render_css($field);
	
		}
		 
		return $html;		 
	}
	
	static function render_css($field)
	{
		$cfg = new fields_types_cfg($field['configuration']);
		
		$css = '';
		
		if(strlen($cfg->get('color')))
		{
			$css .= '
				@media only screen and (min-width: 768px) {	
					.stages-panel-' . $field['id'] . '.cd-breadcrumb.triangle li.visited > * {
					    /* selected step */
					    color: #ffffff;
					    background-color: ' . $cfg->get('color') . ';
					    border-color: ' . $cfg->get('color') . ';
					  }
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-center li.visited > * {					 
					    background-color: ' . $cfg->get('color') . ';
					}
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps li.visited::after {
					    background-color: ' . $cfg->get('color') . ';
					}
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-top li.visited > *::before{
					    background-color: ' . $cfg->get('color') . '; 		
					}
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-bottom li.visited > *::before{
					    background-color: ' . $cfg->get('color') . '; 		
					}
				}					    		
					';
		}
		
		if(strlen($cfg->get('color_active')))
		{
			$css .= '
				@media only screen and (min-width: 768px) {
					.stages-panel-' . $field['id'] . '.cd-breadcrumb.triangle li.current > * {
					    /* selected step */
					    color: #ffffff;
					    background-color: ' . $cfg->get('color_active') . ';
					    border-color: ' . $cfg->get('color_active') . ';
					}
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-center li.current > * {
						  color: #ffffff;
						  background-color: ' . $cfg->get('color_active') . ';
					}
						    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-top li.current > *::before{
					    background-color: ' . $cfg->get('color_active') . '; 		
					}
					    		
					.stages-panel-' . $field['id'] . '.cd-multi-steps.text-bottom li.current > *::before{
					    background-color: ' . $cfg->get('color_active') . '; 		
					}
						
				}
					';
		}
		
		if(strlen($css))
		{
			$css = '
					<style>
					' . $css . '
					</style>	
					';
		}
		
		return $css;
	}
}