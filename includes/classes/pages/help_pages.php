<?php
class help_pages
{
	public $entities_id;

	function __construct($entities_id)
	{
		$this->entities_id = $entities_id;
	}
			
	function render_announcements()
	{
		global $app_user;
		
		$html = '';
		
		$where_sql = " and ((FROM_UNIXTIME(start_date,'%Y-%m-%d')<=date_format(now(),'%Y-%m-%d') or start_date=0) and (FROM_UNIXTIME(end_date,'%Y-%m-%d')>=date_format(now(),'%Y-%m-%d') or end_date=0))";
		
		$pages_query = db_query("select * from app_help_pages where type='announcement' and entities_id='" . $this->entities_id . "' and find_in_set(" . $app_user['group_id'] . ", users_groups) and is_active=1 {$where_sql} order by sort_order, name");
		while($pages = db_fetch_array($pages_query))
		{			
			if($pages['color']=='default')
			{
				$html .= '
						<div>
							<p>' . (strlen($pages['icon']) ? app_render_icon($pages['icon']) . ' ':'') . (strlen($pages['name']) ? '<b>' . $pages['name'] . '</b><br>': '') . $pages['description'] . '</p>
						</div>';
			}
			else
			{
				$html .= '
						<div class="alert alert-' . $pages['color'] . '">' . (strlen($pages['icon']) ? app_render_icon($pages['icon']) . ' ':'') . (strlen($pages['name']) ? '<b>' . $pages['name'] . '</b><br>': '') . $pages['description'] . '</div>';
			}
		}
		
		return '<div class="help-pages-announcement">' . $html . '</div>';
	}
	
	function render_icon($position)
	{
		global $app_user;
		
		$html = '';
		
		$pages_array = [];
		$pages_query = db_query("select * from app_help_pages where type='page' and position='" . $position . "' and entities_id='" . $this->entities_id . "' and find_in_set(" . $app_user['group_id'] . ", users_groups) and is_active=1 order by sort_order, name");
		while($pages = db_fetch_array($pages_query))
		{			
			$pages_array[$pages['id']] = $pages['name'];			
		}
		
		if(count($pages_array)==1)
		{
			$html = '&nbsp;<a title="' . TEXT_HELP . '" class="help-icon" href="javascript: open_dialog(\'' . url_for('help_system/page','id=' . key($pages_array)). '\')"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
		}	
		elseif(count($pages_array)>1)
		{
			foreach($pages_array as $id=>$name)
			{
				$html .= '<li><a href="javascript: open_dialog(\'' . url_for('help_system/page','id=' . $id). '\')">' . $name . '</a></li>';
			}
			
			$html = '
					<div class="btn-group btn-group-help-icon">
					<a title="' . TEXT_HELP . '" class="help-icon" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-question-circle" aria-hidden="false"></i></a>
					<ul class="dropdown-menu ' . ($position=='info' ? 'pull-right':'') . '">
				    ' . $html . '
				  </ul>
					</div>
					';
		}
					
		return $html;
	}
	
	static function get_position_choices()
	{
		$choices = array(
				'listing' => TEXT_ITEMS_LISTING,
				'info' => TEXT_ITEM_DETAILS_POSITION,				
		);
		
		return $choices;
	}
	
	static function get_position_by_name($name)
	{
		$types = self::get_position_choices();
	
		return (isset($types[$name]) ? $types[$name] : '');
	}
	
	static function get_color_choices()
	{
		$choices = array(
				'default' => TEXT_DEFAULT,
				'warning' => TEXT_ALERT_WARNING,
				'danger' => TEXT_ALERT_DANGER,
				'success' => TEXT_ALERT_SUCCESS,
				'info' => TEXT_ALERT_INFO,
		);

		return $choices;
	}

	static function get_color_by_name($name)
	{
		$types = self::get_color_choices();

		return (isset($types[$name]) ? $types[$name] : '');
	}
}