<?php

class fieldtype_google_map
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_GOOGLE_MAP_TITLE);
        
  }
  
  function get_configuration()
  {
    $cfg = array();
        
    $cfg[] = array('title'=>TEXT_API_KEY, 'name'=>'api_key','type'=>'input','tooltip'=>TEXT_FIELDTYPE_GOOGLE_MAP_API_KEY_TIP,'params'=>array('class'=>'form-control input-xlarge required'));
    
    $cfg[] = array('title'=>TEXT_ADDRESS . fields::get_available_fields_helper($_POST['entities_id'], 'fields_configuration_address_pattern', TEXT_SELECT_FIELD,['fieldtype_input','fieldtype_input_masked','fieldtype_mysql_query','fieldtype_textarea','fieldtype_textarea_wysiwyg','fieldtype_text_pattern','fieldtype_text_pattern_static']), 
    							 'name'=>'address_pattern','type'=>'input','tooltip'=>TEXT_ADDRESS_PATTERN_INOF,'params'=>array('class'=>'form-control input-xlarge required'));
    
    $cfg[] = array('title'=>TEXT_WIDHT, 'name'=>'map_width','type'=>'input','tooltip_icon'=>TEXT_WIDTH_INPUT_TIP,'params'=>array('class'=>'form-control input-small'));
    $cfg[] = array('title'=>TEXT_HEIGHT, 'name'=>'map_height','type'=>'input','tooltip_icon'=>TEXT_HEIGHT_INPUT_TIP,'params'=>array('class'=>'form-control input-small'));
    
    $choices = [];
    for($i=3;$i<=20;$i++)
    {
    	$choices[$i] = $i;
    }
    
    $cfg[] = array('title'=>TEXT_DEFAULT_ZOOM,
    		'name'=>'zoom',
    		'type'=>'dropdown',
    		'choices'=>$choices,
    		'default'=>11,
    		'params'=>array('class'=>'form-control input-small'));
    
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {               
    return false;
  }
  
  function process($options)
  {
    return db_prepare_input($options['value']);
  }
  
  function output($options)
  {
  	global $is_google_map_script;
  	
  	$cfg = new fields_types_cfg($options['field']['configuration']);
  	  	 
  	//skip
  	if(!strlen($cfg->get('address_pattern')) or !strlen($options['value']) or isset($options['is_listing']) or isset($options['is_export'])) return '';
    	    	  	  	  	 
  	$value = explode("\t",$options['value']);
  		  		  		
  	$lat = $value[0];
  	$lng = $value[1];
  	$current_address = $value[2];
 		
  	  	  	  	  		  
  	if(strlen($lat) and strlen($lng))
  	{  	
  		$html = '';
  		
  		if($is_google_map_script!=true)
  		{
  			$html .= '<script src="https://maps.googleapis.com/maps/api/js?key=' . $cfg->get('api_key') . '"></script>';
  			$is_google_map_script = true;
  		}
  		
  		$field_id = $options['field']['id'];
  		
  		$access_rules = new access_rules($options['field']['entities_id'], $options['item']);
  		$has_update_access = users::has_access('update',$access_rules->get_access_schema());
  		
  		$html .='
  				
  				<script>
					  				
  					$(function(){
  						  				
						  var mapOptions = {
						    zoom: ' . $cfg->get('zoom') . ',    
						  }
						  
						  var map = new google.maps.Map(document.getElementById("goolge_map_container' . $field_id . '"), mapOptions);
						  
						  geocoder = new google.maps.Geocoder();
						
						  var myLatlng = new google.maps.LatLng(' . $lat . ',' . $lng. ');
                         
			        //Got result, center the map and put it out there
			        map.setCenter(myLatlng);
			                            
			        var marker = new google.maps.Marker({
			            map: map,
			            position: myLatlng,
						  		draggable: ' . ($has_update_access ? 'true':'false') . '
			        });
						  		
						  var infowindow = new google.maps.InfoWindow();
						  		
						  google.maps.event.addListener(marker, "click", function() {
			          infowindow.close();//hide the infowindow
			          infowindow.setContent(\'<div id="content">' . str_replace(array("\n","\r","\n\r"),' ',nl2br(urldecode($current_address))) . '</div>\');
			          infowindow.open(map,marker);
			        });	
			          		
			        google.maps.event.addListener(marker, "dragend", function(evt){			        		
			          		$.ajax({
										  method: "POST",
										  url: "' . url_for('items/google_map','path=' . $options['path'] . '&action=update_latlng'). '",
										  data: { lat: evt.latLng.lat(), lng: evt.latLng.lng(),filed_id: ' . $field_id . ' } 
										})
							});
						  		
						})
																		
						</script>  
					';	
			          		
  		$map_width = (strlen($cfg->get('map_width')) ? $cfg->get('map_width') : '470px');
  		$map_height = (strlen($cfg->get('map_height')) ? $cfg->get('map_height') : '470px');
  		
  		if(!strstr($map_width,'%') and !strstr($map_width,'px')) $map_width = $map_width . 'px'; 
  		if(!strstr($map_height,'%') and !strstr($map_height,'px')) $map_height = $map_height . 'px';
  		
			$html .='			
						<div id="goolge_map_container' . $field_id . '" style="width:100%; max-width: ' . $map_width . '; height: ' . $map_height . ';"></div> 
  				';
  		  		  		
  		return $html;
  	}
  	else
  	{
    	return '';
  	}
  }
  
  public static function update_items_fields($entities_id, $items_id)
  {
  	global $app_fields_cache, $alerts;
  
  	if(isset($app_fields_cache[$entities_id]))
  	{
  		foreach($app_fields_cache[$entities_id] as $fields)
  		{
  			if($fields['type']=='fieldtype_google_map')
  			{
  				$fields_id = $fields['id'];
  				
  				$cfg = new fields_types_cfg($fields['configuration']);
  				
  				//skip if no pattern setup
  				if(!strlen($cfg->get('address_pattern'))) return false;
    				
  				//get item info
  				$item_info_query = db_query("select * from app_entity_{$entities_id} where id={$items_id}");
  				$item_info = db_fetch_array($item_info_query);
  				
  				//get address by pattern
  				$pattern_options = array(
  						'field'=>$fields,
  						'item'=>$item_info,
  						'custom_pattern'=>$cfg->get('address_pattern'),
  						'path' => $entities_id . '-' . $items_id,
  				);
  				 
  				$fieldtype_text_pattern = new fieldtype_text_pattern;
  				$use_address = urlencode(strip_tags($fieldtype_text_pattern->output($pattern_options)));
  				  				  				
  				//skip if address empty
  				if(!strlen($use_address))
  				{
  					db_query("update app_entity_{$entities_id} set field_{$fields_id}='' where id='" . db_input($items_id) . "'");
  					
  					return false;
  				}
  				  				  				
  				$lat = '';
  				$lng = '';
  				$current_address = '';
  				 
  				//get current address
  				if(strlen($item_info['field_' . $fields_id]))
  				{
  					$value = explode("\t",$item_info['field_' . $fields_id]);
  				
  					$lat = $value[0];
  					$lng = $value[1];
  					$current_address = $value[2];
  				}
  				   				
  				//update address if it needs
  				if(!strlen($lat) or $use_address!=$current_address)
  				{
  					$url = "https://maps.google.com/maps/api/geocode/json?key=" . $cfg->get('api_key') . "&address=" . $use_address;
  				
  					$ch = curl_init($url);
  					curl_setopt($ch, CURLOPT_HEADER, false);
  					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  					$result = curl_exec($ch);
  					curl_close($ch);
  				
  				
  					$result = json_decode($result,true);
  				
  					//print_rr($result);
  				
  					if(isset($result['error_message']))
  					{
  						$alerts->add(TEXT_FIELD . ' "' . $fields['name'] . '": ' . $result['error_message'],'error');  						
  					}
  					else
  					{
  						$lat = $result['results'][0]['geometry']['location']['lat'];
  						$lng = $result['results'][0]['geometry']['location']['lng'];
  							
  						$value = $lat . "\t" . $lng . "\t" . $use_address;
  							
  						//echo $value;
  						
  						db_query("update app_entity_{$entities_id} set field_{$fields_id}='" . db_input($value) . "' where id='" . db_input($items_id) . "'");  				  						
  					}
  				}
  				  				  
  			}
  		}
  	}
  }
  
}