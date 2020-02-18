<?php echo ajax_modal_template_header(TEXT_CONFIRM_ACTION) ?>

<?php echo form_tag('action_form', url_for('items/stages','action=update&path=' . $app_path  . '&field_id=' . $stages_field_info['id'] . '&value_id=' . $stages_field_value_id),array('class'=>'form-horizontal')) ?>    
<div class="modal-body">    
<?php

	$cfg = new fields_types_cfg($stages_field_info['configuration']);
	$entity_cfg = new entities_cfg($current_entity_id);
	
	$confirmation_value = ($cfg->get('use_global_list')>0 ? $app_global_choices_cache[$stages_field_value_id]['name'] : $app_choices_cache[$stages_field_value_id]['name']);
	$confirmation_text = $cfg->get('confirmation_text_for_choice_' . $stages_field_value_id); 
	
	if($cfg->get('add_comment')==1)
	{
?>


	  <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo $stages_field_info['name'] ?></label>
      <div class="col-md-9">	
    	  <p class="form-control-static"><b><?php echo (strlen($confirmation_text) ? $confirmation_text : $confirmation_value) ?></b></p>        
      </div>			
    </div>
    
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
	else
	{
		if(strlen($confirmation_text))
		{
			echo $confirmation_text;
		}
		else
		{
			echo '<h3>' . $stages_field_info['name'] . ': ' . $confirmation_value . '</h3>';
		}
	}
?>
</div>
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    
    $('#action_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
    }); 
    
  });
</script>    
