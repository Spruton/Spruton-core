
<?php echo ajax_modal_template_header(sprintf(TEXT_EXT_PROCESS_HEADING,$app_process_info['name'])) ?>

<?php echo form_tag('process', url_for('items/processes','action=run&id=' . $app_process_info['id'] . '&path=' . $app_path . '&redirect_to=' . $app_redirect_to),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>


<?php 

//force use selected itesm
	$count_selected = (isset($_GET['reports_id']) ? count($app_selected_items[$_GET['reports_id']]) :0);
	
	if($count_selected==0 and isset($_GET['reports_id']))
	{			
		echo '
	    <div class="modal-body">
	      <div>' . TEXT_PLEASE_SELECT_ITEMS . '</div>
	    </div>
	  ' . ajax_modal_template_footer('hide-save-button');
	}
	else
	{	
?>
    
<div class="modal-body">  
	<div class="form-body ajax-modal-width-790">  
<?php 
	
	echo input_hidden_tag('reports_id',(isset($_GET['reports_id']) ? $_GET['reports_id'] : 0));	
	
	if(isset($_GET['reports_id']))
	{
		$reports_info = db_find('app_reports',_get::int('reports_id'));
		
		$current_entity_id = $reports_info['entities_id'];
	}
		
//display default confirmation if not set
	if(isset($_GET['reports_id']) and !strlen(strip_tags($app_process_info['confirmation_text'])))
	{
		echo '<p>' . TEXT_ARE_YOU_SURE . '</p>';
	}
		

//display configramtion text
	if(strlen($app_process_info['confirmation_text']))
	{
		echo '<p>' . $app_process_info['confirmation_text'] . '</p>';
	}
	
	if($app_process_info['preview_prcess_actions'] and $app_user['group_id']==0)
	{
		$html = '<table class="table table-striped table-bordered table-hover ">';
		$actions_query = db_query("select pa.*, p.name as process_name from app_ext_processes_actions pa, app_ext_processes p where pa.process_id='" . $app_process_info['id'] . "' and  p.id=pa.process_id order by pa.sort_order");					
		while($actions = db_fetch_array($actions_query))
		{
			$action_entity_id = processes::get_entity_id_from_action_type($actions['type']);
			$entity_info = db_find('app_entities',$action_entity_id);
			
			
			if($action_entity_id==$current_entity_id and $current_item_id>0)
			{
				$html .= '
					<tr>
						<th colspan="2">' .  items::get_heading_field($current_entity_id,$current_item_id) . '</th>
					</tr>';
			}
			else
			{
				$html .= '
					<tr>
						<th colspan="2">' . (strstr($actions['type'],'insert') ? TEXT_INSERT : TEXT_UPDATE ) . ' "' . $entity_info['name'] . '"</th>
					</tr>';
			}
			
			$actions_fields_query = db_query("select af.id, af.fields_id, af.value, f.name from app_ext_processes_actions_fields af, app_fields f left join app_forms_tabs t on f.forms_tabs_id=t.id  where f.id=af.fields_id and af.actions_id='" . db_input($actions['id']) ."' order by t.sort_order, t.name, f.sort_order, f.name");								
			while($actions_fields = db_fetch_array($actions_fields_query))
			{
				$field = db_find('app_fields',$actions_fields['fields_id']);
											
				if(in_array($field['type'],array('fieldtype_input_date','fieldtype_input_datetime')))
				{
					$actions_fields['value'] = (strlen($actions_fields['value'])<5 ? strtotime($actions_fields['value'] . ' day') : $actions_fields['value']);					
				}
				
				
				$output_options = array('class'=>$field['type'],
						'value'=>$actions_fields['value'],
						'field'=>$field,
						'is_listing'=>true,
				);
				
				if(in_array($field['type'],array('fieldtype_input_numeric')) and strstr($actions_fields['value'],'['))
				{
					$html .= '
						<tr>
							<td width="35%" style="padding-left: 25px;">' . $actions_fields['name']  . ': </td>
							<td>' . $actions_fields['value'] . '</td>
						</tr>';
				}	
				elseif(in_array($field['type'],array('fieldtype_input_file','fieldtype_attachments','fieldtype_image')))
				{
					$html .= '
						<tr>
							<td width="35%" style="padding-left: 25px;">' . $actions_fields['name']  . ': </td>
							<td>' . $actions_fields['value'] . '</td>
						</tr>';
				}
				else
				{	
					$html .= '
						<tr>
							<td width="35%" style="padding-left: 25px;">' . $actions_fields['name']  . ': </td>
							<td>' . fields_types::output($output_options) . '</td>
						</tr>';
				}
			}						
		}
		
		$html .= '</table>';
		
		echo $html;
	}
	
	$entity_cfg = new entities_cfg($current_entity_id);
	
	$processes = new processes($current_entity_id);
	if($processes->has_move_action($app_process_info['id']) or $processes->has_copy_action($app_process_info['id']))
	{
		$item_info = db_query("select parent_item_id from app_entity_{$current_entity_id} where id='{$current_item_id}'");
		$item = db_fetch_array($item_info);
		
		$choices = [];
		$choices[$item['parent_item_id']] = items::get_heading_field($app_entities_cache[$current_entity_id]['parent_id'], $item['parent_item_id']);
		
		$html ='
				<div class="form-group">
          	<label class="col-md-3 control-label" for="parent_item_id">' . $app_entities_cache[$app_entities_cache[$app_process_info['entities_id']]['parent_id']]['name'] . '</label>
            <div class="col-md-9">
          	  ' . select_entities_tag('parent_item_id',$choices,$item['parent_item_id'],['entities_id'=>$app_entities_cache[$app_process_info['entities_id']]['parent_id'],'class'=>'form-control','data-placeholder'=>TEXT_ENTER_VALUE]) . '              
            </div>
          </div>';
		echo $html;
	}

//display comments form	
	if($app_process_info['allow_comments'] and $entity_cfg->get('use_comments')==1)
	{			
		
		$fields_access_schema = users::get_fields_access_schema($current_entity_id,$app_user['group_id']);
		
		//build default tab
		$html_default_tab = '';
		$fields_query = db_query("select f.* from app_fields f where f.type  in ('fieldtype_input_numeric_comments') and  f.entities_id='" . db_input($current_entity_id) . "' and f.comments_status=1 order by f.comments_sort_order, f.name");
		while($v = db_fetch_array($fields_query))
		{
			//check field access
			if(isset($fields_access_schema[$v['id']])) continue;
		
			//set off required option for comment form
			$v['is_required'] = 0;
		
			$html_default_tab .='
          <div class="form-group">
          	<label class="col-md-3 control-label" for="fields_' . $v['id']  . '">' . fields_types::get_option($v['type'],'name',$v['name']) . '</label>
            <div class="col-md-9">
          	  ' . fields_types::render($v['type'],$v,array('field_' . $v['id']=>''),array('parent_entity_item_id'=>$parent_entity_item_id,'form'=>'comment')) . '
              ' . tooltip_text($v['tooltip']) . '
            </div>
          </div>
        ';
		}
		
		echo $html_default_tab;
?>
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_COMMENT ?></label>
      <div class="col-md-9">	
    	  <?php echo textarea_tag('description','',array('class'=>'form-control autofocus ' . ($entity_cfg->get('use_editor_in_comments')==1 ? 'editor-auto-focus':''))) ?>        
      </div>			
    </div>

<?php if($entity_cfg->get('disable_attachments_in_comments')!=1): ?>    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_ATTACHMENTS ?></label>
      <div class="col-md-9">	
    	  <?php echo fields_types::render('fieldtype_attachments',array('id'=>'attachments'),array('field_attachments'=>'')) ?>
        <?php echo input_hidden_tag('comments_attachments','',array('class'=>'form-control required_group')) ?>        
      </div>			
    </div>
<?php endif ?> 

<?php 
	} 
	
//handle manually entered fields

	//get fiels where enter manually is "Yes and use value"
	$enter_manually_use_value = [];
	$fields_query = "select af.fields_id, af.value from app_ext_processes_actions_fields af,app_ext_processes_actions pa where af.actions_id=pa.id and af.enter_manually=2 and af.actions_id in (select pa2.id from app_ext_processes_actions pa2 where pa2.process_id='" . $app_process_info['id'] . "') order by pa.sort_order";
	$fields_query = db_query($fields_query);
	while($fields = db_fetch_array($fields_query))
	{
		$enter_manually_use_value[$fields['fields_id']] = $fields['value']; 
	}
	
	
	$html = '';
	$section_name = '';
	$count_fields = 0;
	$entities_in_process = [];
	$fields_query = "select af.fields_id from app_ext_processes_actions_fields af,app_ext_processes_actions pa where af.actions_id=pa.id and af.enter_manually in (1,2) and af.actions_id in (select pa2.id from app_ext_processes_actions pa2 where pa2.process_id='" . $app_process_info['id'] . "') order by pa.sort_order";
	$fields_query = "select f.* from app_fields f left join app_forms_tabs t on f.forms_tabs_id=t.id  where f.id in ({$fields_query}) order by f.entities_id, t.sort_order, t.name, f.sort_order, f.name";
	$fields_query = db_query($fields_query);
	while($fields = db_fetch_array($fields_query))
	{								
		$v = $fields; 
		$obj = db_show_columns('app_entity_' . $v['entities_id']);
		$entity_info = db_find('app_entities',$v['entities_id']);
		
		$entities_in_process[$v['entities_id']] = $v['entities_id'];
		
		if($section_name!=$entity_info['name'])
		{
			$section_name = $entity_info['name'];
			$html .= '<h3  class="form-section" style="margin-top:5px;">' . $section_name . '</h3>';
		}
		
		//prepare parent_entity_item_id that will be using for entity field type
		$parent_entity_id = (isset($parent_entity_id) ? $parent_entity_id :0);
		$use_parent_entity_item_id = 0;
		
		//use parent item id if parent entity the same
		if($parent_entity_id==$entity_info['parent_id'])
		{			
			$use_parent_entity_item_id = $parent_entity_item_id;
		}
		
		//use curent item id as parent 
		if($current_entity_id==$entity_info['parent_id'])
		{
			$use_parent_entity_item_id = $current_item_id;
		}
		
		//check fields access
		$fields_access_schema = users::get_fields_access_schema($entity_info['id'],$app_user['group_id']);
		
		//use curent item obj
		if($current_entity_id==$entity_info['id'])
		{			
			$obj = db_find('app_entity_' . $v['entities_id'],$current_item_id);
								
			//check fields access rules for item
			$item_info = db_find('app_entity_' . $current_entity_id,$current_item_id);
			$access_rules = new access_rules($current_entity_id, $obj);
			$fields_access_schema += $access_rules->get_fields_view_only_access();
		}
		
		//skip fields if no edit access
		if(isset($fields_access_schema[$v['id']]) and $app_process_info['apply_fields_access_rules']==1) continue;
		
		//handle enter manually with value
		if(isset($enter_manually_use_value[$fields['id']]))
		{
			$actions_fields_value = $enter_manually_use_value[$fields['id']];
			switch($fields['type'])
			{
				case 'fieldtype_input_date':
					$obj['field_' . $fields['id']] = ($actions_fields_value==' ' ? 0 : (strlen($actions_fields_value)<5 ? get_date_timestamp(date('Y-m-d',strtotime($actions_fields_value . ' day'))) : $actions_fields_value));
					break;
				case 'fieldtype_input_datetime':
					$obj['field_' . $fields['id']] = ($actions_fields_value==' ' ? 0 : (strlen($actions_fields_value)<5 ? strtotime($actions_fields_value . ' day') : $actions_fields_value));
					break;
				default:
					$obj['field_' . $fields['id']] = $actions_fields_value;
					break;
			}
		}
		
		
		$html .='
	          <div class="form-group form-group-' . $v['id'] . ' form-group-' . $v['type'] . '">
	          	<label class="col-md-3 control-label" for="fields_' . $v['id']  . '">' .
			          	($v['is_required']==1 ? '<span class="required-label">*</span>':'') .
			          	($v['tooltip_display_as']=='icon' ? tooltip_icon($v['tooltip']) :'') .
			          	fields_types::get_option($v['type'],'name',$v['name']) .
			          	'</label>
	            <div class="col-md-9">
	          	  <div id="fields_' . $v['id'] . '_rendered_value">' . fields_types::render($v['type'],$v,$obj,array('parent_entity_item_id'=>$use_parent_entity_item_id, 'form'=>'item', 'is_new_item'=>($current_entity_id==$v['entities_id'] ? false:true) )) . '</div>
	              ' . ($v['tooltip_display_as']!='icon' ? tooltip_text($v['tooltip']):'') . '
	            </div>
	          </div>
	        ';
		
		$count_fields++;
	}
	
	if($count_fields)
	{
		echo $html;
		
		//smart input
		$smart_input = new smart_input($current_entity_id);
		echo $smart_input->render();
	
		//include fields displays rueles	
		if($current_item_id>0 and $app_process_info['apply_fields_display_rules']==1)
		{
			$item_info = db_find('app_entity_' . $current_entity_id,$current_item_id);
			$app_items_form_name = 'process';
			require(component_path('items/forms_fields_rules.js'));
		}
	}
		
?> 
	</div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_CONTINUE) ?>

<?php 
	}
?>

</form>    

<script>
  $(function() { 
  //add method to not accept space  	
  	jQuery.validator.addMethod("noSpace", function(value, element) { 
      return value == '' || value.trim().length != 0;  
    }, '<?php echo addslashes(TEXT_ERROR_REQUIRED) ?>');
    
  	$('#process').validate({ignore:'.ignore-validation',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			},

		//custom error messages
      messages: {			    
        <?php 
	        foreach($entities_in_process as $entities_id)
	        {
	        	echo fields::render_required_messages($entities_id);
	        }
       	?>			   
			},

			
			 //custom erro placment to handle radio etc. 
      errorPlacement: function(error, element) {
        if (element.attr("type") == "radio") 
        {
           error.insertAfter(".radio-list-"+element.attr("data-raido-list"));
        } 
        else 
        {
           error.insertAfter(element);
        }                
      }
    }); 

  //curecny convert
  	app_currency_converter('#process')
                                                                           
  });    
</script> 