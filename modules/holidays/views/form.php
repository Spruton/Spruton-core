<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('holidays_form', url_for('holidays/holidays','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
	  <div class="form-group">
	  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
	    <div class="col-md-9">	
	  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required')) ?>
	    </div>			
	  </div>
     
   <div class="form-group">
    	<label class="col-md-3 control-label" for="start_date"><?php echo TEXT_START_DATE ?></label>
      <div class="col-md-9">    
        <div class="input-group input-medium date datepicker"> 
          <?php echo input_tag('start_date',$obj['start_date'],array('class'=>'form-control required')) ?> 
          <span class="input-group-btn">
            <button class="btn btn-default date-set" type="button"><i class="fa fa-calendar"></i></button>
          </span>
        </div>        
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="end_date"><?php echo TEXT_END_DATE ?></label>
      <div class="col-md-9">    
        <div class="input-group input-medium date datepicker"> 
          <?php echo input_tag('end_date',$obj['end_date'],array('class'=>'form-control required')) ?> 
          <span class="input-group-btn">
            <button class="btn btn-default date-set" type="button"><i class="fa fa-calendar"></i></button>
          </span>
        </div>        
      </div>			
    </div> 
  
    
  </div>
</div>
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>

  $(function() { 
        
    $('#holidays_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    }); 
                                                                                    
  });
  
</script>  