<?php

class image_map
{
	private $data;
	
	private $map_id;
	
	private $field_info;
	
	private $field_cfg;
	
	private $path;
	
	private $scale;
	
	private $fields_in_popup;
	
	private $reports_id;
	
	private $reports_info;
	
	private $fiters_reports_id;
	
	private $background;
	
	private $choices_in_legend; 
	
	function __construct($map_id)
	{
		$this->map_id = $map_id;
		$this->data = array();
		$this->path = false;
		$this->reports_id = false;
		$this->fiters_reports_id = false;
		$this->scale = 'default';
		$this->fields_in_popup = array();
		$this->background = false;
		$this->field_info = array();
		$this->field_cfg = array();
		$this->choices_in_legend = [];
		
	}
	
	function set_path($path)
	{
		$this->path = $path;
	}
	
	function set_reports_id($reports_id)
	{
		$this->reports_id = $reports_id;
		
		$reports_query = db_query("select * from app_ext_image_map where id='" . db_input($this->reports_id) . "'");
		$this->reports_info = db_fetch_array($reports_query);
	}
	
	function set_fiters_reports_id($fiters_reports_id)
	{
		$this->fiters_reports_id = $fiters_reports_id;
	}
	
	function get_data()
	{
		$map_info_query = db_query("select * from app_fields_choices where id='" . $this->map_id . "'");
		if($map_info = db_fetch_array($map_info_query))
		{
			$this->data['code'] = 1;
			
			$this->get_map($map_info);
						
			$this->get_regions_labels($map_info);			
			
			$this->get_markers();
			
			$this->get_html($map_info);
		}	
		
		return json_encode($this->data);		
	}
	
	function get_map($map_info)
	{
		$this->field_info = db_find('app_fields', $map_info['fields_id']);
		$cfg = $this->field_cfg = new fields_types_cfg($this->field_info['configuration']);
		
		//use report scale
		if($this->reports_id)
		{
			$this->scale = $this->reports_info['scale'];
		}
		else
		{
			$this->scale = $cfg->get('scale');
		}
		
		//set fields in popup
		if(is_array($cfg->get('fields_in_popup')))
			$this->fields_in_popup = $cfg->get('fields_in_popup');
		
		//set background
		if(strlen($cfg->get('background')))
		{
			$this->background = $cfg->get('background');
		}
		
		$filename = DIR_WS_UPLOADS  . 'maps/' .$map_info['id'] . '/' . $map_info['filename'];
		
		$data = getimagesize($filename);
		
		$width = $data[0];
		$height = $data[1];
		
		$this->data['data']['map'] = [
				'id' => $map_info['id'],
				'name' => $map_info['name'],
				'enabled' => 1,
				'showLegend' => 1,
				'zoom' => $this->scale,
				'mapImage' => [				
					'width' => $width,
					'height' => $height
				]
		];
	}
	
	function get_regions_labels($map_info)
	{
		$this->data['data']['regions'] = null;
		$this->data['data']['labels'] = null;
		
		if(!$this->reports_id) return false;
		
		$labels = array();
		$regions = array();
		
		$choices_query = db_query("select * from app_fields_choices where parent_id = '" . db_input($map_info['id']). "' order by sort_order, name");				
		while($choices = db_fetch_array($choices_query))
		{
			$x = $y = 0;
				
			//get possition
			$label_info_query = db_query("select * from app_image_map_labels where choices_id = '" . $choices['id']. "' and map_id='" . $map_info['id'] . "'");
			if($label_info = db_fetch_array($label_info_query))
			{
				$x = $label_info['x'];
				$y = $label_info['y'];
			}
			
			$labels[] = [
					'id' => $choices['id'],
					'x' => $x,
					'y' => $y,
					'clickable' =>true,
					'html' => '<div class="cfm-inner" ><div class="cfm-title"><a href="#">' . $choices['name'] . '</a></div></div>',
					
			];
			
			$regions[] = [
					'id' => $choices['id'],
					'name' => '',
					'mapId' => $map_info['id'],
					'x' => $x,
					'y' => $y,
					'zoom' => 'default',			
			];
		}
		
		if(count($labels))
		{
			$this->data['data']['labels'] = $labels;
			$this->data['data']['regions'] = $regions;
		}
	}
	
	function get_fields_in_popup($items)
	{
		$html = '';
		
		if(count($this->fields_in_popup))
		{
			$html .= '
					<table class="cfm-params">
						<tbody>';
			
			
			foreach($this->fields_in_popup as $fields_id)
			{
				$field_query = db_query("select * from app_fields where id='" . $fields_id . "'");
				if($field = db_fetch_array($field_query))
				{
					//prepare field value
					$value = items::prepare_field_value_by_type($field, $items);
			
					$output_options = array('class'=>$field['type'],
							'value'=>$value,
							'field'=>$field,
							'item'=>$items,
							'is_listing'  => true,
							'path'=>'');
			
					$value = trim(fields_types::output($output_options));
			
					if(strlen(strip_tags($value))>255 and in_array($field['type'],['fieldtype_textarea_wysiwyg','fieldtype_textarea'])) $value = substr(strip_tags($value),0,255) . '...';
			
					if(strlen($value))
					{
						$html .= '
							<tr>
								<td valign="top" style="padding-right: 7px;">' . fields_types::get_option($field['type'],'name',$field['name']) . '</td>
								<td valign="top">' . $value . '</td>
							</tr>';						
					}
				}
			}		
			
			$html .= '
						</tbody>
					</table>
					';
		}
		
		return $html;
	}
	
	function get_markers()
	{
		global $sql_query_having;
		
		$this->data['data']['markers'] = null;
		
		$markers = array();
		
		if($this->path)
		{
			$path_info = items::parse_path($this->path);
			$entity_id = $path_info['entity_id'];
			$item_id = $path_info['item_id'];
				        
	    $items_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($entity_id, '') . " from app_entity_" . $entity_id . " e where id='" . $item_id . "'");	     
		}
		elseif($this->reports_id)
		{	
			/**
			 *start build listing query
			 */		
			
			$reports = $this->reports_info;
			
			$entity_id = $reports['entities_id'];
			
			$listing_sql_query = '';
			$select_sql_query = '';
			$listing_sql_query_having = '';
			$sql_query_having = array();
			
			//add filters query
			$listing_sql_query = reports::add_filters_query($this->fiters_reports_id,$listing_sql_query);
			
			//add filter by map
			$listing_sql_query .= " and e.field_" . $reports['fields_id'] . "=" . $this->map_id;
			
			//add access query
			$listing_sql_query = items::add_access_query($reports['entities_id'],$listing_sql_query);

			
			//prepare fields sum for formulas
			$sql_query_select = fieldtype_formula::prepare_query_select($reports['entities_id'],'');
			
			//prepare having query for formula fields
			if(isset($sql_query_having[$reports['entities_id']]))
			{
				$listing_sql_query .= reports::prepare_filters_having_query($sql_query_having[$reports['entities_id']]);
			}
			
			$listing_sql = "select e.* " . $sql_query_select . " from app_entity_" . $reports['entities_id'] . " e  where e.id>0 " . $listing_sql_query;
			
			$items_query = db_query($listing_sql);						
		}
		
		while($items = db_fetch_array($items_query))
		{
			$x = $y = 0;
			
			//get possition 
			$marker_info_query = db_query("select * from app_image_map_markers where entities_id='" . $entity_id . "' and items_id = '" . $items['id']. "' and map_id='" . $this->map_id . "'");
			if($marker_info = db_fetch_array($marker_info_query))
			{
			  $x = $marker_info['x'];
			  $y = $marker_info['y'];
			}
			
			//prepare bakcground by choice
			$typeCssName = '';
			$icon = 'images/map_marker.png';
			if($this->background)
			{
				if(isset($items['field_' . $this->background]))
				{
					$typeCssName = 'type-' . $items['field_' . $this->background];
					
					//prepare icon 
					if(strlen($icon_filename = $this->field_cfg->get('icon_' . $items['field_' . $this->background])))
					{
						if(is_file($icon_filepath = DIR_WS_UPLOADS . 'icons/' . $icon_filename))
						{
							$icon = $icon_filepath;
						}
					}
				}
			}
			
			//prepare marker
			$markers[] = [
					'id' => $items['id'],
					'x' => $x,
					'y' => $y,
					'typeCssName' => $typeCssName,
					'html' => '
						<div class="cfm-inner">
							<div class="cfm-title-params">
								<span class="cfm-icon"><img src="' . $icon . '" width="24" height="24" /></span>
								<span class="cfm-title"><a href="' . url_for('items/info','path=' . $entity_id . '-' . $items['id']) . '" target="_new">' . items::get_heading_field($entity_id, $items['id'], $items) . '</a></span>
							</div>
							' . $this->get_fields_in_popup($items) . '												
						</div>',
			];
			
			//prepare default region for singel item
			if($this->path)
			{				
				$regions[] = [
						'id' => $items['id'],
						'name' => '',
						'mapId' => $this->map_id,
						'x' => $x,
						'y' => $y,
						'zoom' => $this->scale,
						
				];
				
				$this->data['data']['regions'] = $regions;
			}
			
			
			//prepare choices in legend for reports page
			if($this->reports_id and $this->background)
			{
				$this->choices_in_legend[$items['field_' . $this->background]] = $items['field_' . $this->background];
			}		
			
		}
				
		if(count($markers))
		{
			$this->data['data']['markers'] = $markers;
		}		
	}
	
	function get_html($map_info)
	{
		$legend = '';
		$maps = '';
		$map_choices = '';
		
		//prepare legend
		if($this->background)
		{
			$choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($this->background). "' order by sort_order, name");
			while($choices = db_fetch_array($choices_query))
			{				
				if(in_array($choices['id'],$this->choices_in_legend))
				{
					$legend .= '<li><div class="cfm-marker cfm-marker-type-' . $choices['id'] . '"></div>&nbsp;&nbsp;' . $choices['name'] . '</li>';
				}
			}	
		}
		
		//prepare map dropdown menu for reports
		if($this->reports_id)
		{						
			$choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($this->field_info['id']). "' and parent_id=0 order by sort_order, name");
			if(db_num_rows($choices_query)>1)
			{
				$maps .= '            
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true" aria-expanded="false">				                        
							 ' . $this->field_info['name'] . '              
						<i class="fa fa-angle-down"></i>
						</a>
		                        
		        <ul class="dropdown-menu">';
				
					while($choices = db_fetch_array($choices_query))
					{
						$maps .= '<li><a target="_parent" href="' . url_for('ext/image_map/view','id=' . $this->reports_id . '&map_id=' . $choices['id']) . '">' . $choices['name'] . '</a></li>';
					}
		        
		    $maps .= '
		      </ul>                               
		  		</li>  		  	
					';
			}
		}
		
		//prepare labels
		$choices_query = db_query("select * from app_fields_choices where parent_id = '" . db_input($map_info['id']). "' order by sort_order, name");
		if(db_num_rows($choices_query) and $this->reports_id)
		{
			$map_choices .= '
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true" aria-expanded="false">
							 ' . $map_info['name'] . '
						<i class="fa fa-angle-down"></i>
						</a>
			
		        <ul class="dropdown-menu">';
			
			while($choices = db_fetch_array($choices_query))
			{
				$map_choices .= '<li><a href="#" onclick="return image_map_show_region(' . $map_info['id'] . ','.  $choices['id'] . ')">' . $choices['name'] . '</a></li>';
			}
			
			$map_choices .= '
		      </ul>
		  		</li>
					';
		}
		else
		{
			$map_choices = '<li class="dropdown"><a href="#" onClick="return false">' . $map_info['name'] . '</a></li>';
		}
		
		$breadcrumb = '
				<ul class="nav navbar-nav pull-right">
					' . $map_choices . '
					' . $maps . '
				</ul>
				';
		
		$this->data['data']['viewHtml'] = [
				'breadcrumb' => $breadcrumb,
				'legend' => $legend,
		];
	}
	
	static function save_markers()
	{
		$map_id = _get::int('map_id');
		
		if(isset($_GET['path']))
		{
			$path_info = items::parse_path($_GET['path']);
			$entity_id = $path_info['entity_id'];
		}
		if(isset($_GET['reports_id']))
		{
			$reports_query = db_query("select * from app_ext_image_map where id='" . db_input($_GET['reports_id']) . "'");
			$reports = db_fetch_array($reports_query);
				
			$entity_id = $reports['entities_id'];
		}
		
		foreach($_POST['markers'] as $marker)
		{
			$sql_data = array(
					'x' => $marker['x'],
					'y' => $marker['y']
			);
			
		  $marker_info_query = db_query("select * from app_image_map_markers where entities_id='" . $entity_id . "' and items_id = '" . $marker['id']. "' and map_id='" . $map_id . "'");
		  if($marker_info = db_fetch_array($marker_info_query))
		  {
		  	db_perform('app_image_map_markers',$sql_data,'update',"id='" . db_input($marker_info['id']) . "'");
		  }
		  else
		  {
		  	$sql_data['entities_id'] = $entity_id;
		  	$sql_data['items_id'] = $marker['id'];
		  	$sql_data['map_id'] = $map_id;
		  	
		  	db_perform('app_image_map_markers',$sql_data);
		  }
		}
	}
	
	static function save_labels()
	{
		$map_id = _get::int('map_id');
	
		foreach($_POST['labels'] as $label)
		{
			$sql_data = array(
					'x' => $label['x'],
					'y' => $label['y']
			);
				
			$label_info_query = db_query("select * from app_image_map_labels where choices_id = '" . $label['id']. "' and map_id='" . $map_id . "'");
			if($label_info = db_fetch_array($label_info_query))
			{
				db_perform('app_image_map_labels',$sql_data,'update',"id='" . db_input($label_info['id']) . "'");
			}
			else
			{				
				$sql_data['choices_id'] = $label['id'];
				$sql_data['map_id'] = $map_id;
				 
				db_perform('app_image_map_labels',$sql_data);
			}
		}
	}	
	
	static function delete_markers($entities_id, $items_id)
	{
		db_query("delete from app_image_map_markers where entities_id='" . $entities_id . "' and items_id='" . $items_id . "'");
	}
	
	static function render_markers_color($fields_id)
	{
		$css = '';
		
		$field = db_find('app_fields',$fields_id);
						
		$cfg = new fields_types_cfg($field['configuration']);
		
		if(strlen($cfg->get('background')))
		{
			$choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($cfg->get('background')). "' and length(bg_color)>0 order by sort_order, name");
			while($choices = db_fetch_array($choices_query))
			{
				$css .= '
						.zoom-lt-50 .cfm-marker-type-' . $choices['id'] . ' .cfm-inner,.cfm-legend .cfm-marker-type-' . $choices['id'] . ' {background-color: ' . $choices['bg_color'] . ';}
						.zoom-lt-50 .cfm-marker-type-' . $choices['id'] . ' .cfm-inner:hover,.zoom-lt-50 .cfm-marker-type-' . $choices['id'] . '.cfm-selected .cfm-inner{background-color: ' . $choices['bg_color'] . ';}
						';
			}
		}
		
		if(strlen($css))
		{
			$css = '
					<style>
					' . $css . '
					</style>
					';
		}
		
		return $css;
	}
	
	static function has_access($users_groups,$access=false)
	{
		global $app_user;
		
		if($app_user['group_id']==0) return true;
		
		if(strlen($users_groups))
		{
			$users_groups = json_decode($users_groups,true);
			
			if(!$access)
			{
				if(isset($users_groups[$app_user['group_id']]))
				{
					return (strlen($users_groups[$app_user['group_id']]) ? true : false);
				}
			}
			else
			{
				if(isset($users_groups[$app_user['group_id']]))
				{
					return ($users_groups[$app_user['group_id']]==$access ? true : false);
				}
			}
		}
		
		return false;
	}
	
	static function render_cfm_selected_css()
	{
		return '
			<style>
				.cfm-layer-element .cfm-inner,
				.cfm-layer-element.cfm-label a {
					cursor: pointer
				}
				
				.cfm-layer-element.cfm-selected .cfm-inner {
					outline: inherit
				}
			</style>		
			';
	}
}