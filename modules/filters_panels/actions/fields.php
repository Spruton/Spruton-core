<?php

$panels_id = _get::int('panels_id');
$entities_id = _get::int('entities_id');

switch($app_module_action)
{		
	case 'save':
						
		$sql_data = array(
			'panels_id' => $panels_id,
			'entities_id' => $entities_id,
			'fields_id'=>$_POST['fields_id'],
			'width'=>(isset($_POST['width']) ? $_POST['width']:''),
			'exclude_values'=>(isset($_POST['exclude_values']) ? implode(',',$_POST['exclude_values']):''),
			'display_type'=>(isset($_POST['display_type']) ? $_POST['display_type']:''),	
			'height'=>(isset($_POST['height']) ? $_POST['height']:''),
			
		);

		if(isset($_GET['id']))
		{						
			db_perform('app_filters_panels_fields',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			$fields_query = db_query("select max(sort_order) as max_sort_order from app_filters_panels_fields where panels_id='" . _get::int('panels_id'). "'");
			$fields = db_fetch_array($fields_query);
				
			$sql_data['sort_order'] = $fields['max_sort_order']+1;
			
			db_perform('app_filters_panels_fields',$sql_data);
		}

		redirect_to('filters_panels/fields','panels_id=' . $panels_id . '&entities_id=' . $_GET['entities_id']);

		break;

	case 'delete':

		db_delete_row('app_filters_panels_fields',_get::int('id'));

		redirect_to('filters_panels/fields','panels_id=' . $panels_id . '&entities_id=' . $_GET['entities_id']);
		break;
		
	case 'sort':
		$choices_sorted = $_POST['choices_sorted'];
	
		if(strlen($choices_sorted)>0)
		{
			$choices_sorted = json_decode(stripslashes($choices_sorted),true);

			$sort_order = 0;
			foreach($choices_sorted as $v)
			{
				db_query("update app_filters_panels_fields set sort_order={$sort_order} where id={$v['id']}");
				$sort_order++;
			}
		}
		 
		redirect_to('filters_panels/fields','entities_id=' . $_GET['entities_id'] . '&panels_id=' . $panels_id);
		break;		
	case 'load_panels_fields':
		$types_for_filters_list = fields_types::get_types_for_filters_list();
				
		//include special filters for Users
		if($entities_id==1)
		{
			$types_for_filters_list .= ", 'fieldtype_user_accessgroups', 'fieldtype_user_status'";
		}
		
		//include input fields
		$types_for_filters_list .= "," . fields_types::get_types_for_search_list();
		
		$choices = array();
		$choices[''] = '';
		$where_sql = " and f.id not in (select fields_id from app_filters_panels_fields where entities_id={$entities_id} " . (isset($_GET['id']) ? " and id!=" . $_GET['id'] : "") . ")";
		$fields_query = db_query("select f.*, t.name as tab_name, if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type in (" . $types_for_filters_list . ") {$where_sql} and f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
		while($fields = db_fetch_array($fields_query))
		{
			$choices[$fields['id']] = fields_types::get_option($fields['type'],'name',$fields['name']);
		}
		
		if(isset($_GET['id']))
		{
			$obj = db_find('app_filters_panels_fields',$_GET['id']);
		}
		else
		{
			$obj = db_show_columns('app_filters_panels_fields');
		}
		
		$html = '
			<div class="form-group">
			<label class="col-md-3 control-label" for="fields_id">' . TEXT_FIELD . '</label>
		    <div class="col-md-9">	
		  	  ' . select_tag('fields_id',$choices ,$obj['fields_id'],array('class'=>'form-control required','onChange'=>'load_panels_fields_settings()')) . '
		    </div>			
		  </div>
		 ';
		
		echo $html;
		
		exit();
		break;
	case 'load_panels_fields_settings':
		
		$fields_id = _get::int('fields_id');
		$field_info = db_find('app_fields',$fields_id);
		
		$panels_info = db_find('app_filters_panels',$panels_id);
		
		
		$obj = isset($_GET['id']) ? db_find('app_filters_panels_fields',$_GET['id']) : db_show_columns('app_filters_panels_fields');
		
		$html = '';
				
		if(in_array($field_info['type'],array(
			'fieldtype_image_map',
			'fieldtype_autostatus',
			'fieldtype_checkboxes',
			'fieldtype_radioboxes',
			'fieldtype_dropdown',
			'fieldtype_dropdown_multiple',
			'fieldtype_dropdown_multilevel',
			'fieldtype_grouped_users',
			'fieldtype_tags',				
		)))
		{
			$cfg = new fields_types_cfg($field_info['configuration']);
			
			if($cfg->get('use_global_list')>0)
			{
				$choices = global_lists::get_choices($cfg->get('use_global_list'),true);				
			}
			else
			{
				$choices = fields_choices::get_choices($field_info['id'],true);				
			}
			
			$html .= '
				<div class="form-group">
				<label class="col-md-3 control-label" for="fields_id">' . TEXT_EXCLUDE_VALUES . '</label>
			    <div class="col-md-9">
			  	  ' . select_tag('exclude_values[]',$choices ,$obj['exclude_values'],array('class'=>'form-control chosen-select','multiple'=>'multiple')) . '
			    </div>
			  </div>
			 ';
		}	
		
		
		$choices = [];
		
		if(in_array($field_info['type'],array(
				'fieldtype_autostatus',
				'fieldtype_entity_multilevel',
				'fieldtype_user_roles',
				'fieldtype_entity_ajax',
				'fieldtype_tags',
				'fieldtype_created_by',
				'fieldtype_user_status',
				'fieldtype_user_accessgroups',
				'fieldtype_dropdown',
				'fieldtype_radioboxes',
				'fieldtype_grouped_users',
				'fieldtype_checkboxes',
				'fieldtype_dropdown_multiple',
				'fieldtype_entity',
				'fieldtype_users',				
				'fieldtype_dropdown_multilevel',				
		)))
		{
			$choices['dropdown'] = TEXT_FIELDTYPE_DROPDOWN_TITLE;
			$choices['dropdown_multiple'] = TEXT_FIELDTYPE_DROPDOWN_MULTIPLE_TITLE;
			
			if($panels_info['position']=='vertical')
			{
				$choices['checkboxes'] = TEXT_FIELDTYPE_CHECKBOXES_TITLE;
				$choices['radioboxes'] = TEXT_FIELDTYPE_RADIOBOXES_TITLE;
			}		
		}
						
		if(count($choices))
		{	
			$html .= '
				<div class="form-group">
				<label class="col-md-3 control-label" for="fields_id">' . TEXT_DISPLAY_AS . '</label>
			    <div class="col-md-9">
			  	  ' . select_tag('display_type',$choices ,$obj['display_type'],array('class'=>'form-control required')) . '
			    </div>
			  </div>
			 ';
			
			if($panels_info['position']=='horizontal')
			{
				
				$html .= '
					<div class="form-group">
					<label class="col-md-3 control-label" for="width">' . TEXT_WIDHT . '</label>
				    <div class="col-md-9">	
				  	  ' . select_tag('width',filters_panels::get_field_width_choices(),$obj['width'],array('class'=>'form-control input-medium')) . '
				    </div>			
				  </div>
				 ';
			}
		}
		
		echo $html;
		
		exit();
		break;
}

