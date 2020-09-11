<?php

$fields_query = db_query("select * from app_fields where id='" . _get::int('fields_id') . "'");
if($fields = db_fetch_array($fields_query))
{	
	switch($fields['type'])
	{
		case 'fieldtype_entity_multilevel':
		case 'fieldtype_entity_ajax':
		case 'fieldtype_entity':
			$cfg = new fields_types_cfg($fields['configuration']);
			
			$item_id = _get::int('item_id');
			$parent_entity_item_id = _get::int('parent_entity_item_id');
			
				
			$obj = array();
					
			if(in_array($cfg->get('display_as'),['dropdown_muliple','dropdown_multiple']) and $_GET['current_field_values']!='null')
			{
				$obj['field_' . $fields['id']] = $_GET['current_field_values'] . ',' . _get::int('item_id');
			}
			else
			{
				$obj['field_' . $fields['id']] = _get::int('item_id');
			}
											
			echo fields_types::render($fields['type'],$fields,$obj,array('parent_entity_item_id'=>$parent_entity_item_id, 'form'=>'item'));
			
			echo '
			    <script>
			        appHandleChosen(); 
			        app_handle_submodal_open_btn();			        
			    </script>';
			
			switch($fields['type'])
			{
			    case 'fieldtype_entity_ajax':
			        echo '
			            <script>
			                $("#fields_' . $fields['id'] . '_select2_on").load("' . url_for('dashboard/select2_json','action=copy_values&form_type=items/render_field_value&entity_id=' . $cfg->get('entity_id') . '&field_id=' . $fields['id']) . '",{item_id:' . _get::int('item_id') . '})
			            </script>
			            ';
			        break;
			}
		break;
	}
}

exit();