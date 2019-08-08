
<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('pages_form', url_for('help_pages/pages','action=save&entities_id=' . _get::int('entities_id') . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('type',$obj['type'])?>
<div class="modal-body">
  <div class="form-body">
   
  <div class="form-group">
	 	<label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active',$obj['is_active'],array('checked'=>($obj['is_active']==1 ? 'checked':''))) ?></p>
    </div>			
  </div>
  
<?php if($obj['type']=='announcement'){ ?>    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="color"><?php echo TEXT_COLOR ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('color',help_pages::get_color_choices(),$obj['color'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="icon"><?php echo TEXT_ICON ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('icon',$obj['icon'],array('class'=>'form-control input-medium')) ?>
  	  <?php echo tooltip_text(TEXT_MENU_ICON_TITLE_TOOLTIP)?>
    </div>			
  </div>
<?php }else{ ?>

	<div class="form-group">
  	<label class="col-md-3 control-label" for="color"><?php echo TEXT_POSITION ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('position',help_pages::get_position_choices(),$obj['position'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div>

<?php } ?>  
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_TITLE ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-xlarge' . ($obj['type']=='page' ? ' required':''))) ?>
  	  <?php echo ($obj['type']=='announcement' ? tooltip_text(TEXT_NOT_REQUIRED_FIELD) : '') ?>
    </div>			
  </div>
  
<?php if($obj['type']=='announcement'): ?>
  <div class="form-group">
  	<label class="col-md-3 control-label" for="description"><?php echo TEXT_DESCRIPTION ?></label>
    <div class="col-md-9">	
  	  <?php echo textarea_tag('description',$obj['description'],array('class'=>'editor')) ?>
    </div>			
  </div>
  
<div class="form-group">
  	<label class="col-md-3 control-label" for="start_date"><?php echo TEXT_DISPLAY_DATE ?></label>
    <div class="col-md-9">	
  	  <div class="input-group input-large datepicker input-daterange daterange-filter">					
				<span class="input-group-addon">
					<i class="fa fa-calendar"></i>
				</span>
				<?php echo input_tag('start_date',($obj['start_date']>0 ? date('Y-m-d',$obj['start_date']) :''),array('class'=>'form-control','placeholder'=>TEXT_DATE_FROM))?>
				<span class="input-group-addon">
					<i style="cursor:pointer" class="fa fa-refresh" aria-hidden="true" title="<?php echo TEXT_EXT_RESET ?>" onClick="app_reset_date_range_input('daterange-filter','start_date','end_date')"></i>
				</span>
				<?php echo input_tag('end_date',($obj['end_date']>0 ? date('Y-m-d',$obj['end_date']) :''),array('class'=>'form-control','placeholder'=>TEXT_DATE_TO))?>			
			</div>	
    </div>			
  </div>  
  
<?php endif ?>  

  
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


<?php if($obj['type']=='page'): ?>
  <div class="form-group">
  	<label class="col-md-3 control-label" for="description"><?php echo TEXT_DESCRIPTION ?></label>
    <div class="col-md-12">	
  	  <?php echo textarea_tag('description',$obj['description']) ?>
    </div>			
  </div>
  
<script>
	$(function(){        
	  use_editor_full('description',false,300)     
	})  
</script>

<?php endif ?>

      
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#pages_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                           
  }); 

</script>   
    
 
