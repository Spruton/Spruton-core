<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_listing_sections',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_listing_sections');
	$obj['sort_order'] = listing_types::get_sections_next_order($_GET['listing_types_id']);
}