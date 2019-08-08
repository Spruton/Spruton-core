<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_INFO ?></h4>
</div>


<?php echo form_tag('panel_form', url_for('filters_panels/fields','action=save&panels_id=' . _get::int('panels_id'). '&entities_id=' . $_GET['entities_id'] . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
  <div id="panels_fields"></div>
  
  <div id="panels_fields_settings"></div>
  
  <div class="form-group field-height-option" style="display:none">
	<label class="col-md-3 control-label" for="width"><?php  echo tooltip_icon(TEXT_ENTER_LIST_HEIGHT) . TEXT_HEIGHT ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('height',$obj['height'],array('class'=>'form-control input-small number')) ?>
    </div>			
  </div>
             
  </div>
</div>
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() {     
    $('#panel_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });  

    load_panels_fields();                                                                 
  });

  function load_panels_fields()
  {
  	$("#panels_fields").load('<?php echo url_for('filters_panels/fields','action=load_panels_fields&panels_id=' . _get::int('panels_id'). '&entities_id=' . $_GET['entities_id']. (isset($_GET['id']) ? '&id=' . $_GET['id']:'')) ?>',function(){
  		load_panels_fields_settings();
  		
			appHandleUniform();      
      jQuery(window).resize(); 	
      
    })
  }

  function load_panels_fields_settings()
  {
  	$("#panels_fields_settings").load('<?php echo url_for('filters_panels/fields','action=load_panels_fields_settings&panels_id=' . _get::int('panels_id'). '&entities_id=' . $_GET['entities_id']. (isset($_GET['id']) ? '&id=' . $_GET['id']:'')) ?>&fields_id='+$('#fields_id').val(),function(){
  		appHandleUniform();      
      jQuery(window).resize();
      
      check_field_height_option();
   	})
  }

  function check_field_height_option()
  {
    if($('#display_type').val()=='checkboxes' || $('#display_type').val()=='radioboxes')
    {
      $('.field-height-option').show();
    }
    else
    {
    	$('.field-height-option').hide();
    }
  }
  
</script>  