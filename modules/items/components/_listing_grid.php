<?php

$listing_type = $listing->get_listing_type_info('grid');

$html .= '<div class="hidden">' . input_checkbox_tag('select_all_items',_post::int('reports_id'),array('class'=>'select_all_items')) . '</div>';

$html .= '
		
		<style>
		ul.listing-grid{
			grid-template-columns: repeat(auto-fit, minmax(' . (strlen($listing_type['width']) ? $listing_type['width'] : '200').  'px, 1fr));
			margin-top: 10px;
		}
		</style>										
		
		<ul class="listing-grid">';
while($item = db_fetch_array($items_query))
{
	$html .= '
			<li>
				<table style="width: 100%" ' . (($users_notifications->has($item['id']) and $entity_cfg->get('disable_highlight_unread')!=1) ? 'class="unread-item-row"':''). '>
			';
	
	
	//perpare selected checkbox
	$hide_actions_buttons = false;
	
	if($has_with_selected)
	{
		$checkbox_html = '<div class="listing-section-checkbox">' . input_checkbox_tag('items_' . $item['id'],$item['id'],array('class'=>'items_checkbox','checked'=>in_array($item['id'],$app_selected_items[$_POST['reports_id']]))) . '</div>';
			
		//check access to action with assigned only
		if(users::has_users_access_name_to_entity('action_with_assigned',$current_entity_id))
		{
			if(!users::has_access_to_assigned_item($current_entity_id,$item['id']))
			{
				$checkbox_html = '';
					
				$hide_actions_buttons = true;
			}
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
		$html .= '
				<tr>
					<td class="listing-section-align-' . $section['align'] . '">';
		
		if(strlen($section['name']))
		{
			$html .= '
					<div class="listing-section-heading">' . $section['name'] . '</div>
					';
		}
	
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
					
				if($entity_cfg->get('use_comments')==1 and $user_has_comments_access and $entity_cfg->get('display_last_comment_in_listing',1))
				{
					$value .= comments::get_last_comment_info($current_entity_id,$item['id'],$path, $fields_access_schema);
				}
					
				$value .= '</td>';
			}
			else
			{
				$value = fields_types::output($output_options);
			}
			
			if($field['type']=='fieldtype_action' and !$hide_actions_buttons)
			{
				$value .= $checkbox_html;
			}
				
			$section_fields[] = [
					'name' => fields_types::get_option($field['type'],'name',$field['name']),
					'short_name' => $field['short_name'],
					'long_name' => $field['long_name'],
					'value' => $value,
			];
				
		}
	
	
		if($section['display_as']=='list')
		{
			$html .= '<table class="listing-section-table" style="width: 100%">';
				
			foreach($section_fields as $field)
			{
				$html .= '
						<tr>
							' . ($section['display_field_names'] ? '<th ' . (strlen($field['short_name']) ? 'title="' . htmlspecialchars($field['long_name']) . '"':'' ) . '>' . $field['name'] . ': </th>' : '') . '
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
	
	
	
		$html .= '				
					</td>
				</tr>';
	}
	
	$html .= '					
				</table>
			</li>';  
}

if($listing_split->number_of_rows==0)
{
	$html .= '
    <li>' . TEXT_NO_RECORDS_FOUND . '</li>
  ';
}

$html .= '</ul>';

//add pager
$html .= '
<div class="row">
  <div class="col-md-4 col-sm-12">' . $listing_split->display_count() . '</div>
  <div class="col-md-8 col-sm-12">' . $listing_split->display_links(). '</div>
</div>
';

echo $html;
