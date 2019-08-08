
<?php echo ajax_modal_template_header(TEXT_CHANGE_STRUCTURE) ?>

<?php echo form_tag('entities_form', url_for('entities/entities_change_structure','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <p><?php echo TEXT_CHANGE_STRUCTURE_INFO; ?></p>
  
  <div class="form-body">
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_MOVE_ENTITY ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('entities_id',[''=>TEXT_SELECT_ENTITY]+entities::get_choices(),'',array('class'=>'form-control input-xlarge required')) ?>
    </div>			
  </div>
  
<?php if(!defined('TEXT_ENTITY_MOVE_TO')) define('TEXT_ENTITY_MOVE_TO','Move to') ?>  
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_ENTITY_MOVE_TO ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('move_to_entities_id',entities::get_choices_with_empty(TEXT_TOP_LEVEL),'',array('class'=>'form-control input-xlarge required','onChange'=>'get_parent_items_list()')) ?>
    </div>			
  </div>
  
  <div id="parent_items_list"></div>
  
  </div>
</div>

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#entities_form').validate({ignore:'',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                                                  
  });

  function get_parent_items_list()
  {
  	$('#parent_items_list').html('<div class="ajax-loading"></div>');
  	
  	$('#parent_items_list').load('<?php echo url_for("entities/entities_change_structure","action=get_parent_items")?>',{ entities_id:$('#move_to_entities_id').val() },function(response, status, xhr) {
      if (status == "error") {                                 
         $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
      }
      else
      {    
      	appHandleUniform();            
        jQuery(window).resize();      
      }    
    }); 
  }
  
</script> 