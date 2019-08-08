<?php

switch($app_module_action)
{
	case 'getMapView':
		header('Content-Type: application/json');
							
		$image_map = new image_map(_post::int('id'));
		
		if(isset($_GET['path']))
			$image_map->set_path($app_path);
		
		if(isset($_GET['reports_id']))
			$image_map->set_reports_id(_get::int('reports_id'));
		
		if(isset($_GET['fiters_reports_id']))
			$image_map->set_fiters_reports_id(_get::int('fiters_reports_id'));
		
		$data = $image_map->get_data();
		
		//$data = '{"code":1,"data":{"map":{"id":86,"name":"Test","enabled":1,"showLegend":1,"zoom":"2","mapImage":{"width":6500,"height":1825}},"regions":[{"id":"1","name":"Region 1","mapId":"1","x":"1952","y":"550","zoom":"default"}],"labels":[{"id":1,"x":1964,"y":488,"clickable":true,"html":"<!--<div class=\"cfm-layer-element cfm-label\" data-id=\"1\" data-x=\"1964\" data-y=\"488\" >--><div class=\"cfm-inner\" ><div class=\"cfm-title\"><a href=\"#\" data-cfm-region-link=\'{\"mapId\":\"1\",\"regionId\":1}\'>Label 1<\/a><\/div><\/div><!--<\/div>-->"}],"markers":[{"id":2,"x":1648,"y":232,"typeCssName":"Male","html":"<div class=\"cfm-inner\"><div class=\"cfm-title-params\"><span class=\"cfm-icon\"><img src=\"uploads\/icons\/male-24-24.png\" width=\"24\" height=\"24\" \/><\/span><span class=\"cfm-title\">Test Male<\/span><\/div><\/div>"},{"id":3,"x":1384,"y":592,"typeCssName":"Male","html":"<div class=\"cfm-inner\"><div class=\"cfm-title-params\"><span class=\"cfm-icon\"><img src=\"uploads\/icons\/male-24-24.png\" width=\"24\" height=\"24\" \/><\/span><span class=\"cfm-title\">asdasd<\/span><\/div><\/div>"},{"id":4,"x":884,"y":504,"typeCssName":"test","html":"<div class=\"cfm-inner\"><div class=\"cfm-title-params\"><span class=\"cfm-icon\"><img src=\"uploads\/icons\/male-24-24.png\" width=\"24\" height=\"24\" \/><\/span><span class=\"cfm-title\">123123<\/span><\/div><\/div>"}],"viewHtml":{"breadcrumb":"<ul class=\"breadcrumb\" ><li>Test<\/li><\/ul>","legend":"<li><div class=\"cfm-marker cfm-marker-Male\"><\/div>&nbsp;&nbsp;Male<\/li><li><div class=\"cfm-marker cfm-marker-test\"><\/div>&nbsp;&nbsp;Test<\/li>"}}}';
						
		echo $data;
		
		exit();
		break;
	case 'saveElementsPositions':
		if(isset($_POST['markers']))
		{
			image_map::save_markers();
		}
		
		if(isset($_POST['labels']))
		{
			image_map::save_labels();
		}
		
		exit();
		break;
}