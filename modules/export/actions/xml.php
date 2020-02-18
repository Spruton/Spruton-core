<?php

$templates_query = db_query("select * from app_ext_xml_export_templates where id='" . _get::int('id') . "' and is_public=1 and is_active=1");
if(!$templates = db_fetch_array($templates_query))
{
	die(TEXT_PAGE_NOT_FOUND_HEADING);
}
	
$xml_export = new xml_export($templates['id']);
$xml_export->export();

exit();	