<?php

if(!isset($two_step_verification_info['code']))
{
	two_step_verification::send_code();
}		

switch($app_module_action)
{
	case 'check':
		
		if($two_step_verification_info['code']==$_POST['code'])
		{
			two_step_verification::approve();
		}
		else
		{
			$alerts->add(TEXT_INCORRECT_CODE,'error');
			redirect_to('users/2step_verification');
		}
		
		break;
}		

$app_layout = 'public_layout.php';