
<h3 class="page-title"><?php echo TEXT_HEADING_ATTACHMENTS_CONFIGURAITON ?></h3>
 
<?php echo form_tag('cfg', url_for('configuration/save'),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('redirect_to','configuration/attachments') ?>
<div class="form-body">

	<div class="form-group">
  	<label class="col-md-3 control-label" ><?php echo TEXT_MAX_UPLOAD_FILE_SIZE ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_SERVER_UPLOAD_MAX_FILESIZE; ?> MB</p>
  	  <?php echo tooltip_text(TEXT_MAX_UPLOAD_FILE_SIZE_TIP) ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_ENCRYPT_FILE_NAME ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[ENCRYPT_FILE_NAME]',$default_selector,CFG_ENCRYPT_FILE_NAME,array('class'=>'form-control input-small')); ?>
  	  <?php echo tooltip_text(TEXT_ENCRYPT_FILE_NAME_TIP) ?>
    </div>			
  </div>
  
<?php 
$choices = [];

$choices[''] = TEXT_NONE;
$fields_query = db_query("select f.id,f.name, e.name as entity_name from app_fields f, app_entities e where e.id=f.entities_id and type in ('fieldtype_attachments', 'fieldtype_image','fieldtype_input_file')  order by e.sort_order, e.name, f.name");
while($fields = db_fetch_array($fields_query))
{
	$choices[$fields['entity_name']][$fields['id']] = $fields['name']; 
}
?>  
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_PUBLIC_ATTACHMENTS"><?php echo TEXT_ALLOW_PUBLIC_ACCESS ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[PUBLIC_ATTACHMENTS][]',$choices,CFG_PUBLIC_ATTACHMENTS,array('class'=>'form-control input-xlarge chosen-select','multiple'=>true)); ?>
  	  <?php echo tooltip_text(TEXT_PUBLIC_ATTACHMENTS_TIP) ?>
    </div>			
  </div>
  
<h3 class="form-section"></h3>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_RESIZE_IMAGES ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[RESIZE_IMAGES]',$default_selector,CFG_RESIZE_IMAGES,array('class'=>'form-control input-small')); ?>
  	  <?php echo tooltip_text(TEXT_RESIZE_IMAGES_TIP) ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_MAX_IMAGE_WIDTH"><?php echo TEXT_MAX_IMAGE_WIDTH ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('CFG[MAX_IMAGE_WIDTH]', CFG_MAX_IMAGE_WIDTH,array('class'=>'form-control input-small number','type'=>'number')); ?>
  	  <?php echo tooltip_text(TEXT_ENTER_VALUES_IN_PIXELS_OR_LEAVE_LBANK) ?>  	  
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_MAX_IMAGE_HEIGHT"><?php echo TEXT_MAX_IMAGE_HEIGHT ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('CFG[MAX_IMAGE_HEIGHT]', CFG_MAX_IMAGE_HEIGHT,array('class'=>'form-control input-small number','type'=>'number')); ?>
  	  <?php echo tooltip_text(TEXT_ENTER_VALUES_IN_PIXELS_OR_LEAVE_LBANK) ?>  	  
    </div>			
  </div> 
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES_TYPES"><?php echo tooltip_icon(TEXT_RESIZE_IMAGES_TYPES_TIP) . TEXT_IMAGES_TYPES ?></label>
    <div class="col-md-9">	
  	  <?php echo select_checkboxes_tag('CFG[RESIZE_IMAGES_TYPES]',array('1'=>'gif','2'=>'jpeg','3'=>'png'), CFG_RESIZE_IMAGES_TYPES); ?>  	  
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_SKIP_IMAGE_RESIZE"><?php echo TEXT_SKIP_IMAGE_RESIZE ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('CFG[SKIP_IMAGE_RESIZE]', CFG_SKIP_IMAGE_RESIZE,array('class'=>'form-control input-small number','type'=>'number')); ?>
  	  <?php echo tooltip_text(TEXT_SKIP_IMAGE_RESIZE_TIP) ?>
    </div>			
  </div> 
 
                                                                                                          
  <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
  

      
</div>
</form>



