
<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('entities_form', url_for('entities/listing_types','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') . '&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
<?php 
	if($obj['type']=='table')
	{
		echo input_hidden_tag('is_active',1);
	}
	else 
	{
	?>  
  	<div class="form-group" id="is-heading-container">
	   	<label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
	    <div class="col-md-9">	
	  	  <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('is_active','1',array('checked'=>$obj['is_active'])) ?></label></div>        
	    </div>			
	  </div>
<?php } ?>
	  
<?php if($obj['type']!='mobile'): ?>	  
	  <div class="form-group" id="is-heading-container">
	   	<label class="col-md-3 control-label" for="is_default"><?php echo TEXT_IS_DEFAULT ?></label>
	    <div class="col-md-9">	
	  	  <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('is_default','1',array('checked'=>$obj['is_default'])) ?></label></div>        
	    </div>			
	  </div>  
<?php endif ?>
	  
<?php if($obj['type']=='grid'): ?>

	<div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_WIDHT ?> (px)</label>
    <div class="col-md-9">	
  	  <?php echo input_tag('width',$obj['width'],array('class'=>'form-control input-small')) ?>
  	  <?php echo tooltip_text(TEXT_GRID_WIDHT_INFO) ?>
    </div>			
  </div>
  
<?php endif ?>	  
    
   </div>
</div>

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#entities_form').validate({ ignore: '',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                                                  
  });
  
</script>   
    
 
