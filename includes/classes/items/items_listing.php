<?php

class items_listing
{
  private $fields_in_listing;
  
  private $entities_id;
  
  public $rows_per_page;
  
  public $force_access_query;
  
  public $report_type;
  
  public $entity_cfg;
  
  public $reports_info;
  
  function __construct($reports_id, $entity_cfg=false)
  {
    $reports_info = db_find('app_reports',$reports_id);
    
    $this->reports_info = $reports_info;
    
    $this->fields_in_listing = $reports_info['fields_in_listing'];
    
    $this->entities_id = $reports_info['entities_id'];  
    
    $this->rows_per_page = $reports_info['rows_per_page'];
    
    $this->force_access_query = $reports_info['displays_assigned_only'];
    
    $this->report_type = $reports_info['reports_type'];
    
    //set listing type
    $choices = listing_types::get_choices($this->entities_id);    
    $this->listing_type = (isset($choices[$reports_info['listing_type']]) ? $reports_info['listing_type']:'');    
    
    if(!$entity_cfg)
    {
    	$this->entity_cfg = new entities_cfg($reports_info['entities_id']);
    }
    else
    {
    	$this->entity_cfg = $entity_cfg;
    }
  }
  
  function get_fields_query()
  {
    if(strlen($this->fields_in_listing)>0)
    {
      $sql = "select f.*,if(length(f.short_name)>0,f.short_name,f.name) as name, f.name as long_name  from app_fields f where f.id in (" . $this->fields_in_listing . ") and  f.entities_id='" . db_input($this->entities_id) . "' order by field(f.id," . $this->fields_in_listing . ")";  
    }
    else
    {
      $sql = "select f.*,if(length(f.short_name)>0,f.short_name,f.name) as name, f.name as long_name  from app_fields f where f.listing_status=1 and  f.entities_id='" . db_input($this->entities_id) . "' order by f.listing_sort_order, f.name";
    }
    
    return $sql;
  }
  
  function get_listing_type()
  {  	  	
  	if(is_mobile())
  	{  		
  		if(listing_types::has_mobile($this->entities_id))
  		{  			
  			return 'mobile';
  		}
  	}
  	
  	return (strlen($this->listing_type) ? $this->listing_type : listing_types::get_default($this->entities_id));
  }
  
  function get_listing_type_info($type)
  {
  	$listing_type = [];
  	$sections = [];
  	
  	$listing_type_query = db_query("select * from app_listing_types where entities_id='" . $this->entities_id . "' and type='" . $type . "'");
  	if($listing_type = db_fetch_array($listing_type_query))
  	{
  		$listing_sections_query = db_query("select * from app_listing_sections where listing_types_id={$listing_type['id']} order by sort_order, name");
  		while($listing_sections = db_fetch_array($listing_sections_query))
  		{
  			$choices = [];
  			if(strlen($listing_sections['fields']))
  			{  				
  				$fields_query = db_query("select *,if(length(short_name)>0,short_name,name) as name, name as long_name  from app_fields where id in (" . $listing_sections['fields'] . ") order by field(id," . $listing_sections['fields'] . ")");
  				while($fields = db_fetch_array($fields_query))
  				{
  					$choices[] = $fields;
  				}  				   				
  			}
  			
  			$sections[] = [
  					'name' => $listing_sections['name'],
  					'display_field_names' => $listing_sections['display_field_names'],
  					'display_as' => $listing_sections['display_as'],
  					'width' => $listing_sections['width'],
  					'align' => $listing_sections['text_align'],  					
  					'fields' => $choices,
  			];
  		}
  		
  		$listing_type = [
  				'width' => $listing_type['width'], 
  				'sections' => $sections,
  		];
  	}
  	
  	return $listing_type;
  }
  
  function is_resizable()
  {  	    	
  	return (int)$this->entity_cfg->get('change_col_width_in_listing');
  }
  
  function get_listing_col_width($field_id)
  {
  	global $app_fields_cache;
  	  	  
  	if(!$this->is_resizable()) return '';
  	
  	if(strlen($this->reports_info['listing_col_width']))
  	{
  		$listing_col_width = json_decode($this->reports_info['listing_col_width'],true);
  		
  		if(isset($listing_col_width[$field_id]))
  		{
  			return 'style="width:' . $listing_col_width[$field_id] . 'px"; data-resizable-width="' . $listing_col_width[$field_id] . '"';
  		}
  		elseif($app_fields_cache[$this->entities_id][$field_id]['type']!='fieldtype_action')
  		{
  			$atuo_widht = strlen($app_fields_cache[$this->entities_id][$field_id]['name'])*15;
  			$atuo_widht = ($atuo_widht>0 ? $atuo_widht:220);
  			return 'style="width:' . $atuo_widht . 'px"; data-resizable-width="' . $atuo_widht . '"';
  		}
  	}
  	elseif(strlen($app_fields_cache[$this->entities_id][$field_id]['type']!='fieldtype_action'))
  	{  		  		
  		$atuo_widht = strlen($app_fields_cache[$this->entities_id][$field_id]['name'])*10;
  		$atuo_widht = ($atuo_widht>0 ? $atuo_widht:160);
  		return 'style="min-width:' . $atuo_widht . 'px"; data-resizable-width="' . $atuo_widht . '"';
  	}
  }
  
  function resizable_table_widht()
  {
  	if(!$this->is_resizable()) return '';
  	
  	$html = '';
  	if(strlen($this->reports_info['listing_col_width']))
  	{
  		$listing_col_width = json_decode($this->reports_info['listing_col_width'],true);
  		
  		$total_width = 0;
  		foreach($listing_col_width as $width)
  		{
  			$total_width+=$width;
  		}
  		
  		$html = 'data-has-resizable-width="1" data-resizable-width="' . $total_width . '" style="width: ' . $total_width . 'px !important"';
  	}
  	
  	return $html;
  }
}