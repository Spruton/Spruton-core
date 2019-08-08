
<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('users_alerts_form', url_for('dashboard_configure/sections','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('type',$obj['type'])?>
<div class="modal-body">
  <div class="form-body">
   

    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="title"><?php echo TEXT_NAME ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required')) ?>
  	  <?php echo tooltip_text(TEXT_DASHBOARD_BLOCK_SECTION_INFO)?>
    </div>			
  </div>
   
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_GRID ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('grid',dashboard_pages::get_section_grid_choices(), $obj['grid'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div> 
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-xsmall')) ?>
    </div>			
  </div> 

      
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#users_alerts_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                           
  }); 

</script>   
    
 
