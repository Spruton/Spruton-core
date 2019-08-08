<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_INFO ?></h4>
</div>


<?php echo form_tag('panel_form', url_for('filters_panels/panels','action=save&entities_id=' . $_GET['entities_id'] . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
   <div class="form-group">
	 	<label class="col-md-4 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
    <div class="col-md-8">	
  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active',$obj['is_active'],array('checked'=>($obj['is_active']==1 ? 'checked':''))) ?></p>
    </div>			
  </div>
      
  <div class="form-group">
  	<label class="col-md-4 control-label" for="position"><?php echo TEXT_POSITION ?></label>
    <div class="col-md-8">	
  	  <?php echo select_tag('position',filters_panels::get_position_choices(),$obj['position'],array('class'=>'form-control input-medium required','onChange'=>'check_panel_position()')) ?>
    </div>			
  </div>
  
	<div class="form-group">
	 	<label class="col-md-4 control-label" for="is_active_filters"><?php echo tooltip_icon(TEXT_ACTIVE_FILTERS_INFO) . TEXT_ACTIVE_FILTERS ?></label>
    <div class="col-md-8">	
  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active_filters',$obj['is_active_filters'],array('checked'=>($obj['is_active_filters']==1 ? 'checked':''))) ?></p>
    </div>			
  </div>  
  
  <div class="form-group panel-with-option">  
		<label class="col-md-4 control-label" for="width"><?php echo TEXT_WIDHT ?></label>
    <div class="col-md-8">	
  	  <?php echo select_tag('width',filters_panels::get_width_choices(),$obj['width'],array('class'=>'form-control input-small')) ?>
    </div>			
  </div>
    
<?php 

$choices = [];
$choices[0] = TEXT_ADMINISTRATOR;

$groups_query = db_fetch_all('app_access_groups','','sort_order, name');
while($groups = db_fetch_array($groups_query))
{
	$entities_access_schema = users::get_entities_access_schema($_GET['entities_id'],$groups['id']);

	if((!in_array('view',$entities_access_schema) and  !in_array('view_assigned',$entities_access_schema))) continue;

	$choices[$groups['id']] = $groups['name'];
}
?>  
  
  <div class="form-group">  
		<label class="col-md-4 control-label" for="users_groups"><?php echo tooltip_icon(TEXT_FILTERS_PANELS_ACCESS_INFO) . TEXT_USERS_GROUPS ?></label>
    <div class="col-md-8">	
  	  <?php echo select_tag('users_groups[]',$choices,$obj['users_groups'],array('class'=>'form-control chosen-select','multiple'=>'multiple')) ?>
    </div>			
  </div>
  
  <div class="form-group">  
		<label class="col-md-4 control-label" for="width"><?php echo TEXT_SORT_ORDER ?></label>
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
    $('#panel_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    }); 

    check_panel_position()                                                                  
  });

  function check_panel_position()
  {
    if($('#position').val()=='horizontal')
    {
      $('.panel-with-option').hide()
    }
    else
    {
    	$('.panel-with-option').show()
    }

  }
  
</script>  