<?php echo ajax_modal_template_header(TEXT_EXT_SEND_SMS) ?>

<?php 
	$phone = '';	
	$field_id = _get::int('field_id');
	
	$item_info = db_find('app_entity_' . $current_entity_id, _get::int('item_id'));
	
	if(isset($item_info['field_' . $field_id]))
	{
		$phone = db_prepare_input($item_info['field_' . $field_id]);
	}
	 
?>

<?php echo form_tag('sms_form', url_for('items/send_sms','action=send&path=' . $app_path),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<?php echo input_hidden_tag('phone',$phone) ?>
<?php echo input_hidden_tag('module_id',_get::int('module_id')) ?>
<div class="modal-body">
  <div class="form-body" id="sms_form_body">
  
  <div class="form-group">
  	<label class="col-md-4 control-label" for="name"><?php echo  TEXT_PHONE ?></label>
    <div class="col-md-8">	
  	  <p class="form-control-static"><?php echo $phone ?></p>      
    </div>			
  </div>
       
  <div class="form-group">
  	<label class="col-md-4 control-label" for="name"><?php echo  TEXT_EXT_MESSAGE_TEXT ?></label>
    <div class="col-md-8">	
  	  <?php echo textarea_tag('message_text','',array('class'=>'form-control input-large textarea-small required')) ?>      
    </div>			
  </div>
  
   </div>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_SEND) ?>

</form>   

<script>
	$(function() { 
	  $('#sms_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				
				$('#sms_form_body').load($('#sms_form').attr('action'),$('#sms_form').serializeArray())
			}
	  });                                          
	});        
</script> 