<?php 
	$entity_info = db_find('app_entities',_get::int('role_entities_id'));
	
	$user_roles_info = db_find('app_user_roles',_get::int('role_id'));
	
	echo ajax_modal_template_header($user_roles_info['name'] . ' / ' . $entity_info['name']);
?>

<?php echo form_tag('cfg', url_for('entities/user_roles_access','action=set_fields_access&role_id=' . _get::int('role_id') . '&role_entities_id=' . _get::int('role_entities_id') . '&entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id'))) ?>

<div class="modal-body">
  <div class="form-body ajax-modal-width-790">
  	
<?php 
	$access_choices_default = array('yes'=>TEXT_YES,'view'=>TEXT_VIEW_ONLY,'hide'=>TEXT_HIDE);
	$access_choices_internal = array('yes'=>TEXT_YES,'hide'=>TEXT_HIDE);

	$fields_list = array();
	$fields_query = db_query("select f.*, t.name as tab_name,if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_created_by'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_action','fieldtype_parent_item_id') and f.entities_id='" . db_input(_get::int('role_entities_id')) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
	while($v = db_fetch_array($fields_query))
	{
		$fields_list[$v['id']] = array(
				'name' => fields_types::get_option($v['type'],'name',$v['name']),
				'type' => $v['type']
		);
	}
	
	$html = '
      <div class="table-scrollable">
      <table class="table table-striped table-bordered table-hover">
        <tr>
          <th>' . TEXT_FIELDS . '</th>
          <th>' . TEXT_ACCESS . ': ' . select_tag('access_0',array_merge(array(''=>''),$access_choices_default),'',array('class'=>'form-control input-medium ','onChange'=>'set_access_to_all_fields(this.value,0)')) . '</th>
        </tr>
      ';
	
	$access_schema = [];
	$access_schema_info_query = db_query("select * from app_user_roles_access where user_roles_id='" . _get::int('role_id') . "' and entities_id='" . _get::int('role_entities_id') . "' and fields_id='" . _get::int('fields_id') . "'");
	if($access_schema_info = db_fetch_array($access_schema_info_query))
	{
		if(strlen($access_schema_info['fields_access']))
			$access_schema = json_decode($access_schema_info['fields_access'],true);
	}
			
	
	foreach($fields_list as $id=>$field)
	{
		$value = (isset($access_schema[$id]) ? $access_schema[$id] : 'yes');
	
		$access_choices = (in_array($field['type'],array('fieldtype_id','fieldtype_date_added','fieldtype_created_by')) ? $access_choices_internal : $access_choices_default);
	
		$html .= '
        <tr>
          <td>' . $field['name'] . '</td>
          <td>' . select_tag('access[' . $id . ']',$access_choices, $value,array('class'=>'form-control input-medium access_group_0')). '</td>
        </tr>
      ';
	}
	
	$html .= '</table></div>';
	
	echo $html;		
?>
	</div>
</div>
	
	
<?php echo ajax_modal_template_footer() ?>

</form>	