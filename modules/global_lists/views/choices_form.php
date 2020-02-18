
<?php echo ajax_modal_template_header(TEXT_HEADING_VALUE_IFNO) ?>

<?php echo form_tag('choices_form', url_for('global_lists/choices','action=save&lists_id=' . $_GET['lists_id'] . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
<ul class="nav nav-tabs">
  <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>  
  <li><a href="#users"  data-toggle="tab"><?php echo TEXT_USERS ?></a></li>  
  <li><a href="#note"  data-toggle="tab"><?php echo TEXT_NOTE ?></a></li>
</ul> 


<div class="tab-content">
  <div class="tab-pane fade active in" id="general_info">      
       
  <div class="form-group">
  	<label class="col-md-4 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
    <div class="col-md-8">	
  	  <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('is_active','1',array('checked'=>$obj['is_active'])) ?></label></div>      
    </div>			
  </div>
       
  <div class="form-group">
  	<label class="col-md-4 control-label" for="parent_id"><?php echo tooltip_icon(TEXT_CHOICES_PARENT_INFO) . TEXT_PARENT ?></label>
    <div class="col-md-8">	
  	  <?php echo select_tag('parent_id',global_lists::get_choices($_GET['lists_id'],true,TEXT_TOP_LEVEL), (isset($_GET['parent_id']) ? $_GET['parent_id'] : $obj['parent_id']),array('class'=>'form-control input-large chosen-select')) ?>      
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-4 control-label" for="name"><?php echo tooltip_icon(TEXT_CHOICES_NAME_INFO) . TEXT_NAME ?></label>
    <div class="col-md-8">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required autofocus')) ?>      
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-4 control-label" for="is_default"><?php echo tooltip_icon(TEXT_CHOICES_IS_DEFAULT_INFO) . TEXT_IS_DEFAULT ?></label>
    <div class="col-md-8">	
  	  <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('is_default','1',array('checked'=>$obj['is_default'])) ?></label></div>      
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-4 control-label" for="bg_color"><?php echo tooltip_icon(TEXT_CHOICES_BACKGROUND_COLOR_INFO) .  TEXT_BACKGROUND_COLOR ?></label>
    <div class="col-md-8">
    	<div class="input-group input-small color colorpicker-default" data-color="<?php echo (strlen($obj['bg_color'])>0 ? $obj['bg_color']:'#ff0000')?>" >
  	   <?php echo input_tag('bg_color',$obj['bg_color'],array('class'=>'form-control input-small')) ?>
        <span class="input-group-btn">
  				<button class="btn btn-default" type="button"><i style="background-color: #3865a8;"></i>&nbsp;</button>
  			</span>
  		</div>      
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-4 control-label" for="sort_order"><?php echo tooltip_icon(TEXT_CHOICES_SORT_ORDER_INFO) . TEXT_SORT_ORDER ?></label>
    <div class="col-md-8">	
  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-small')) ?>      
    </div>			
  </div>
      
  </div>
  
  <div class="tab-pane fade" id="users">
    
	  <div class="form-group">
	  	<label class="col-md-4 control-label" for="users"><?php echo tooltip_icon(TEXT_CHOICES_USERS_INFO) . TEXT_USERS_LIST ?></label>
	    <div class="col-md-8">	
	  	  <?php 
	        $attributes = array('class'=>'form-control chosen-select input-xlarge',
	                          'multiple'=>'multiple',
	                          'data-placeholder'=>TEXT_SELECT_SOME_VALUES); 
	                          
	        echo select_tag('users[]',users::get_choices(),explode(',',$obj['users']),$attributes);
	        echo tooltip_text(TEXT_GLOBAL_LIST_USER_NOTES);
	      ?>      
	    </div>			
	  </div>
  
  </div>
  
  <div class="tab-pane fade" id="note">
	  <div class="form-group">
	  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_ADMINISTRATOR_NOTE ?></label>
	    <div class="col-md-9">	
	  	  <?php echo textarea_tag('notes',$obj['notes'],array('class'=>'form-control')) ?>
	    </div>			
	  </div> 
  </div>
  
</div>  
      
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#choices_form').validate({
    	submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
     });                                                                                                    
  });        
</script>   