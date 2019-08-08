<?php
$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_user_roles',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_user_roles');
}