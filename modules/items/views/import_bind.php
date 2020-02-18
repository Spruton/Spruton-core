
<?php echo ajax_modal_template_header(TEXT_HEADING_BIND_FIELD) ?>

<?php echo form_tag('bind_field_form', url_for('tools/import_data','action=bind_filed&multilevel_import=' . _get::int('multilevel_import')),array('onSubmit'=>'return bind_field(' . $_GET['col'] . ')')) . input_hidden_tag('col',$_GET['col']); ?>

<div class="modal-body">
  
<?php

	$multilevel_import = _get::int('multilevel_import');
	 
	$entities_list = [];
	$entities_list[$current_entity_id] = entities::get_name_by_id($current_entity_id);
	
	if($multilevel_import>0)
	{		
		foreach(entities::get_tree($current_entity_id) as $entity)
		{
			$entities_list[$entity['id']] = $entity['name'];
	
			if($entity['id'] == $multilevel_import) break;
		}
	}
	
	//print_rr($entities_list);
	
	$choices = [];
	$choices[] = TEXT_NONE;
	
	foreach($entities_list as $entity_id=>$entity_name)
	{
	  $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in (" . fields_types::skip_import_field_types() . ") and f.entities_id='" . $entity_id . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");		  	  
	  $fields_access_schema = users::get_fields_access_schema($current_entity_id,$app_user['group_id']);
	  
	  while($v = db_fetch_array($fields_query))
	  {
	    if(in_array($v['id'],$import_fields)) continue;
	    
	    if(isset($fields_access_schema[$v['id']])) continue;
	    
	    //echo '<div><label>' . input_radiobox_tag('filed_id',$v['id']) . ' ' . fields_types::get_option($v['type'],'name',$v['name']) . '</label></div>';
	    
	    $choices[$entity_name][$v['id']] =  ($v['is_heading']==1 ? '* ':'') . fields_types::get_option($v['type'],'name',$v['name']) . ($v['is_heading']==1 ? ' (' . TEXT_HEADING . ')':''); 
	  }
	}
	
	echo select_tag('filed_id',$choices,'',['class'=>'form-control chosen-select']);
?>  
        
</div> 
             
<div class="modal-footer">
  <button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_BIND ?></button>
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo TEXT_BUTTON_CLOSE ?></button>    
</div>
    
<script>
  jQuery(document).ready(function() {                  
     appHandleUniform()                     
  });
</script>

</form> 