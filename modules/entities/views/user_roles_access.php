
<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo $field_info['name'] . ' <i class="fa fa-angle-right"></i> <a href="' . url_for('entities/user_roles','entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id')) . '">'.  TEXT_USER_ROLES  . '</a> <i class="fa fa-angle-right"></i> ' . $user_roles_info['name'] .' <i class="fa fa-angle-right"></i> ' . TEXT_NAV_ENTITY_ACCESS ?></h3>

<p><?php echo TEXT_USER_ROLES_ACCESS_INFO ?></p>

<?php echo form_tag('pivot_access_form', url_for('entities/user_roles_access','action=save&role_id=' . _get::int('role_id') . '&entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id'))) ?>
<div class="table-scrollable" style="overflow-x:visible;overflow-y:visible; ">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>    
    <th></th>
    <th width="100%"><?php echo TEXT_ENTITY ?></th>
    <th><?php echo TEXT_VIEW_ACCESS ?></th>    
    <th><?php echo TEXT_ACCESS ?></th>           
  </tr>
</thead>
<tbody>
<?php 
foreach(entities::get_tree(_get::int('entities_id')) as $v): 

	$checked = false;

	$access_schema = array();
	$comments_schema = '';
	
	$acess_info_query = db_query("select access_schema, comments_access from app_user_roles_access where entities_id='" . $v['id'] . "' and user_roles_id='" . db_input(_get::int('role_id')) . "' and fields_id='" . _get::int('fields_id'). "'");
	if($acess_info = db_fetch_array($acess_info_query))
	{
		$checked = true;
		
		$access_schema = explode(',',$acess_info['access_schema']);
		
		$comments_schema = str_replace(',','_',$acess_info['comments_access']);		
	}
	
	
	$entity_cfg = new entities_cfg($v['id']);

?>
<tr>  
  <td>
  <?php 
  $params = array('class'=>'access-schema-settings access-schema-settings-flag');
  
  if($checked) $params['checked'] = 'checked';
  
  echo input_checkbox_tag('entities[' . $v['id'] . ']',$v['id'],$params)?></td>
  <td style="white-space: nowrap">
  	<?php echo  '<label for="entities_' . $v['id'] . '">' . str_repeat('&nbsp;<i class="fa fa-minus" aria-hidden="true"></i>&nbsp;', $v['level']) . ' ' . $v['name'] . '</label>' ?>  	
  </td>  
  <td>
	  <div class="access-configuration-block<?php echo $v['id']?> hidden">
		  <?php 
		  	echo select_tag('access[' . $v['id']. '][]',access_groups::get_access_view_choices(),access_groups::get_access_view_value($access_schema),array('id'=>'access_' . $v['id'],'class'=>'form-control input-large access-schema-settings','data-entity-id'=>$v['id'],'onChange'=>'check_access_schema(this.value,' . $v['id'] . ')')); 
		  	
		  	echo '<div style="padding-top: 5px; text-align: right;">' . button_tag(TEXT_NAV_FIELDS_ACCESS,url_for('entities/user_roles_fields_access','role_id=' . _get::int('role_id') .'&role_entities_id=' . $v['id'] . '&entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id')),true,array('class'=>'btn btn-default btn-sm')) . '</div>';
		  ?>
		</div>
  </td>
  <td>
	  <div class="access-configuration-block<?php echo $v['id']?> hidden">
		  <?php   
		  	$choices = access_groups::get_access_choices();
		  	unset($choices['reports']);
		  	
			  echo select_tag('access[' . $v['id']. '][]',$choices,$access_schema,array('id'=>'access_shcema_' . $v['id'],'class'=>'form-control input-xlarge chosen-select access-schema-settings','data-entity-id'=>$v['id'],'multiple'=>'multiple'));
			  
			  if($entity_cfg->get('use_comments'))
			  {
			  	echo '<div style="padding-top: 5px; "><ul class="list-inline"><li>' . TEXT_COMMENTS . ':</li><li>' . select_tag('comments_access[' . $v['id']. ']',comments::get_access_choices(),$comments_schema,array('class'=>'form-control input-medium access-schema-settings','data-entity-id'=>$v['id'])) . '</li><ul></div>';
				}
		  ?>
	  </div>
  </td>
    
</tr>  
<?php endforeach ?>
</tbody>
</table>
</div>
</form>

<?php echo '<a class="btn btn-default" href="' . url_for('entities/user_roles','entities_id=' . _get::int('entities_id') . '&fields_id=' . _get::int('fields_id')) . '">' . TEXT_BUTTON_BACK . '</a>'?>

<script>
function check_access_schema(access,entity_id)
{
	if(access=='')
	{
		$('#access_shcema_'+entity_id).val('');
		$('#access_shcema_'+entity_id).trigger("chosen:updated");

		$('#comments_access_'+entity_id).val('');
		
	}
}

function check_access_schema_flag()
{
	$('.access-schema-settings-flag').each(function(){
		entity_id = $(this).val();		
		if($(this).is(':checked'))
		{			
			$('.access-configuration-block'+entity_id).removeClass('hidden')
		}
		else
		{
			$('.access-configuration-block'+entity_id).addClass('hidden')
		}
	})
}

$(function(){

	$('.access-schema-settings').change(function(){
		check_access_schema_flag();
		form  = $('#pivot_access_form');			
		$.ajax({type: "POST",url: form.attr('action'),data: form.serializeArray()});
	})	

	check_access_schema_flag();

})
</script> 
