<?php

class fieldtype_signature
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_SIGNATURE_TITLE);
  }
  
  function get_configuration($params = array())
  {
  	  	
  	$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_DESCRIPTION, 'name'=>'signature_description','type'=>'textarea','params'=>array('class'=>'form-control textarea-small'));
  	$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_WIDTH_IN_ITEM_PAGE, 'name'=>'signature_width_item_page','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_WIDTH_IN_ITEM_PAGE_INFO);
  	$cfg[TEXT_SETTINGS][] = array('title'=>TEXT_WIDTH_IN_PRINT_PAGE, 'name'=>'signature_width_print_page','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_WIDTH_IN_PRINT_PAGE_INFO);
  	  	  	  	
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_BUTTON_TITLE, 'name'=>'button_title','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip_icon'=>TEXT_DEFAULT . ': ' . TEXT_APPROVE);
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_ICON, 'name'=>'button_icon','type'=>'input','params'=>array('class'=>'form-control input-medium'),'tooltip'=>TEXT_MENU_ICON_TITLE_TOOLTIP);
    $cfg[TEXT_BUTTON][] = array('title'=>TEXT_COLOR, 'name'=>'button_color','type'=>'colorpicker');
    
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_ADD_COMMENT, 'name'=>'add_comment','type'=>'dropdown','choices'=>['0'=>TEXT_NO,'1'=>TEXT_YES],'params'=>array('class'=>'form-control input-small'));
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_COMMENT_TEXT, 'name'=>'comment_text','type'=>'textarea','params'=>array('class'=>'form-control textarea-small'),'tooltip_icon'=>TEXT_DEFAULT . ': ' . TEXT_APPROVED);
     
    
    $choices = [];
    $choices[0] = '';
    
    if(is_ext_installed())
    {
    	$processes_query = db_query("select id, name from app_ext_processes where entities_id='" . $params['entities_id'] . "' order by sort_order, name");
    	while($processes = db_fetch_array($processes_query))
    	{
    		$choices[$processes['id']] = $processes['name'];
    	}
    }
    
    $cfg[TEXT_ACTION][] = array('title'=>TEXT_ALL_USERS_APPROVED, 'name'=>'run_process','type'=>'dropdown','choices'=>$choices,'params'=>array('class'=>'form-control input-large'),'tooltip'=>TEXT_ALL_USERS_APPROVED_INFO);
            
    return $cfg;
  }  
  
  
  function render($field,$obj,$params = array())
  {
    return false;    
  }
  
  function process($options)
  {
    return false;
  }
  
  function output($options)
  {
    global $app_users_cache, $app_user, $app_path, $app_module_path;
    
       
    $cfg = new fields_types_cfg($options['field']['configuration']);
     
    //print_rr($options);
    
    if(isset($options['is_print']))
    {
    	$html = '';
    	 
    	if(strlen($options['value']))
			{
					
				$html .= '<td>' . $options['value'] . '</td>';
				
				$approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);
				
				if(count($approved_users))
				{
					
					$approved_users = current($approved_users);
					
					if(strlen($approved_users['signature']))
        	{         		
        		$html .= '<td><img src="' . $approved_users['signature'] . '" width="' . (strlen($cfg->get('signature_width_print_page')) ? (int)$cfg->get('signature_width_print_page') : 150) . '"></td>';
        	}
				}
													
			
			}
    	
    	if(strlen($html))
    	{
    		$html = '
      			<table>
      				<tr>' . $html . '</tr>
      			</table>
      			';
    	}
    	
    	return $html;
    }	
    elseif(isset($options['is_export']) or isset($options['is_email']) or isset($options['is_comments_listing']))
    {
      return $options['value'];
    }
    else
    {
    	$html = '';
    	    	    	
			if(!strlen($options['value']) and $this->check_button_filter($options))
			{
				
				$button_title = (strlen($cfg->get('button_icon')) ? app_render_icon($cfg->get('button_icon')) . ' ' : '') . (strlen($cfg->get('button_title')) ? $cfg->get('button_title') : TEXT_APPROVE);
				
				$btn_css = 'btn-color-' . $options['field']['id'];
				
				$path_info = items::get_path_info($options['field']['entities_id'], $options['item']['id'],$options['item']);
				
				
				$redirect_to = '&redirect_to=items';
				
				if(isset($options['redirect_to']))
				{
					if(strlen($options['redirect_to'])>0)
					{
						$redirect_to = '&redirect_to=' . $options['redirect_to'];
					}
				}
				elseif($app_module_path=='items/info')
				{
					$redirect_to = '&redirect_to=items_info';
				}
				
				//print_rr($options);					
				
				$redirect_to .= (isset($_POST['page']) ? '&gotopage[' . $options['reports_id'] . ']=' . $_POST['page'] :'');
				
				$button_html = button_tag($button_title,url_for('items/signature_field','fields_id=' . $options['field']['id'] . '&path=' . $path_info['full_path'] . $redirect_to),true,['class'=>'btn btn-primary btn-sm ' . $btn_css]);
				
				$html .= '<div style="padding-top: 5px;">' . $button_html . app_button_color_css($cfg->get('button_color'), $btn_css) . '</div>';
			}
			elseif(strlen($options['value']))
			{
				$html .= '<div id="signature_info_' . $options['field']['id'] . '_' . $options['item']['id'] . '">';
				$approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);
				
				if(count($approved_users))
				{
					$approved_users = current($approved_users);
					
						if(strlen($approved_users['signature']))
        		{        			        			
        			$html .= '<img src="' . $approved_users['signature'] . '" width="' . (strlen($cfg->get('signature_width_item_page')) ? (int)$cfg->get('signature_width_item_page') : 150) . '">';
        		}
				}
				
				$html .= '
							<div> ' . $options['value'] . ' <a href="javascript: remove_signature_' . $options['field']['id']. '_' . $options['item']['id'] . '()" title="' . TEXT_DELETE . '"><i class="fa fa-trash-o"></i></a></div>
						</div>
									
				<script>
					function remove_signature_' . $options['field']['id']. '_' . $options['item']['id'] . '()
					{
						if(confirm("' . addslashes(TEXT_ARE_YOU_SURE). '"))
						{		
							$("#signature_info_' . $options['field']['id'] . '_' . $options['item']['id'] . '").hide();
							$.ajax({method:"POST",url:"' . url_for('items/signature_field','action=cancel_singature&fields_id=' . $options['field']['id'] . '&path=' . $options['field']['entities_id'] . '-'. $options['item']['id']) . '"})
						}
					}
				</script>					
				';
					
			
			}
      
      
      return $html;
    }
  }  
  
  function check_button_filter($options)
  {
  	global $sql_query_having;
  	
  	$field_id = $options['field']['id'];
  	$entities_id = $options['field']['entities_id'];
  	  	
  	$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($entities_id). "' and reports_type='fieldfilter" . $field_id . "'");
  	if($reports_info = db_fetch_array($reports_info_query))
  	{
  		$reports_fileds = [];
  		$filtes_query = db_query("select fields_id from app_reports_filters where reports_id='" . $reports_info['id'] . "'");
  		while($filtes = db_fetch_array($filtes_query))
  		{
  			$reports_fileds[] = $filtes['fields_id'];
  		}
  		  		  		
  		$listing_sql_query = "e.id='" . $options['item']['id'] . "'";  		  	
  		$listing_sql_query_having = '';
  		  		
  		$listing_sql_select = fieldtype_formula::prepare_query_select($reports_info['entities_id'],'',false,['fields_in_query'=>implode(',',$reports_fileds)]);
  			
  		$listing_sql_query = reports::add_filters_query($reports_info['id'],$listing_sql_query);
  		 
  		//prepare having query for formula fields
  		if(isset($sql_query_having[$reports_info['entities_id']]))
  		{
  			$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$reports_info['entities_id']]);
  		}
  		
  		$listing_sql = "select  e.* " . $listing_sql_select . " from app_entity_" . $reports_info['entities_id'] . " e where " . $listing_sql_query . $listing_sql_query_having;
  		$items_query = db_query($listing_sql, false);
  		if($item = db_fetch_array($items_query))
  		{
  			return true;
  		}
  		else
  		{
  			return false;
  		}
  		 
  	}
  	
  	return true;
  }
  
  function reports_query($options)
  {  	  	
  	global $app_user;
  	
    $filters = $options['filters'];
    $sql_query = $options['sql_query'];
           
  	if(strlen($filters['filters_values'])>0)
    {  
    	$filters['filters_values'] = str_replace('current_user_id',$app_user['id'],$filters['filters_values']);
    	
      $sql_query[] = "(select count(*) from app_entity_" . $options['entities_id'] . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($options['filters']['fields_id'])  . "' and cv.value in (" . $filters['filters_values'] . ")) " . ($filters['filters_condition']=='include' ? '>0': '=0');
    }
                
    return $sql_query;
  }  
}