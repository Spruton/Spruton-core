<?php


$html .= '
<div class="table-scrollable ">
  <div class="table-scrollable table-wrapper slimScroll" id="slimScroll">
    <table class="table table-striped table-bordered table-hover ' . ($listing->is_resizable() ? 'table-resizable':''). '" data-count-fixed-columns="' . reports::get_count_fixed_columns($_POST['reports_id'],$has_with_selected) . '" data-fixed-head="1" data-resizable="' . $listing->is_resizable(). '" ' . $listing->resizable_table_widht(). '>
      <thead>
        <tr>
          ' . ($has_with_selected ?  '<th class="multiple-select-action-th">' . input_checkbox_tag('select_all_items',$_POST['reports_id'],array('class'=>'select_all_items')) . '</th>' : '');

//render listing heading
$listing_fields = array();
$listing_numeric_fields = array();
$fields_query = db_query($listing->get_fields_query());
while($v = db_fetch_array($fields_query))
{
	//check field access
	if(isset($fields_access_schema[$v['id']]))
	{
		if($fields_access_schema[$v['id']]=='hide') continue;
	}

	//skip fieldtype_parent_item_id for deafult listing
	if($v['type']=='fieldtype_parent_item_id' and (strlen($app_redirect_to)==0 or $current_entity_info['parent_id']==0 or $listing->report_type=='parent_item_info_page'))
	{
		continue;
	}

	if(!in_array($v['type'],fields_types::get_types_excluded_in_sorting()))
	{
		if(!isset($listing_order_clauses[$v['id']]))
		{
			$listing_order_clauses[$v['id']] = 'asc';
		}

		$listing_order_action = 'onClick="listing_order_by(\'' . $_POST['listing_container'] . '\',\'' . $v['id'] . '\',\'' . (($listing_order_clauses[$v['id']]=='asc' and in_array($v['id'],$listing_order_fields_id)) ? 'desc':'asc'). '\')"';
	}
	else
	{
		$listing_order_action = '';
	}

	$th_css_class = $v['type'] . '-th field-' . $v['id'] . '-th';

	if(in_array($v['id'],$listing_order_fields_id))
	{
		$listing_order_css_class = 'class="' . $th_css_class . ' listing_order listing_order_' . $listing_order_clauses[$v['id']] .'"';
	}
	else
	{
		$listing_order_css_class = 'class="' . $th_css_class . ' listing_order"';
	}

	if($v['type']=='fieldtype_dropdown_multilevel')
	{
		$html .= fieldtype_dropdown_multilevel::output_listing_heading($v,false,$listing);
	}
	else
	{
		$html .= '
	      <th ' . $listing_order_action . ' ' . $listing_order_css_class . ' data-field-id="' . $v['id'] . '" ' . $listing->get_listing_col_width($v['id']). '><div>' . fields_types::get_option($v['type'],'name',$v['name']) . '</div></th>
	  ';
	}

	$listing_fields[] = $v;

	$field_cfg = new fields_types_cfg($v['configuration']);

	if(in_array($v['type'],array('fieldtype_months_difference','fieldtype_years_difference','fieldtype_hours_difference','fieldtype_days_difference','fieldtype_mysql_query','fieldtype_input_numeric','fieldtype_formula','fieldtype_js_formula','fieldtype_input_numeric_comments')) and ($field_cfg->get('calclulate_totals')==1 or $field_cfg->get('calculate_average')==1))
	{
		$listing_numeric_fields[] = $v['id'];
	}
}

 
$html .= '
    </tr>
  </thead>
  <tbody>
';




while($item = db_fetch_array($items_query))
{
	$html .= '
      <tr ' . ($users_notifications->has($item['id']) ? 'class="unread-item-row"':''). '>
        ';

	//perpare selected checkbox
	$hide_actions_buttons = false;

	if($has_with_selected)
	{
		$checkbox_html = '<td>' . input_checkbox_tag('items_' . $item['id'],$item['id'],array('class'=>'items_checkbox','checked'=>in_array($item['id'],$app_selected_items[$_POST['reports_id']]))) . '</td>';
		 
		//check access to action with assigned only
		if(users::has_users_access_name_to_entity('action_with_assigned',$current_entity_id))
		{
			if(users::has_access_to_assigned_item($current_entity_id,$item['id']))
			{
				$html .= $checkbox_html;
			}
			else
			{
				$html .= '<td></td>';
					
				$hide_actions_buttons = true;
			}
		}
		else
		{
			$html .= $checkbox_html;
		}
	}
	//end prepare selected checkbox

	$path_info_in_report = array();

	if($reports_entities_id>0  and $current_entity_info['parent_id']>0)
	{
		$path_info_in_report = items::get_path_info($_POST['reports_entities_id'],$item['id'],$item);
	}


	foreach($listing_fields as $field)
	{

		//check field access
		if(isset($fields_access_schema[$field['id']]))
		{
			if($fields_access_schema[$field['id']]=='hide') continue;
		}


		if($field['type']=='fieldtype_parent_item_id' and (strlen($app_redirect_to)==0 or $current_entity_info['parent_id']==0  or $listing->report_type=='parent_item_info_page'))
		{
			continue;
		}

		//prepare field value
		$value = items::prepare_field_value_by_type($field, $item);

		$output_options = array(
				'class'       => $field['type'],
				'value'       => $value,
				'field'       => $field,
				'item'        => $item,
				'is_listing'  => true,
				'redirect_to' => $app_redirect_to,
				'reports_id'  => ($reports_entities_id>0 ? $_POST['reports_id']:0),
				'path'        => (isset($path_info_in_report['full_path']) ? $path_info_in_report['full_path']  :$current_path),
				'path_info'   => $path_info_in_report,
				'hide_actions_buttons' => $hide_actions_buttons,
		);


		if($field['is_heading']==1)
		{
			//get fields in popup
			$popup_html = '';
			if(strlen($_POST['force_popoup_fields']))
			{
				$fields_in_popup = fields::get_items_fields_data_by_id($item,$_POST['force_popoup_fields'],$current_entity_id,$fields_access_schema);

				if(count($fields_in_popup))
				{
					$popup_html = app_render_fields_popup_html($fields_in_popup, $reports_info);
				}
			}
			 
			$path = (isset($path_info_in_report['full_path']) ? $path_info_in_report['full_path']  :$current_path . '-' . $item['id']);

			$html .= '
          <td class="' . $field['type'] . '  field-' . $field['id'] . '-td item_heading_td' . (($entity_cfg['heading_width_based_content']==1 or $entity_cfg['change_col_width_in_listing']==1) ? ' width-auto':'') . '"><a ' . $popup_html . ' class="item_heading_link" href="' . url_for('items/info', 'path=' . $path . '&redirect_to=subentity&gotopage[' . $_POST['reports_id'] . ']=' . $_POST['page']) . '">' . fields_types::output($output_options) . '</a>
      ';

			if($entity_cfg['use_comments']==1 and $user_has_comments_access)
			{
				$html .= comments::get_last_comment_info($current_entity_id,$item['id'],$path, $fields_access_schema);
			}

			$html .= '</td>';
		}
		elseif($field['type']=='fieldtype_dropdown_multilevel')
		{
			$html .= fieldtype_dropdown_multilevel::output_listing($output_options);
		}
		else
		{
			$td_class = (in_array($field['type'],array('fieldtype_action','fieldtype_date_added','fieldtype_input_datetime')) ? 'class="' . $field['type'] . ' field-' . $field['id'] . '-td nowrap"':'class="' . $field['type'] . ' field-' . $field['id'] . '-td"');
			$html .= '
          <td ' . $td_class . '>' . fields_types::output($output_options) . '</td>
      ';
		}
	}
	 
	$html .= '
      </tr>
  ';
}

if($listing_split->number_of_rows==0)
{
	$html .= '
    <tr>
      <td colspan="100">' . TEXT_NO_RECORDS_FOUND . '</td>
    </tr>
  ';
}

$html .= '
  </tbody>';

if(count($listing_numeric_fields)>0)
{
	require(component_path('items/calculate_fields_totals'));
}

$html .= '
    </table>
		<div class="tableScrollRailX"></div>
  	<div class="tableScrollBarX"></div>
  </div>
</div>
';

//add pager
$html .= '
<div class="row">
  <div class="col-md-5 col-sm-12">' . $listing_split->display_count() . '</div>
  <div class="col-md-7 col-sm-12">' . $listing_split->display_links(). '</div>
</div>
';

echo $html;