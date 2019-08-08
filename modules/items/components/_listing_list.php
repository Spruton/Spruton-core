<?php


$listing_type = $listing->get_listing_type_info('list');

$html .= '
<div class="table-scrollable">
  <div class="table-scrollable table-wrapper slimScroll" id="slimScroll">
    <table class="table table-striped table-bordered table-hover" data-fixed-head="1">
      <thead>
        <tr>
          ' . ($has_with_selected ?  '<th class="multiple-select-action-th">' . input_checkbox_tag('select_all_items',$_POST['reports_id'],array('class'=>'select_all_items')) . '</th>' : '');

//render listing heading
foreach($listing_type['sections'] as $section)
{		
	$listing_order_css_class = '';
	$listing_order_action = '';
	
	if(count($section['fields'])==1)
	{	
		$v = $section['fields'][0];		
		
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
			$listing_order_css_class = 'class="listing_order listing_order_' . $listing_order_clauses[$v['id']] .'"';
		}
		else
		{
			$listing_order_css_class = 'class="listing_order"';
		}
	}
	
	$html .= '
      <th ' . $listing_order_action . ' ' . $listing_order_css_class . (strlen($section['width']) ? ' style="width: ' . $section['width'] . '"':'') . '><div>' . $section['name'] . '</div></th>
  ';
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
	
	foreach($listing_type['sections'] as $section)
	{
		$html .= '<td class="listing-section-align-' . $section['align'] . '" ' . (strlen($section['width']) ? ' style="white-space:normal"':''). '>';
		
		$section_fields = [];
		
		foreach($section['fields'] as $field)
		{
			//check field access
			if(isset($fields_access_schema[$field['id']]))
			{
				if($fields_access_schema[$field['id']]=='hide') continue;
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
						$popup_html = app_render_fields_popup_html($fields_in_popup);
					}
				}
			
				$path = (isset($path_info_in_report['full_path']) ? $path_info_in_report['full_path']  :$current_path . '-' . $item['id']);
			
				$value = '<a ' . $popup_html . ' class="item_heading_link" href="' . url_for('items/info', 'path=' . $path . '&redirect_to=subentity&gotopage[' . $_POST['reports_id'] . ']=' . $_POST['page']) . '">' . fields_types::output($output_options) . '</a>';	
			
				if($entity_cfg['use_comments']==1 and $user_has_comments_access)
				{
					$html .= comments::get_last_comment_info($current_entity_id,$item['id'],$path, $fields_access_schema);
				}
			
				$value .= '</td>';
			}
			else
			{								
				$value = fields_types::output($output_options);
			}
			
			$section_fields[] = [
					'name' => fields_types::get_option($field['type'],'name',$field['name']), 
					'value' => $value,
			];
			
		}
		
				
		if($section['display_as']=='list')
		{
			$html .= '<table class="listing-section-table">';
			
			foreach($section_fields as $field)
			{
				$html .= '
						<tr>
							' . ($section['display_field_names'] ? '<th>' . $field['name'] . ': </th>' : '') . '
							<td class="' . ($section['display_field_names'] ? 'with_th' : '') . '">' . $field['value'] . '</td>
						</tr>
						';
			}
			
			$html .= '</table>';
		}
		else
		{
			$html .= '<ul class="list-inline">';
			
			foreach($section_fields as $field)
			{
				$html .= '
						<li>
							' . ($section['display_field_names'] ? $field['name'] . ': ' : '') . $field['value'] . '
						</li>
						';
			}
			
			$html .= '</ul>';
		}
		
		
						
		$html .= '</td>';
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
		</tbody>
    </table>
  </div>
</div>
';


//add pager
$html .= '
<div class="row">
  <div class="col-md-4 col-sm-12">' . $listing_split->display_count() . '</div>
  <div class="col-md-8 col-sm-12">' . $listing_split->display_links(). '</div>
</div>
';

//add horizontal scroll bar
$html .= '
  <div class="tableScrollRailX"></div>
  <div class="tableScrollBarX"></div>
';

echo $html;


