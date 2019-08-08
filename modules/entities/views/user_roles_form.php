
<?php echo ajax_modal_template_header(TEXT_HEADING_VALUE_IFNO) ?>

<?php echo form_tag('fields_form', url_for('entities/user_roles','action=save&fields_id=' . _get::int('fields_id') . '&entities_id=' . _get::int('entities_id') . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<div class="modal-body">
  <div class="form-body">
       
	  <div class="form-group">
	  	<label class="col-md-4 control-label" for="name"><?php echo tooltip_icon(TEXT_CHOICES_NAME_INFO) . TEXT_NAME ?></label>
	    <div class="col-md-8">	
	  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required autofocus')) ?>      
	    </div>			
	  </div>
	  
	  <div class="form-group">
	  	<label class="col-md-4 control-label" for="sort_order"><?php echo tooltip_icon(TEXT_CHOICES_SORT_ORDER_INFO) . TEXT_SORT_ORDER ?></label>
	    <div class="col-md-8">	
	  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-small')) ?>      
	    </div>			
	  </div>
      
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
	$(function() { 
	  $('#fields_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
	  });                                           
	});        
</script>   