<?php echo ajax_modal_template_header(TEXT_IMPORT) ?>

<?php $import_fields = array(); ?>

<?php echo form_tag('import_data', url_for('items/import_preview','path=' . $app_path),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>

<div class="modal-body">
  <div class="form-body">
  
<p><?php echo TEXT_IMPORT_DATA_INFO ?></p>

<div class="alert alert-info"><?php echo TEXT_IMPORT_DATA_TOOLTIP ?></div>
    
<?php 
	$choices = array(
			'import' => TEXT_ACTION_IMPORT_DATA,
			'update' => TEXT_ACTION_UPDATE_DATA,
			'update_import' => TEXT_ACTION_UPDATE_AND_IMPORT_DATA,
	);
	
?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_ACTION ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('import_action',$choices,'',array('class'=>'form-control input-large required')) ?>
    </div>			
  </div>
        
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_FILENAME ?></label>
    <div class="col-md-9">	
  	  <?php echo input_file_tag('filename',array('class'=>'form-control required input-xlarge', 'accept'=>fieldtype_attachments::get_accept_types_by_extensions('xls,xlsx'))) ?>
      <span class="help-block">*.xls, *.xlsx</span>      
    </div>			
  </div>  
  
<?php 
	if(entities::has_subentities($current_entity_id)) {
		
  $choices = [];
  $choices[] = '';
  foreach(entities::get_tree($current_entity_id) as $entity)
  {
  	$choices[$entity['id']] = str_repeat(' - ', ($entity['level']+1)) . $entity['name'];
  }
?>  

	<div class="form-group">
  	<label class="col-md-3 control-label" for="entities_id"><?php echo tooltip_icon(TEXT_MULTI_LEVEL_IMPORT_INFO) . TEXT_MULTI_LEVEL_IMPORT ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('multilevel_import',$choices,'',array('class'=>'form-control input-xlarge')) ?>
    </div>			
  </div>
  
<?php 
	}
	if(is_ext_installed())
	{
		$choices = import_templates::get_choices($current_entity_id);
		
		if(count($choices)>1)
		{
?>  

	<div class="form-group">
  	<label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_EXT_TEMPLATE ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('import_template',$choices,'',array('class'=>'form-control input-large')) ?>
    </div>			
  </div>

<?php
		}
	} 
?>
   
 </div>
</div>

<?php echo ajax_modal_template_footer(TEXT_BUTTON_CONTINUE) ?>

</form> 

<script>
  $(function() { 
    $('#import_data').validate({
	    	submitHandler: function(form){
					app_prepare_modal_action_loading(form)
					return true;
				}
      }); 
                                                                    
  });
  
</script> 

