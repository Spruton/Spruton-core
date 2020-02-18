<?php 
//set check to stop autologoff current user
$two_step_verification_info['is_checked']=true;
?>
<h3 class="page-title"><?php echo TEXT_2STEP_VERIFICATION ?></h3>

<p><?php echo TEXT_2STEP_VERIFICATION_INFO ?></p>

<?php echo form_tag('cfg', url_for('configuration/save','redirect_to=configuration/2step_verification'),array('class'=>'form-horizontal')) ?>
<div class="form-body">

	<div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_2STEP_VERIFICATION_ENABLED"><?php echo TEXT_ENABLE_TEXT_2STEP_VERIFICATION ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[2STEP_VERIFICATION_ENABLED]', $default_selector ,CFG_2STEP_VERIFICATION_ENABLED,array('class'=>'form-control input-small')); ?>
    </div>			
  </div>
  
<?php 
$choices = [
		'email' => 'Email',
		'sms' => 'SMS'		
];
?>  
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_2STEP_VERIFICATION_TYPE"><?php echo TEXT_SEND_CODE_BY ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[2STEP_VERIFICATION_TYPE]', $choices ,CFG_2STEP_VERIFICATION_TYPE,array('class'=>'form-control input-small')); ?>
    </div>			
  </div>
  

<div id="sms_settings" style="display:none">  
<?php 
if(is_ext_installed())
{
	
	$modules = new modules('sms');
	$choices = $modules->get_active_modules();	
?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_2STEP_VERIFICATION_SMS_MODULE"><?php echo TEXT_EXT_SMS_MODULE ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[2STEP_VERIFICATION_SMS_MODULE]', $choices ,CFG_2STEP_VERIFICATION_SMS_MODULE,array('class'=>'form-control input-large required')); ?>
    </div>			
  </div>

<?php 
	$choices = array(''=>'');
	$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_input','fieldtype_input_masked','fieldtype_phone') and f.entities_id=1 and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
	while($fields = db_fetch_array($fields_query))
	{
		$choices[$fields['id']] = $fields['name'];
	}
?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_2STEP_VERIFICATION_USER_PHONE"><?php echo TEXT_PHONE ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('CFG[2STEP_VERIFICATION_USER_PHONE]', $choices ,CFG_2STEP_VERIFICATION_USER_PHONE,array('class'=>'form-control input-large required')); ?>
  	  <?php echo tooltip_text(TEXT_EXT_SEND_TO_USER_NUMBER_INFO) ?>
    </div>			
  </div>
<?php 
}
else
{
?>
	<div class="form-group">
  	<label class="col-md-3 control-label" ></label>
    <div class="col-md-3">	
  	  <?php echo alert_error(TEXT_EXTENSION_REQUIRED);?>
    </div>			
  </div>
<?php   
	
}
?>  
</div>

<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
 
</div>
</form>

<script>
$(function() { 
	$('#cfg').validate();

	$('#CFG_2STEP_VERIFICATION_TYPE').change(function(){
			show_sms_settings($(this).val())			
		})

	show_sms_settings($('#CFG_2STEP_VERIFICATION_TYPE').val())
})

function show_sms_settings(type)
{
	if(type=='sms')
  {
	  $('#sms_settings').show();
	}
	else
	{
		$('#sms_settings').hide();
	}
}
</script>