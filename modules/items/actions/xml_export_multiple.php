<?php

if(!xml_export::has_users_access($current_entity_id,_get::int('templates_id')))
{
	redirect_to('dashboard/access_forbidden');
}

$template_info = db_find('app_ext_xml_export_templates',_get::int('templates_id'));


switch($app_module_action)
{
	case 'export':
		$filename = str_replace(' ','_',trim($_POST['filename']));

		$xml_export = new xml_export($template_info['id'],$app_selected_items[$_POST['reports_id']]);
		$xml_export->filename = $filename;
		$xml_export->export();

		exit();
		break;
}
