
<?php echo ajax_modal_template_header('JavaScript') ?>

<?php $cfg = new entities_cfg($_GET['entities_id']); ?>

<?php echo form_tag('fields_form', url_for('entities/forms','action=save_javascript&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<div class="modal-body ajax-modal-width-790">
  <div class="form-body">
    
<ul class="nav nav-tabs">
  <li class="active"><a href="#javascript_in_form"  data-toggle="tab"><?php echo TEXT_JAVASCRIPT_IN_FORM ?></a></li>
  <li><a href="#onSubmit" data-toggle="tab" id="onSubmitTab"><?php echo 'onSubmit' ?></a></li>  
</ul>

<div class="tab-content">
  
  <div class="tab-pane fade active in" id="javascript_in_form">
  	<p><?php echo TEXT_JAVASCRIPT_IN_FORM_INFO?></p>         
	  <div class="form-group">	  	
	    <div class="col-md-12">	
	  	 <?php echo textarea_tag('javascript_in_from',$cfg->get('javascript_in_from'),array('class'=>'form-control')) ?>      
	    </div>			
	  </div>	  
  </div>
   
  <div class="tab-pane fade " id="onSubmit">
  	<p><?php echo TEXT_JAVASCRIPT_ONSUBMIT_FORM_INFO?></p>         
	  <div class="form-group">		  	
	    <div class="col-md-12">	
	  	  <?php echo textarea_tag('javascript_onsubmit',$cfg->get('javascript_onsubmit'),array('class'=>'form-control')) ?>      
	    </div>			
	  </div>	  
  </div>  
   
</div> 	  
    
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<?php echo app_include_codemirror(['javascript']) ?>

<script>
$('#ajax-modal').on('shown.bs.modal', function() {
	var myCodeMirror1 = CodeMirror.fromTextArea(document.getElementById('javascript_in_from'), {
    lineNumbers: true,       
    autofocus:true,    
  }); 
	
});

$('#onSubmitTab').click(function(){
	if(!$(this).hasClass('acitve-codemirror'))
	{
		setTimeout(function() {
				var myCodeMirror2 = CodeMirror.fromTextArea(document.getElementById('javascript_onsubmit'), {
			    lineNumbers: true
			  });
			}, 300);

		$(this).addClass('acitve-codemirror')
	}		
})
	
</script>
   