<?php
class entities_menu
{	
	static function get_reports_choices()
	{
		global $app_user;
		
		$choices = array();
		
		$reports_query = db_query("select id, name from app_reports where created_by='" . db_input($app_user['id']) . "' and reports_type in ('standard') order by name");
		while($v = db_fetch_array($reports_query))
		{
			$choices[TEXT_STANDARD_REPORTS]['standard' . $v['id']] = $v['name'];
		}
		
		$reports_query = db_query("select id, name from app_reports_groups where created_by='" . db_input($app_user['id']) . "' and is_common=0 order by sort_order, name");
		while($v = db_fetch_array($reports_query))
		{
			$choices[TEXT_REPORTS_GROUPS]['dashboard' . $v['id']] = $v['name'];
		}
		
		if(is_ext_installed())
		{	
			//get common reports
			$reports_query = db_query("select r.id, r.name from app_reports r, app_entities e, app_entities_access ea  where r.entities_id = e.id and e.id=ea.entities_id and r.reports_type = 'common' order by r.dashboard_sort_order, name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_COMMON_REPORTS]['common' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_reports_groups where is_common=1 order by sort_order, name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_COMMON_REPORTS_GROUPS]['dashboard' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_track_changes where is_active=1 order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_CHANGE_HISTORY]['track_changes' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select g.id, g.name from app_ext_ganttchart g, app_entities e where e.id=g.entities_id and e.parent_id=0 order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_GANTTCHART_REPORT]['ganttreport' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_graphicreport order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_GRAPHIC_REPORT]['graphicreport' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_pivotreports order by sort_order, name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_PIVOTREPORTS]['pivotreports' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_timeline_reports order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_TIMELINE_REPORTS]['timelinereport' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_funnelchart order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_FUNNELCHART]['funnelchart' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select k.id, k.name from app_ext_kanban k, app_entities e where e.id=k.entities_id and e.parent_id=0 order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_KANBAN]['kanban' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_calendar order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_СALENDAR]['calendarreport' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_pivot_calendars order by sort_order, name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_PIVOT_СALENDAR]['pivot_calendars' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_image_map order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_IMAGE_MAP]['image_map' . $v['id']] = $v['name'];
			}
			
			$reports_query = db_query("select id, name from app_ext_map_reports order by name");
			while($v = db_fetch_array($reports_query))
			{
				$choices[TEXT_EXT_MAP_REPORTS]['map_reports' . $v['id']] = $v['name'];
			}
		}
		
		//check duplicates names and add (x) if exist
		if(count($choices))
		{
			foreach($choices as $type=>$report)
			{
				foreach($report as $key=>$name)
				{
					
					$count = 1;
					
					do
					{
						$check = false;
						
						foreach($choices as $type1=>$report1)
						{
							foreach($report1 as $key1=>$name1)
							{
								//echo $name . '==' . $name1 . ' - ' . $key . '!=' . $key1 . '<br>';
								
								if($name==$name1 and $key!=$key1)
								{
									$check = true;
								}	
							}
						}
						
						if($check)
						{
							if(strstr($name,' (' . ($count-1) . ')'))
							{
								$name = str_replace(' (' . ($count-1) . ')',' (' . $count . ')',$name);
							}
							else
							{
								$name .= ' (' . $count . ')';
							}
							
							$count++;
						}
					}
					while($check);
					
					
					$choices[$type][$key] = $name;
				}
			}
		}
						
		return $choices;
	}
	
	static function get_reports_types()
	{
		return array(
				'standard',
				'dashboard',
				'common',
				'track_changes',
				'ganttreport',
				'graphicreport',
				'pivotreports',
				'timelinereport',	
				'funnelchart',
				'kanban',
				'calendarreport',
				'pivot_calendars',
				'image_map',
				'map_reports',
		);
	}
	
	static function get_reports_list($reports_list)
	{
		$choices = array();		
		foreach(explode(',',$reports_list) as $reports_type)
		{
												
			$reports_id = str_replace(self::get_reports_types(),'',$reports_type);
			
			$reports_table = '';
			
			switch(true)
			{
				case strstr($reports_type,'standard'):
				case strstr($reports_type,'common'):					
					$reports_table = 'app_reports';										
					break;
				case strstr($reports_type,'dashboard'):
					$reports_table = 'app_reports_groups';
					break;
				case strstr($reports_type,'track_changes'):					
					$reports_table = 'app_ext_track_changes';					
					break;
				case strstr($reports_type,'ganttreport'):
					$reports_table = 'app_ext_ganttchart';
					break;
				case strstr($reports_type,'graphicreport'):
					$reports_table = 'app_ext_graphicreport';
					break;
				case strstr($reports_type,'pivotreports'):
					$reports_table = 'app_ext_pivotreports';
					break;
				case strstr($reports_type,'timelinereport'):
					$reports_table = 'app_ext_timeline_reports';
					break;
				case strstr($reports_type,'funnelchart'):
					$reports_table = 'app_ext_funnelchart';
					break;
				case strstr($reports_type,'kanban'):
					$reports_table = 'app_ext_kanban';
					break;
				case strstr($reports_type,'calendarreport'):
					$reports_table = 'app_ext_calendar';
					break;
				case strstr($reports_type,'pivot_calendars'):
					$reports_table = 'app_ext_pivot_calendars';
					break;
				case strstr($reports_type,'image_map'):
					$reports_table = 'app_ext_image_map';
					break;
				case strstr($reports_type,'map_reports'):
					$reports_table = 'app_ext_map_reports';
					break;
				
			}
			
			if(strlen($reports_table))
			{
				$reports_info_query = db_query("select name from {$reports_table} where id='" . $reports_id. "'");
				if($reports_info = db_fetch_array($reports_info_query))
				{					
					$choices[] = $reports_info['name'];
				}
			}
		}
		
		$html = '';
		
		foreach($choices as $v)
		{
			$html .= '<div style="padding-left: 19px;">- ' . $v . '</div>';
		}
		
		return $html;
	}
	
	static function build_menu($reports_list, $sub_menu)
	{
		global $app_user;
		
		if(!strlen($reports_list)) return $sub_menu;
		
		foreach(explode(',',$reports_list) as $reports_type)
		{
			$reports_id = str_replace(self::get_reports_types(),'',$reports_type);
										
			switch(true)
			{
				case strstr($reports_type,'standard'):
					$reports_info_query = db_query("select name, id, menu_icon from app_reports where id='" . $reports_id. "' and created_by='" . $app_user['id'] . "'");
					if($reports_info = db_fetch_array($reports_info_query))
					{
						$menu_icon = (strlen($reports_info['menu_icon'])>0 ? $reports_info['menu_icon'] : 'fa-reorder');
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('reports/view','reports_id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'dashboard'):
					$reports_info_query = db_query("select name, id, menu_icon, is_common from app_reports_groups where id='" . $reports_id. "' and ((created_by='" . $app_user['id'] . "' and is_common=0) or (find_in_set(" . $app_user['group_id'] . ",users_groups) and is_common=1))");
					if($reports_info = db_fetch_array($reports_info_query))
					{
						$menu_icon = (strlen($reports_info['menu_icon'])>0 ? $reports_info['menu_icon'] : 'fa-cubes');
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('dashboard/' . ($reports_info['is_common'] ? 'reports_groups':'reports'),'id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'common'):
					$reports_info_query = db_query("select r.id, r.name, r.menu_icon from app_reports r, app_entities e, app_entities_access ea  where r.entities_id = e.id and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($app_user['group_id']) . "' and find_in_set(" . $app_user['group_id'] . ",r.users_groups) and r.reports_type = 'common'  and r.id='" . $reports_id . "'");
					if($reports_info = db_fetch_array($reports_info_query))
					{
						$menu_icon = (strlen($reports_info['menu_icon'])>0 ? $reports_info['menu_icon'] : 'fa-reorder');
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('reports/view','reports_id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'track_changes'):
					$reports_query = db_query("select id, name, menu_icon from app_ext_track_changes where id='" . $reports_id. "' and is_active=1 and (find_in_set('" . $app_user['group_id']. "',users_groups) or find_in_set('" .  $app_user['id'] . "',assigned_to))");
					if($reports_info = db_fetch_array($reports_query))
					{
						$menu_icon = (strlen($reports_info['menu_icon'])>0 ? $reports_info['menu_icon'] : 'fa-reorder');
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/track_changes/view','reports_id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'ganttreport'):
					if($app_user['group_id']>0)
					{
						$reports_query = db_query("select g.id, g.name from app_ext_ganttchart g, app_entities e, app_ext_ganttchart_access ga where g.id='" . $reports_id. "' and e.id=g.entities_id and e.parent_id=0 and g.id=ga.ganttchart_id and ga.access_groups_id='" . db_input($app_user['group_id']) . "' order by name");
					}
					else
					{
						$reports_query = db_query("select g.id, g.name from app_ext_ganttchart g, app_entities e where g.id='" . $reports_id. "' and  e.id=g.entities_id and e.parent_id=0 order by g.name");
					}
					
					if($reports_info = db_fetch_array($reports_query))
					{
						$menu_icon = 'fa-align-left';
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/ganttchart/dhtmlx','id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'graphicreport'):
					$reports_query = db_query("select id, name, allowed_groups from app_ext_graphicreport where id='" . $reports_id. "'");
					if($reports_info = db_fetch_array($reports_query))
					{
						if(in_array($app_user['group_id'],explode(',',$reports_info['allowed_groups'])) or $app_user['group_id']==0)
						{
							$menu_icon = 'fa-area-chart';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/graphicreport/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
				case strstr($reports_type,'pivotreports'):
					$reports_query = db_query("select id, name, allowed_groups from app_ext_pivotreports where id='" . $reports_id. "'");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(in_array($app_user['group_id'],explode(',',$reports_info['allowed_groups'])) or $app_user['group_id']==0)
						{
							$menu_icon = 'fa-table';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/pivotreports/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
				case strstr($reports_type,'timelinereport'):
					$reports_query = db_query("select id, name, allowed_groups from app_ext_timeline_reports where id='" . $reports_id. "'");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(in_array($app_user['group_id'],explode(',',$reports_info['allowed_groups'])) or $app_user['group_id']==0)
						{							
							$menu_icon = 'fa-sliders';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/timeline_reports/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
				case strstr($reports_type,'funnelchart'):
					$reports_query = db_query("select id, name, users_groups from app_ext_funnelchart where id='" . $reports_id. "'");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(in_array($app_user['group_id'],explode(',',$reports_info['users_groups'])) or $app_user['group_id']==0)
						{
							$menu_icon = 'fa-filter';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/funnelchart/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
				case strstr($reports_type,'kanban'):
					if($app_user['group_id']>0)
					{
						$reports_query = db_query("select c.id, c.name from app_ext_kanban c, app_entities e where c.id='" . $reports_id. "' and e.id=c.entities_id and e.parent_id=0 and find_in_set(" . $app_user['group_id'] . ",c.users_groups) order by c.name");
					}
					else
					{
						$reports_query = db_query("select c.id, c.name from app_ext_kanban c, app_entities e where c.id='" . $reports_id. "' and e.id=c.entities_id and e.parent_id=0 order by c.name");
					}
					
					while($reports_info = db_fetch_array($reports_query))
					{						
						$menu_icon = 'fa-th-list';
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/kanban/view','id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
				case strstr($reports_type,'calendarreport'):
					if($app_user['group_id']>0)
					{
						$reports_query = db_query("select c.* from app_ext_calendar c, app_entities e, app_ext_calendar_access ca where c.id='" . $reports_id. "' and e.id=c.entities_id and c.id=ca.calendar_id and ca.access_groups_id='" . db_input($app_user['group_id']) . "' order by c.name");
					}
					else
					{
						$reports_query = db_query("select c.* from app_ext_calendar c, app_entities e where c.id='" . $reports_id. "' and  e.id=c.entities_id order by c.name");
					}
					
					while($reports_info = db_fetch_array($reports_query))
					{						
						$menu_icon = 'fa-calendar';
						$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/calendar/report','id=' . $reports_info['id']),'class'=>$menu_icon);
					}
					break;
					
				case strstr($reports_type,'pivot_calendars'):
					$reports_query = db_query("select * from app_ext_pivot_calendars where id='" . $reports_id . "' order by sort_order, name");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(pivot_calendars::has_access($reports_info['users_groups']))
						{
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/pivot_calendars/view','id=' . $reports_info['id']),'class'=>'fa-calendar');
						}
					}
					break;
				case strstr($reports_type,'image_map'):
					$reports_query = db_query("select id, name, users_groups from app_ext_image_map where id='" . $reports_id. "'");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(image_map::has_access($reports_info['users_groups']))
						{
							$menu_icon = 'fa-picture-o';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/image_map/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
				case strstr($reports_type,'map_reports'):
					$reports_query = db_query("select id, name, users_groups from app_ext_map_reports where id='" . $reports_id. "'");
					while($reports_info = db_fetch_array($reports_query))
					{
						if(map_reports::has_access($reports_info['users_groups']))
						{
							$menu_icon = 'fa-map-marker';
							$sub_menu[] = array('title'=>$reports_info['name'],'url'=>url_for('ext/map_reports/view','id=' . $reports_info['id']),'class'=>$menu_icon);
						}
					}
					break;
					
					
			
			}
		}
		
		return $sub_menu;
	}
}