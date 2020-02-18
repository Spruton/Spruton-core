<?php

class dashboard_pages
{
	public $has_pages;
	
	function __construct()
	{
		$this->has_pages = false;
	}
	static function get_color_choices()
	{
		$choices = array(
				'default' => TEXT_DEFAULT,
				'warning' => TEXT_ALERT_WARNING,
				'danger' => TEXT_ALERT_DANGER,
				'success' => TEXT_ALERT_SUCCESS,
				'info' => TEXT_ALERT_INFO,
		);
	
		return $choices;
	}
	
	static function get_color_by_name($name)
	{
		$types = self::get_color_choices();
	
		return (isset($types[$name]) ? $types[$name] : '');
	}
	
	function render_info_blocks()
	{
		global $app_user;
		
		$html_sections = '';
		
		$sections_choices = [];
		$sections_choices[] = ['id'=>0,'grid'=>4,'name'=>$app_user['name']];
		$sections_query = db_query("select * from app_dashboard_pages_sections order by sort_order, name");
		while($sections = db_fetch_array($sections_query))
		{
			$sections_choices[] = ['id'=>$sections['id'],'grid'=>$sections['grid'],'name'=>$sections['name']];
		}	
		
		
		foreach($sections_choices as $section)
		{	
			$html = '';
			
			$pages_query = db_query("select * from app_dashboard_pages where sections_id='" . $section['id'] . "' and type='info_block' and find_in_set(" . $app_user['group_id'] . ", users_groups) and is_active=1 order by sort_order, name");
			
			if(db_num_rows($pages_query))
			{
				$item_query = db_query("select e.* " . fieldtype_formula::prepare_query_select(1, '') . " from app_entity_1 e where e.id='" . db_input($app_user['id']) . "' and e.field_5=1");
				$item = db_fetch_array($item_query);
			}	
			
			$count = 1;
			
			while($pages = db_fetch_array($pages_query))
			{
				$fields_html = '';
				
				if(strlen($pages['users_fields']))
				{
					
					$fields_access_schema = users::get_fields_access_schema(1,$app_user['group_id']);
					
					$fields_html = '<table class="table">';
					
					$count_fields = 0;
					
					$fields_query = db_query("select id, type, name, configuration, entities_id from app_fields where id in (" . $pages['users_fields'] . ") order by field(id," . $pages['users_fields'] . ")");				
					while($field = db_fetch_array($fields_query))
					{
						//prepare field value
						$value = items::prepare_field_value_by_type($field, $item);
											
						$cfg = new fields_types_cfg($field['configuration']);
						
						//hide if empty
						if(($cfg->get('hide_field_if_empty')==1 and strlen($value)==0) or ($cfg->get('hide_field_if_empty')==1 and in_array($field['type'],array('fieldtype_dropdown','fieldtype_radioboxes','fieldtype_created_by','fieldtype_input_date','fieldtype_input_datetime')) and $value==0))
						{
							continue;
						}
						
						//hide if date updated empty
						if($field['type']=='fieldtype_date_updated' and $value==0) continue;
						
						//check field access
						if(isset($fields_access_schema[$field['id']]))
						{
							if($fields_access_schema[$field['id']]=='hide') continue;
						}
						
						$output_options = array(
								'class'=>$field['type'],
								'value'=>$value,
								'field'=>$field,
								'item'=>$item,
								'is_listing' => true,
								'display_user_photo'=>true,
								'path'=>'1-' . $app_user['id']);
						
						$fields_html .= '
								<tr>
									<th>' . ($field_name = fields_types::get_option($field['type'], 'name', $field['name'])) . '</th>
									<td>' . ($field_value = fields_types::output($output_options)) . '</td>
								<tr>
								';
						
						$count_fields++;
					}
					
					$fields_html .= '</table>';
				}
				
							
				if($count_fields==1 and !strlen($pages['description']) and !strlen($pages['name']))
				{
					$html .= '
					<div class="col-md-' . $section['grid'] . '">
						<div class="stats-overview stat-block stats-' . $pages['color'] . '">
						 	<table width="100%">
								<tr>
							' . (strlen($pages['icon']) ? '<td width="32"><div class="icon">' . app_render_icon($pages['icon']) . '</div></td>':'') . '
									<td>
										
										<div class="display stat ok huge">
											<div class="percent float-left">
												' . $field_value . '
											</div>
										</div>
										
										<br>
												
										<div class="details">
											<div class="title">
												 ' . $field_name . '
											</div>
											<div class="numbers">
					
											</div>
										</div>
									</td>
								</tr>
							</table>
							
						</div>
					</div>			
	      ';
					
					switch($section['grid'])
					{
						case '6': $count_col = 2; 
							break;
						case '4': $count_col = 3;
							break;
						case '3': $count_col = 4;
							break;
						default: $count_col = 3;
							break;
					}
					if($count/$count_col==floor($count/$count_col)) $html .= '</div><div class="row users-info-blocks">';
					
					$count++;				
				}	
				elseif($count_fields>0 or strlen($pages['description']))
				{									
					$html .= '
							<div class="col-md-' . $section['grid'] . '">
							<div class="panel panel-' . $pages['color'] . '">
							  ' . (strlen($pages['name']) ? '<div class="panel-heading">' . (strlen($pages['icon']) ? app_render_icon($pages['icon']) . ' ' : '') . $pages['name'] . '</div>' : '') . '
							  <div class="panel-body">
							    ' . (strlen($pages['description']) ? '<p>' . $pages['description'] . '</p>':'') . '
							    ' . $fields_html . '		
							  </div>
							</div>
							</div>
							';
					
					if($count/3==floor($count/3)) $html .= '</div><div class="row users-info-blocks">';
					
					$count++;
				}
				
				
			}
			
			if(strlen($html))
			{
				$html_sections .= '
						<h3 class="page-title">' . str_replace('[user_name]',$app_user['name'],$section['name']) . '</h3>
						<div class="row users-info-blocks users-info-blocks-content">' . $html . '</div>		
						';
				
				$this->has_pages = true;
			}
		}
		
		return $html_sections;
	}
	
	function render_info_pages()
	{
		global $app_user;
	
		$html = '';
	
		$pages_query = db_query("select * from app_dashboard_pages where type='page' and find_in_set(" . $app_user['group_id'] . ", users_groups) and is_active=1 order by sort_order, name");
		
		while($pages = db_fetch_array($pages_query))
		{
			if($pages['color']=='default')
			{	
				$html .= '
						<h3 class="page-title">' . (strlen($pages['icon']) ? app_render_icon($pages['icon']) . ' ' : '') . $pages['name'] . '</h3>
						<p>' . $pages['description'] . '</p>
						';
			}
			else
			{
				$html .= '
						<div class="alert alert-' . $pages['color'] . '">
							<h3 class="page-title">' . (strlen($pages['icon']) ? app_render_icon($pages['icon']) . ' ' : '') . $pages['name'] . '</h3>
							<p>' . $pages['description'] . '</p>
						</div>		
						';
			}
		}
	
		if(strlen($html))
		{
			$this->has_pages = true;
		}
	
		return $html;
	}	
	
	static function get_section_grid_choices()
	{
		$choices = [];
		$choices[6] = '2 ' . TEXT_COLUMNS;
		$choices[4] = '3 ' . TEXT_COLUMNS;
		$choices[3] = '4 ' . TEXT_COLUMNS;
		
		return $choices;
	}
	
	static function get_section_grid_name($v)
	{
		$choices = self::get_section_grid_choices();
		
		return (isset($choices[$v]) ? $choices[$v] : '');
				
	}
	
	static function get_section_choices()
	{
		$choices = [];
		$choices[] = TEXT_DEFAULT;
		$sections_query = db_query("select * from app_dashboard_pages_sections order by sort_order, name");
		while($sections = db_fetch_array($sections_query))
		{
			$choices[$sections['id']] = $sections['name'];
		}
		
		return $choices;
	}
	
	
}