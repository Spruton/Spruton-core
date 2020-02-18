<?php
$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_records_visibility_rules',(int)$_GET['id']);
}
else
{
	$obj = db_show_columns('app_records_visibility_rules');
	$obj['is_active']=1;
}