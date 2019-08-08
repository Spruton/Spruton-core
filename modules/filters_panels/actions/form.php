<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_filters_panels',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_filters_panels');
	$obj['is_active_filters']=1;
}