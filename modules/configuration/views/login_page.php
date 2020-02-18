
<h3 class="page-title"><?php echo TEXT_HEADING_LOGIN_PAGE_CONFIGURATION ?></h3>

<?php echo form_tag('cfg', url_for('configuration/save','redirect_to=configuration/login_page'),array('enctype'=>'multipart/form-data','class'=>'form-horizontal')) ?>
<div class="form-body">

  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_LOGIN_PAGE_HEADING"><?php echo TEXT_LOGIN_PAGE_HEADING ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('CFG[LOGIN_PAGE_HEADING]', CFG_LOGIN_PAGE_HEADING,array('class'=>'form-control input-large')); ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_LOGIN_PAGE_CONTENT"><?php echo TEXT_LOGIN_PAGE_CONTENT ?></label>
    <div class="col-md-9">	
  	  <?php echo textarea_tag('CFG[LOGIN_PAGE_CONTENT]', CFG_LOGIN_PAGE_CONTENT,array('class'=>'form-control input-xlarge','rows'=>3)); ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="APP_LOGIN_PAGE_BACKGROUND"><?php echo TEXT_LOGIN_PAGE_BACKGROUND ?></label>
    <div class="col-md-9">	
  	  <?php echo input_file_tag('APP_LOGIN_PAGE_BACKGROUND',array('accept'=>fieldtype_attachments::get_accept_types_by_extensions('gif,jpg,png'))) . input_hidden_tag('CFG[APP_LOGIN_PAGE_BACKGROUND]',CFG_APP_LOGIN_PAGE_BACKGROUND);             
      if(is_file(DIR_FS_UPLOADS  . '/' . CFG_APP_LOGIN_PAGE_BACKGROUND))
      {
        echo  '<span class="help-block">' . CFG_APP_LOGIN_PAGE_BACKGROUND . '<label class="checkbox">' . input_checkbox_tag('delete_login_page_background') . ' ' . TEXT_DELETE . '</label></span>';
      }   
      
      echo tooltip_text(TEXT_LOGIN_PAGE_BACKGROUND_INFO);                                                                                              
    ?>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_LOGIN_PAGE_HIDE_REMEMBER_ME"><?php echo TEXT_HIDE . ' "' . TEXT_REMEMBER_ME . '"' ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[LOGIN_PAGE_HIDE_REMEMBER_ME]',$default_selector,CFG_LOGIN_PAGE_HIDE_REMEMBER_ME,array('class'=>'form-control input-small')); ?>
    </div>			
  </div>
  
<?php 
	if(is_ext_installed())
	{ 
		$modules = new modules('digital_signature');
		$choices = $modules->get_active_modules();
?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_LOGIN_DIGITAL_SIGNATURE_MODULE"><?php echo TEXT_DIGITAL_SIGNATURE_LOGIN ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[LOGIN_DIGITAL_SIGNATURE_MODULE]',[''=>'']+$choices,CFG_LOGIN_DIGITAL_SIGNATURE_MODULE,array('class'=>'form-control input-large')); ?>
  	  <?php echo tooltip_text(TEXT_DIGITAL_SIGNATURE_LOGIN_INFO) ?>
    </div>			
  </div>
<?php } ?>  
    
<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>

</div>
</form>
