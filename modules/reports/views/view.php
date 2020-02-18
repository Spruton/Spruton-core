<?php
  app_reset_selected_items();
   
  $listing_container = 'entity_items_listing' . (isset($force_filters_reports_id) ? $force_filters_reports_id : $reports_info['id']) . '_' . $reports_info['entities_id'];
      
  //check if parent reports was not set
  if($entity_info['parent_id']>0 and $reports_info['parent_id']==0)
  {
    reports::auto_create_parent_reports($reports_info['id']);
  }
  
  //get report entity access schema
  $access_schema = users::get_entities_access_schema($reports_info['entities_id'],$app_user['group_id']);
  
  $user_access_schema = users::get_entities_access_schema(1,$app_user['group_id']);
  
  
  if($reports_info['reports_type']=='entity_menu')
  {
  	$help_pages = new help_pages($reports_info['entities_id']);
  	
  	if(is_ext_installed())
  	{
  		$common_filters = new common_filters($reports_info['entities_id'],$reports_info['id']);  		
  		echo $common_filters->render($page_title . $help_pages->render_icon('listing'));
  	}
  	else
  	{  	
  		echo '<h3 class="page-title">' . $page_title . $help_pages->render_icon('listing') . '</h3>';
  	}
  	
  	echo $help_pages->render_announcements();
  }
  else
  {
  	echo '<h3 class="page-title">' . $page_title . '</h3>';
  }

if($reports_info['reports_type']!='common')
{
	if($reports_info['reports_type']=='entity_menu')
	{
		if(filters_preivew::has_default_panel_access($entity_cfg))
		{
			$filters_preivew = new filters_preivew($reports_info['id']);
			
			if(!in_array($app_user['group_id'],explode(',',$entity_cfg->get('listing_config_access'))) and strlen($entity_cfg->get('listing_config_access')))
			{
				$filters_preivew->has_listing_configuration = false;
			}
			
			echo $filters_preivew->render();
		}
		
		$filters_panels = new filters_panels($reports_info['entities_id'],$reports_info['id'],$listing_container);
		echo $filters_panels->render_horizontal();
	}
	else
	{
  	$filters_preivew = new filters_preivew($reports_info['id']);
  	echo $filters_preivew->render();
	}
}    
?>

<div class="row">
  <div class="col-sm-5">
    <div class="entitly-listing-buttons-left">
    
      <?php
      
      	$reports_hide_insert_button = ($reports_info['reports_type']=='entity_menu' ? false : $entity_cfg->get('reports_hide_insert_button'));
      	
        if(users::has_access('create',$access_schema) and $reports_hide_insert_button!=1)
        {         	
          if($entity_info['parent_id']==0)
          {
            $url = url_for('items/form','path=' . $reports_info['entities_id'] . '&redirect_to=report_' . $reports_info['id']); 
          }
          elseif($entity_info['parent_id']==1 and !users::has_access('view',$user_access_schema))
          {          	
          	$url = url_for('items/form','path=1-' . $app_user['id'] . '/' . $reports_info['entities_id'] . '&redirect_to=report_' . $reports_info['id']);
          }
          else
          {
            $url = url_for('reports/prepare_add_item','reports_id=' . $reports_info['id']);
          }
          echo button_tag((strlen($entity_cfg->get('insert_button'))>0 ? $entity_cfg->get('insert_button') : TEXT_ADD), $url) . ' ';
        } 
      ?>


<?php 
	$with_selected_menu = '';
	
	if(users::has_access('export_selected',$access_schema) and users::has_access('export',$access_schema))
	{
		$with_selected_menu .= '<li>' . link_to_modalbox('<i class="fa fa-file-excel-o"></i> ' . TEXT_EXPORT,url_for('items/export','path=' . $reports_info["entities_id"]  . '&reports_id=' . $reports_info['id'] )) . '</li>';
	}

	if(class_exists('processes'))
	{
		$processes = new processes($reports_info['entities_id']);
		$processes->rdirect_to = 'reports';
		$with_selected_menu .=  $processes->render_buttons('menu_with_selected',$reports_info['id']);
	}

	
  $with_selected_menu .=  plugins::render_simple_menu_items('with_selected');
	
  
  if( users::has_access('delete',$access_schema) and users::has_access('delete_selected',$access_schema) and $reports_info['entities_id']!=1)
  {
  	$with_selected_menu .=  '<li>' . link_to_modalbox('<i class="fa fa-trash-o"></i> ' . TEXT_BUTTON_DELETE,url_for('items/delete_selected', 'redirect_to=report_' . $reports_info['id'] . '&path=' . $reports_info['entities_id'] . '&reports_id=' . $reports_info['id'] )) . '</li>';
  }
  
  $listing = new items_listing($reports_info['id']);
  $curren_listing_type = $listing->get_listing_type();
    
if(strlen($with_selected_menu))
{
?>      
      <div class="btn-group">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">
				<?php echo TEXT_WITH_SELECTED ?> <i class="fa fa-angle-down"></i>
				</button>
				<ul class="dropdown-menu" role="menu">
					<?php echo $with_selected_menu ?>          
				</ul>
			</div>
<?php 
		if(in_array($curren_listing_type,['grid','mobile']))
		{
			if(listing_types::has_action_field($curren_listing_type,$reports_info['entities_id']))
			{
				echo '
			  			<label>' . input_checkbox_tag('select_all_items',$reports_info['id'],array('class'=>$listing_container . '_select_all_items_force','data-container-id'=>$listing_container))  . ' ' . TEXT_SELECT_ALL. '</label>
		  			';
			}
		}
	} 	
?>
    </div>
  </div>
  <div class="col-sm-2">
  	<?php echo (($reports_info['reports_type']!='common' and !isset($force_filters_reports_id)) ? listing_types::render_switches($reports_info,$curren_listing_type):'') ?>
  </div>
  <div class="col-sm-5">
    <div class="entitly-listing-buttons-right">    
      <?php echo render_listing_search_form($reports_info["entities_id"],$listing_container,$reports_info['id']) ?>
      
      <?php 
      	if(!filters_preivew::has_default_panel_access($entity_cfg) and $reports_info['reports_type']=='entity_menu' and (in_array($app_user['group_id'],explode(',',$entity_cfg->get('listing_config_access'))) or !strlen($entity_cfg->get('listing_config_access'))))
      	{
      		$html = '
			      <div class="btn-group" style="float:right">
							<a class="btn dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown">
							<i class="fa fa-gear"></i></i>
							</a>
							<ul class="dropdown-menu pull-right">
								<li>
			            ' . link_to_modalbox('<i class="fa fa-sort-amount-asc"></i> ' . TEXT_HEADING_REPORTS_SORTING,url_for('reports/sorting','reports_id=' . $reports_info['id'] . '&redirect_to=listing' . (isset($_GET['app_path'])>0 ? '&path=' . $app_path : '') )). '
			            ' . link_to_modalbox('<i class="fa fa-wrench"></i> ' . TEXT_NAV_LISTING_CONFIG,url_for('reports/configure','reports_id=' . $reports_info['id'] . '&redirect_to=listing'  . (isset($_GET['app_path']) ? '&path=' . $app_path : '') )) . '
			      		
								</li>
							</ul>
						</div>
			    ';
      		
      		echo $html;
      	}
      ?>  
                  
    </div>                    
  </div>
</div> 


<?php if(isset($filters_panels)) { ?>

<div class="row">
  <div class="col-xs-<?php echo (12-$filters_panels->vertical_width)?>">
    <div id="<?php echo $listing_container ?>" class="entity_items_listing"></div>
  </div>
  
  <?php echo $filters_panels->render_vertical(); ?>
</div>

<?php }else{ ?>

<div class="row">
  <div class="col-xs-12">
    <div id="<?php echo $listing_container;  ?>" class="entity_items_listing"></div>
  </div>
</div>
<?php } ?>

<?php echo input_hidden_tag($listing_container . '_order_fields',$reports_info['listing_order_fields']) ?>
<?php echo input_hidden_tag($listing_container . '_has_with_selected',(strlen($with_selected_menu) ? 1:0)) ?>

<?php require(component_path('items/load_items_listing.js')); ?>

<?php 
	$gotopage = 1;
	if(isset($_GET['gotopage'][$reports_info['id']]))
	{
		$gotopage = (int)$_GET['gotopage'][$reports_info['id']];
	}
	elseif(isset($listing_page_keeper[$reports_info['id']]))
	{
		$gotopage = $listing_page_keeper[$reports_info['id']];
		unset($listing_page_keeper[$reports_info['id']]);
	}	
?>

<script>  
  $(function() {     
    load_items_listing('<?php echo $listing_container  ?>',<?php echo $gotopage ?>);                                                                         
  });          
</script> 

