<?php

class filters_panels
{
	public $entities_id, $reports_id, $listing_container, $vertical_width, $fields_access_schema, $parent_entity_item_id;
	
	function __construct($entities_id,$reports_id,$listing_container,$parent_entity_item_id=false)
	{
		global $app_user;
	
		$this->entities_id = $entities_id;
		$this->reports_id = $reports_id;
		$this->listing_container = $listing_container;
		$this->parent_entity_item_id = $parent_entity_item_id;
		
		$this->vertical_width = $this->get_vertical_width();
		
		$this->fields_access_schema = users::get_fields_access_schema($entities_id,$app_user['group_id']);
		
		$this->type = '';
		$this->load_items_listing_funciton_name  = 'load_items_listing';
		$this->custom_panel_id = '';
		$this->custom_panel_css = '';
	}
			
	static function get_fields_list($entities_id)
	{
		global $app_user;
		
		$list = [];
		
		$panels_query = db_query("select * from app_filters_panels where (length(users_groups)=0 or find_in_set(" . $app_user['group_id'] . ",users_groups)) and is_active=1 and entities_id='" . $entities_id . "' order by sort_order");
		$count_panels = db_num_rows($panels_query);
		while($panels = db_fetch_array($panels_query))
		{
						
			$fields_query = db_query("select * from app_filters_panels_fields where panels_id='" . $panels['id'] . "' order by sort_order");
			while($fields = db_fetch_array($fields_query))
			{
				$list[] = $fields['fields_id'];
			}
		}
		
		return $list;
	}
	
	function set_type($type)
	{
		$this->type = $this->custom_panel_id = $type;
		$this->custom_panel_css = '.' . $type;
	}
	
	function set_items_listing_funciton_name($name)
	{
		$this->load_items_listing_funciton_name = $name;
	}
	
	function render_horizontal()
	{
		global $app_user, $app_module_path;
				 		
		$html = '<div class="filters-panels horizontal-filters-panels">';
				
		$panels_query = db_query("select f.* from app_filters_panels f where (select count(*) from app_filters_panels_fields fp where fp.panels_id=f.id)>0 and f.position='horizontal' and f.type='" . $this->type . "' and (length(f.users_groups)=0 or find_in_set(" . $app_user['group_id'] . ",f.users_groups)) and f.is_active=1 and f.entities_id='" . $this->entities_id . "' order by f.sort_order");
		$count_panels = db_num_rows($panels_query);
		while($panels = db_fetch_array($panels_query))
		{			
			$html .= '<ul class="list-inline filters-panels-' . $panels['id'] . '">';
			
			$fields_query = db_query("select *, f.type from app_filters_panels_fields fp, app_fields f where f.id=fp.fields_id  and fp.panels_id='" . $panels['id'] . "' order by fp.sort_order");								
			while($fields = db_fetch_array($fields_query))
			{		
				//check field access
				if(isset($this->fields_access_schema[$fields['fields_id']]))
				{
					if($this->fields_access_schema[$fields['fields_id']]=='hide') continue;
				}
				
				//skip filter by parent in main listing
				if($app_module_path=='items/items' and $fields['type']=='fieldtype_parent_item_id') continue;				
				
				$html .= '<li>' . $this->render_fields($fields,$panels) . '</li>';
			}
			
			if($panels['is_active_filters']==0)
			{
				$html .= '<li><br><a href="javascript: apply_panel_filters(' . $panels['id']. ')" class="btn btn-info" title="' . TEXT_SEARCH . '"><i class="fa fa-search" aria-hidden="true"></i> ' . TEXT_SEARCH . '</a></li>';
			}
						
			$html .= '<li><br><a href="javascript: reset_panel_filters' . $this->custom_panel_id . '(' . $panels['id']. ')" class="btn btn-default" title="' . TEXT_RESET_FILTERS . '"><i class="fa fa-refresh" aria-hidden="true"></i></a></li>';
						
			$html .= '</ul>';						
		}
		
		$html .= '</div>';
		
		
		$html .= $this->render_js();
		
		
		return $html;
	}
	
	function get_vertical_width()
	{
		global $app_user;
		
		$panels_query = db_query("select max(width) as max_width from app_filters_panels where position='vertical' and (length(users_groups)=0 or find_in_set(" . $app_user['group_id'] . ",users_groups)) and is_active=1 and entities_id='" . $this->entities_id . "' order by sort_order");
		$panels = db_fetch_array($panels_query);
		
		return (int)$panels['max_width'];
	}
	
	function render_vertical()
	{
		global $app_user, $app_module_path;
		
		if($this->vertical_width==0) return '';
		
		$html = '
			<div class="col-sm-' . $this->vertical_width . ' filters-panels vertical-filters-panels">	
				';
			
		$panels_query = db_query("select * from app_filters_panels where position='vertical' and (length(users_groups)=0 or find_in_set(" . $app_user['group_id'] . ",users_groups)) and is_active=1 and entities_id='" . $this->entities_id . "' order by sort_order");
		while($panels = db_fetch_array($panels_query))
		{					
			$html .= '
					<div class="filters-panels-' . $panels['id'] . '">
					';
			
			$fields_query = db_query("select *, f.type from app_filters_panels_fields fp, app_fields f where f.id=fp.fields_id  and fp.panels_id='" . $panels['id'] . "' order by fp.sort_order");
			while($fields = db_fetch_array($fields_query))
			{
				//check field access
				if(isset($this->fields_access_schema[$fields['fields_id']]))
				{
					if($this->fields_access_schema[$fields['fields_id']]=='hide') continue;
				}
				
				//skip filter by parent in main listing
				if($app_module_path=='items/items' and $fields['type']=='fieldtype_parent_item_id') continue;
				
				$html .= '<div class="fields-container">' . $this->render_fields($fields,$panels) . '</div>';
			}
				
			$html .= '
						<div class="buttons">
							' . ($panels['is_active_filters']==0 ? '<a href="javascript: apply_panel_filters(' . $panels['id']. ')" class="btn btn-info" title="' . TEXT_SEARCH . '"><i class="fa fa-search" aria-hidden="true"></i> ' . TEXT_SEARCH . '</a>':'') . '
							<a href="javascript: reset_panel_filters(' . $panels['id']. ')" class="btn btn-default" title="' . TEXT_RESET_FILTERS . '"><i class="fa fa-refresh" aria-hidden="true"></i> ' . TEXT_RESET . '</a>
						</div>
					</div>';
		
		}
		
		$html .= '				
			</div>';
		
		return $html;
	}
	
	function render_fields($panel_field,$panel_info)
	{	
		$field_info_query = db_query("select * from app_fields where id='" . $panel_field['fields_id'] . "'");
		if(!$field_info = db_fetch_array($field_info_query))
		{
			return '';
		}
		
		$panels_id_str = ($panel_info['is_active_filters']!=1 ? '-' . $panel_info['id']:'');
		
		$filters_values = '';
		$reports_filters_query = db_query("select * from app_reports_filters where fields_id='" . $field_info['id'] . "' and reports_id='" . $this->reports_id . "' and filters_condition!='exclude'");
		if($reports_filters = db_fetch_array($reports_filters_query))
		{
			$filters_values = $reports_filters['filters_values'];
		}
		
		
		$field_name = strlen($field_info['short_name']) ? $field_info['short_name'] : fields_types::get_option($field_info['type'],'name',$field_info['name']);		
		
		$html = '				
				<div class="heading">
					' . $field_name . ': <a href="javascript:delete_field_fielter_value' . $this->custom_panel_id . '(' . $field_info['id'] . ')" title="' . TEXT_RESET . '"><i class="fa fa-times" aria-hidden="true"></i></a>						
			  </div>';
					
		switch($field_info['type'])
		{		
			case 'fieldtype_parent_item_id':
				$choices = [];
				
				$entity_info = db_find('app_entities',$field_info['entities_id']);
				
				if($entity_info['parent_id']>0)
				{
					$items_query = db_query("select e.* from app_entity_" . $entity_info['parent_id'] . " e where e.id>0 " . items::add_access_query($entity_info['parent_id'],'') . ' '. items::add_access_query_for_parent_entities($entity_info['parent_id']) . ' ' . items::add_listing_order_query_by_entity_id($entity_info['parent_id']));
					while($items = db_fetch_array($items_query))
					{
						$choices[$items['id']] = items::get_heading_field($entity_info['parent_id'],$items['id']);
					}
				}
				
				break;
			case 'fieldtype_date_added':
			case 'fieldtype_date_updated':
			case 'fieldtype_input_date':
			case 'fieldtype_input_datetime':
		  case 'fieldtype_dynamic_date':
				
				$filters_values = explode(',',$filters_values);
				//print_r($reports_filters);
				
				$html .='
						<form  action="' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '" method="post">
							' . input_hidden_tag('field_id',$field_info['id']). '
							<div class="input-group input-medium datepicker input-daterange daterange-filter-' . $field_info['id'] . '">												
								' . input_tag('field_val[]',(isset($filters_values[1]) ? $filters_values[1] : ''),array('class'=>'form-control filters-panels-date-fields' . $panels_id_str . ' filters-panels-date-field-' . $field_info['id'], 'data-field-id'=>$field_info['id'], 'placeholder'=>TEXT_DATE_FROM)) .'
								<span class="input-group-addon" style="width: 1px; padding:0;">									
								</span>
								' . input_tag('field_val[]',(isset($filters_values[2]) ? $filters_values[2] : ''),array('class'=>'form-control filters-panels-date-fields' . $panels_id_str . ' filters-panels-date-field-' . $field_info['id'], 'data-field-id'=>$field_info['id'], 'placeholder'=>TEXT_DATE_TO)) . '			
							</div>
						</form>
						';
				break;
			
			case 'fieldtype_access_group':
				$choices = fieldtype_access_group::get_choices($field_info);
				break;
				
			case 'fieldtype_image_map':
			case 'fieldtype_autostatus':
			case 'fieldtype_checkboxes':
			case 'fieldtype_radioboxes':
			case 'fieldtype_dropdown':
			case 'fieldtype_dropdown_multiple':
			case 'fieldtype_dropdown_multilevel':
			case 'fieldtype_grouped_users':
			case 'fieldtype_tags':
			case 'fieldtype_stages':
			
				$cfg = new fields_types_cfg($field_info['configuration']);
			
				if($cfg->get('use_global_list')>0)
				{
					$choices = global_lists::get_choices($cfg->get('use_global_list'),false);
				}
				else
				{
					$choices = fields_choices::get_choices($field_info['id'],false);
				}
				
				//exlude values
				if(strlen($panel_field['exclude_values']))
				{
					foreach(explode(',',$panel_field['exclude_values']) as $id)
					{
						if(isset($choices[$id])) unset($choices[$id]);
					}
				}
																		
				break;
				
			case 'fieldtype_user_accessgroups':
				$choices = access_groups::get_choices(true);
				break;
				
			case 'fieldtype_user_status':
				$choices = array('1'=>TEXT_ACTIVE,'0'=>TEXT_INACTIVE);
				break;
			
			case 'fieldtype_user_roles':
				$choices = fieldtype_user_roles::get_choices($field_info, ['parent_entity_item_id'=>$this->parent_entity_item_id]);
				break;
				
			case 'fieldtype_users':
				$choices = fieldtype_users::get_choices($field_info, ['parent_entity_item_id'=>$this->parent_entity_item_id]);
				break;
				
			case 'fieldtype_created_by':
				$choices = users::get_choices_by_entity($this->entities_id,'create');
				break;
				
			case 'fieldtype_entity_multilevel':
			case 'fieldtype_entity_ajax':
			case 'fieldtype_entity':
				$parent_entity_item_is_the_same = false;
				$choices_tmp = fieldtype_entity::get_choices($field_info, ['parent_entity_item_id'=>$this->parent_entity_item_id],'',$parent_entity_item_is_the_same);				
				
				$choices = [];
				foreach($choices_tmp as $k=>$v)
				{
					if($k>0) $choices[$k] = $v;
				}												
				break;
				
			default: 
				
				$input_width = 'input-medium';
				if(in_array($field_info['type'],['fieldtype_id','fieldtype_formula','fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_years_difference','fieldtype_months_difference','fieldtype_hours_difference','fieldtype_days_difference','fieldtype_mysql_query','fieldtype_auto_increment']))
				{
					$input_width = 'input-small';
				}
				
				if(in_array($field_info['type'],['fieldtype_input','fieldtype_text_pattern_static']) and strlen($panel_field['width']))
				{
					$input_width = $panel_field['width'];
				}
				
				if($panel_info['position']=='vertical')
				{
					$input_width = '';
				}
				
				$html .='
						<form class="filters-panels-form" action="' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '" method="post">
							' . input_hidden_tag('field_id',$field_info['id']). '
							<div class="input-group ' . $input_width . '">
								' . input_tag('field_val',$filters_values,array('class'=>'form-control filters-panels-input-fields' . $panels_id_str . ' filters-panels-input-field-' . $field_info['id'], 'data-field-id'=>$field_info['id'])) .'
								' . ($panel_field['search_type_match']==1 ? input_hidden_tag('search_type_match',1) : '') . '		
								<span class="input-group-btn">
									<button class="btn btn-default" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
								</span>
							</div>
				
				
						</form>
						';
				break;
		}
			
		
		if($panel_info['position']=='vertical')
		{
			$panel_field['width'] = '';
		}
						
		switch($panel_field['display_type'])
		{
			case 'dropdown':
				$attributes = array('class'=>'form-control filters-panels-fields' . $panels_id_str. ' filters-panels-field-' . $field_info['id'] . ' chosen-select ' . $panel_field['width'], 'data-field-id'=>$field_info['id']);
				$html .= select_tag('values[]',[''=>'']+$choices,$filters_values,$attributes);
				break;
			case 'dropdown_multiple':
				$attributes = array('class'=>'form-control filters-panels-fields' . $panels_id_str. ' filters-panels-field-' . $field_info['id'] . ' chosen-select '  . $panel_field['width'],'multiple'=>'multiple','style'=>'height:24px; visibility: hidden', 'data-field-id'=>$field_info['id']);
				$html .= select_tag('values[]',$choices,$filters_values,$attributes);
				break;
			case 'checkboxes':
				$attributes = array('class'=>'filters-panels-checkbox-fields' . $panels_id_str. ' filters-panels-checkbox-field-' . $field_info['id'] . '', 'data-field-id'=>$field_info['id']);
				$html .= '<div class="panel-field-container" ' . ($panel_field['height'] ? 'style="max-height:' . $panel_field['height'] . 'px; overflow-y: scroll "':'') . '>' . select_checkboxes_tag('values',$choices,$filters_values,$attributes) . '</div>';
				break;
			case 'radioboxes':
				$attributes = array('class'=>'filters-panels-checkbox-fields' . $panels_id_str. ' filters-panels-checkbox-field-' . $field_info['id'] . '', 'data-field-id'=>$field_info['id']);
				$html .= '<div class="panel-field-container" '  . '>' . select_radioboxes_tag('values',$choices,$filters_values,$attributes) . '</div>';
				break;
		}
						
		return  $html ;
	}
	
	function render_js()
	{
	
		$html = '
				<script>			
					$(function(){
				
						//dorpdowns		
						$("' . $this->custom_panel_css . ' .filters-panels-fields").change(function(){
							field_id = $(this).attr("data-field-id")
							field_val = $(this).val();
							$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '",
								data: {field_id:field_id,field_val:field_val}								
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})						
						})
										
						//input 
						$("' . $this->custom_panel_css . ' .filters-panels-form").submit(function(){
							$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '",
								data: $(this).serializeArray()								
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})
						  return false;
						})
								
						//checkoxes & radiboxes				
						$("' . $this->custom_panel_css . ' .filters-panels-checkbox-fields").change(function(){
							field_id = $(this).attr("data-field-id")
										
							field_val = [];			
							$(".filters-panels-checkbox-field-"+field_id+":checked").each(function(){
								field_val.push($(this).val())
							})										
							
							$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '",
								data: {field_id:field_id,field_val:field_val}								
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})
						})		
										
						//dates
						var filters_panels_date_fields_is_init = false;
										
						$("' . $this->custom_panel_css . ' .filters-panels-date-fields").click(function(){
							 filters_panels_date_fields_is_init = true;										
						})				
										
						$("' . $this->custom_panel_css . ' .filters-panels-date-fields").on("changeDate", function(e) {
							
							//skip ajax load for first load			
							if(filters_panels_date_fields_is_init==false) return false;			
										
							var field_id = $(this).attr("data-field-id")
										
							setTimeout(function(){			
										
								field_val = [];
								field_val.push("")
								$(".filters-panels-date-field-"+field_id).each(function(){
									field_val.push($(this).val())
								})		
											
								//alert(field_val)
											
								$.ajax({
									method: "POST",
									url: "' . url_for('reports/filters','action=set_field_fielter_value&reports_id=' . $this->reports_id) . '",
									data: {field_id:field_id,field_val:field_val}								
								}).done(function(){
									' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
								})
							
							},100);			
								
						})				
					})
										
					function apply_panel_filters' . $this->custom_panel_id . '(panel_id)
					{						
						fields_values = {};
																						
						$(".filters-panels-fields-"+panel_id).each(function(){
							field_id = $(this).attr("data-field-id")
							fields_values[field_id] = $(this).val();
						})	
										
						$(".filters-panels-input-fields-"+panel_id).each(function(){
							field_id = $(this).attr("data-field-id")
							fields_values[field_id] = $(this).val();
						})				
										
						$(".filters-panels-date-fields-"+panel_id).each(function(){
							field_id = $(this).attr("data-field-id")
										
							if(!fields_values[field_id])
							{ 			
										
								field_val = [];
								field_val.push("")
								$(".filters-panels-date-field-"+field_id).each(function(){
									field_val.push($(this).val())
								})				
											
								fields_values[field_id] = field_val;
							}
						})	
										
						$(".filters-panels-checkbox-fields-"+panel_id).each(function(){
							field_id = $(this).attr("data-field-id")
										
							if(!fields_values[field_id])
							{ 													
								field_val = [];			
								$(".filters-panels-checkbox-field-"+field_id+":checked").each(function(){
									field_val.push($(this).val())
								})	
								
											
								if(field_val.length>0)
								{			
									fields_values[field_id] = field_val;
								}
								else
								{
									fields_values[field_id] = "";		
								}			
							}
						})				
										
						//console.log(fields_values)														
																						
						$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=set_multiple_fields_fielter_values&reports_id=' . $this->reports_id) . '",
								data: {fields_values: fields_values}								
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})				
					}					
										
					function delete_field_fielter_value' . $this->custom_panel_id . '(field_id)
					{
						$(".filters-panels-field-"+field_id).val("");
										
						//reset date				
						$(".filters-panels-date-field-"+field_id).val("");										
										
						//reset input				
						$(".filters-panels-input-field-"+field_id).val("");				
								
						//reset chosen				
						if($(".filters-panels-field-"+field_id).hasClass("chosen-select"))
						{
							$(".filters-panels-field-"+field_id).trigger("chosen:updated");			
						}
						
						//reset checkboxes				
						$(".filters-panels-checkbox-field-"+field_id+":checked").each(function(){
							$(this).prop("checked",false)
							id = $(this).val();										
							$("#uniform-values_"+id+" span").removeClass("checked")			
						})
																																
						$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=delete_field_fielter_value&reports_id=' . $this->reports_id) . '",
								data: {field_id:field_id}								
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})				
					}	
										
					function reset_panel_filters' . $this->custom_panel_id . '(panel_id)
					{
						$(".filters-panels-"+panel_id+" .form-control").val("");
						$(".filters-panels-"+panel_id+" .chosen-select").trigger("chosen:updated");
										
						//reset checkboxes				
						$(".filters-panels-"+panel_id+" input:checked").each(function(){
							$(this).prop("checked",false)
							id = $(this).val();										
							$("#uniform-values_"+id+" span").removeClass("checked")			
						})				
										
						$.ajax({
								method: "POST",
								url: "' . url_for('reports/filters','action=reset_panel_filters&reports_id=' . $this->reports_id) . '",
								data: {panels_id:panel_id}		
							}).done(function(){
								' . $this->load_items_listing_funciton_name . '("' . $this->listing_container. '",1)
							})				
					}					
				</script>
				';
				
		return $html;
	}
	
	
	static function get_position_choices()
	{
		return ['horizontal'=>TEXT_HORIZONTAL,'vertical'=>TEXT_VERTICAL];
	}
	
	static function get_position_name($type)
	{
		$choices = self::get_position_choices();
		
		return $choices[$type];
	}
	
	static function get_field_width_choices()
	{				
		return array('input-small'=>TEXT_INPTUT_SMALL,'input-medium'=>TEXT_INPUT_MEDIUM,'input-large'=>TEXT_INPUT_LARGE,'input-xlarge'=>TEXT_INPUT_XLARGE);
	}
	
	static function get_width_choices()
	{
		return array('1'=>'10%','2'=>'20%','3'=>'30%','4'=>'40%');
	}
	
	static function get_width_name($key)
	{
		$choices = self::get_width_choices();
		
		return $choices[$key];
	}
	
	static function get_field_display_type_name($key)
	{
		$choices = [];
		$choices['dropdown'] = TEXT_FIELDTYPE_DROPDOWN_TITLE;
		$choices['dropdown_multiple'] = TEXT_FIELDTYPE_DROPDOWN_MULTIPLE_TITLE;
		$choices['checkboxes'] = TEXT_FIELDTYPE_CHECKBOXES_TITLE;
		$choices['radioboxes'] = TEXT_FIELDTYPE_RADIOBOXES_TITLE;
		
		return isset($choices[$key]) ? $choices[$key] : '';
	}
	
	//check if user has any panels setup
	static function has_any($entity_id, $entity_cfg)
	{
		global $app_user;
		
		$common_filters_query = db_query("select id, name from app_reports where entities_id='" . $entity_id . "' and reports_type='common_filters' and (length(users_groups)=0 or find_in_set(" . $app_user['group_id']. ",users_groups)) limit 1");
		
		$panels_query = db_query("select * from app_filters_panels where (length(users_groups)=0 or find_in_set(" . $app_user['group_id'] . ",users_groups)) and is_active=1 and entities_id='" . $entity_id . "' limit 1");
		
		if($panels = db_fetch_array($panels_query) or $common_filters = db_fetch_array($common_filters_query) or filters_preivew::has_default_panel_access($entity_cfg))
		{
			return true;			
		}
		else
		{
			return false;
		}
	}
}