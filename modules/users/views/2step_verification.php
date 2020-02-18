<?php

switch(CFG_2STEP_VERIFICATION_TYPE)
{
	case 'email':
		$email = $app_user['email'];
		if(strlen($email)<15)
		{
			$email = substr_replace($email,str_repeat('*', strlen($email)-5),-5);
		}	
		else 
		{
			$email = substr_replace($email,str_repeat('*', strlen($email)-10),5,-5);
		}
		
		$page_title = TEXT_CODE_FROM_EMAIL;
		$page_body = sprintf(TEXT_CODE_FROM_EMAIL_INFO,$email);
		break;
	case 'sms':
		$phone = $app_user['fields']['field_' . CFG_2STEP_VERIFICATION_USER_PHONE];
		$page_title = TEXT_CODE_FROM_SMS;
		$page_body = sprintf(TEXT_CODE_FROM_SMS_INFO,substr_replace($phone,str_repeat('*', strlen($phone)-7),5,-2));
		break;
}

?>

<h3 class="form-title"><?php echo $page_title ?></h3>

<p><?php echo $page_body ?></p>


<?php echo form_tag('login_form', url_for('users/2step_verification','action=check'),array('class'=>'login-form')) ?>

<div class="form-group">
	<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
	<label class="control-label visible-ie8 visible-ie9"><?php echo $page_title ?></label>
	<div class="input-icon">
		<i class="fa fa-user"></i>
		<input class="form-control placeholder-no-fix required" type="text" autocomplete="off" placeholder="<?php echo $page_title ?>" name="code"/>
	</div>
</div>

<div class="form-actions">
	
	<button type="button" id="back-btn" class="btn btn-default" onClick="location.href='<?php echo url_for('users/login')?>'"><i class="fa fa-arrow-circle-left"></i> <?php echo TEXT_BUTTON_BACK ?></button>
	
	<button type="submit" class="btn btn-info pull-right"><?php echo TEXT_BUTTON_LOGIN ?></button>
</div>

</form>

<script>
  $(function() { 
    $('#login_form').validate();                                                                            
  });    
</script> 