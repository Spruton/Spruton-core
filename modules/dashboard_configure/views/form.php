
<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('users_alerts_form', url_for('dashboard_configure/index','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('type',$obj['type'])?>
<div class="modal-body">
  <div class="form-body">
   
  <div class="form-group">
	 	<label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active',$obj['is_active'],array('checked'=>($obj['is_active']==1 ? 'checked':''))) ?></p>
    </div>			
  </div>

<?php if($_GET['type']=='info_block'):  ?>  
	<div class="form-group">
  	<label class="col-md-3 control-label" for="sections_id"><?php echo TEXT_POSITION ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('sections_id',dashboard_pages::get_section_choices(),$obj['sections_id'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div>
<?php endif ?>  
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="color"><?php echo TEXT_COLOR ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('color',dashboard_pages::get_color_choices(),$obj['color'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div>
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_TITLE ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-xlarge' . ($obj['type']=='page' ? ' required':''))) ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="icon"><?php echo TEXT_ICON ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('icon',$obj['icon'],array('class'=>'form-control input-medium')) ?>
  	  <?php echo tooltip_text(TEXT_MENU_ICON_TITLE_TOOLTIP)?>
    </div>			
  </div>
  
<?php if($_GET['type']=='page') { ?>
  <div class="form-group">
  	<label class="col-md-3 control-label" for="description"><?php echo TEXT_DESCRIPTION ?></label>
    <div class="col-md-9">	
  	  <?php echo textarea_tag('description',$obj['description'],array('class'=>'editor')) ?>
    </div>			
  </div>
<?php }else{ ?>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="description"><?php echo TEXT_DESCRIPTION ?></label>
    <div class="col-md-9">	
  	  <?php echo textarea_tag('description',$obj['description'],array('class'=>'form-control input-xlarge textarea-small')) ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="users_fields"><?php echo TEXT_FIELDS ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('users_fields[]',fields::get_choices(1) ,$obj['users_fields'],array('class'=>'form-control input-xlarge chosen-select chosen-sortable','multiple'=>'multiple', 'chosen_order'=>$obj['users_fields'])) ?>
    </div>			
  </div>
  
<?php }?>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_USERS_GROUPS ?></label>
    <div class="col-md-9">	
<?php 
	  	  $attributes = array('class'=>'form-control input-xlarge chosen-select',
	  	  		'multiple'=>'multiple',
	  	  		'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
	  	  
	  	  $users_groups = (strlen($obj['users_groups'])>0 ? explode(',',$obj['users_groups']) : array());
	  	  echo select_tag('users_groups[]',access_groups::get_choices(),$users_groups,$attributes);
?>      
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
    
 
