
<?php require(component_path('items/navigation')) ?>

<?php 
  app_reset_selected_items();
   
  $listing_container = 'entity_items_listing' . $reports_info['id'] . '_' . $reports_info['entities_id'];
  
  echo input_hidden_tag('entity_items_listing_path',$_GET['path']); 
?>

<?php 
if(filters_preivew::has_default_panel_access($entity_cfg))
{
  $filters_preivew = new filters_preivew($reports_info['id'],false);
  $filters_preivew->path = $current_path;
  $filters_preivew->redirect_to = 'listing';
  
  if(!in_array($app_user['group_id'],explode(',',$entity_cfg->get('listing_config_access'))) and strlen($entity_cfg->get('listing_config_access')))
  {
  	$filters_preivew->has_listing_configuration = false;
  }
  
  echo $filters_preivew->render();
}  

//print_rr($reports_info);

$filters_panels = new filters_panels($current_entity_id,$reports_info['id'],$listing_container,$parent_entity_item_id);
echo $filters_panels->render_horizontal();

?>

<div class="row">
  <div class="col-sm-5">
    <div class="entitly-listing-buttons-left">
      <?php if(users::has_access('create') and access_rules::has_add_buttons_access($current_entity_id,$parent_entity_item_id)) echo button_tag((strlen($entity_cfg->get('insert_button'))>0 ? $entity_cfg->get('insert_button') : TEXT_ADD), url_for('items/form','path=' . $_GET['path'])) . ' '; ?>
      
<?php

$with_selected_menu = '';
if(users::has_access('export_selected') and users::has_access('export'))
{	
	$with_selected_menu .= '<li>' . link_to_modalbox('<i class="fa fa-file-excel-o"></i> ' . TEXT_EXPORT,url_for('items/export','path=' . $_GET['path'] . '&reports_id=' . $reports_info['id'] )) . '</li>';
}
		
if(is_ext_installed())
{
	$processes = new processes($reports_info['entities_id']);
	$processes->rdirect_to = 'items';
	$with_selected_menu .=  $processes->render_buttons('menu_with_selected',$reports_info['id']);
}

	
$with_selected_menu .=  plugins::render_simple_menu_items('with_selected','&reports_id=' . $reports_info['id']);
  
 
if(users::has_access('delete') and users::has_access('delete_selected') and $current_entity_id!=1)
{
	$with_selected_menu .=  '<li>' . link_to_modalbox('<i class="fa fa-trash-o"></i> ' . TEXT_BUTTON_DELETE,url_for('items/delete_selected','path=' . $_GET['path'] . '&reports_id=' . $reports_info['id'] )) . '</li>';
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

if(users::has_access('import'))
{
	echo  link_to_modalbox('<i class="fa fa-upload"></i>',url_for('items/import','path=' . $_GET['path']), array('class'=>'btn btn-default','title'=>TEXT_IMPORT));
}
?>
    </div>
  </div>
  <div class="col-sm-2">
  	<?php echo listing_types::render_switches($reports_info,$curren_listing_type) ?>
  </div>
  <div class="col-sm-5">
    <div class="entitly-listing-buttons-right1">    
      <?php echo render_listing_search_form($entity_info['id'],$listing_container, $reports_info['id']) ?>
      
      <?php 
      	if(!filters_preivew::has_default_panel_access($entity_cfg) and $reports_info['reports_type']!='default' and (in_array($app_user['group_id'],explode(',',$entity_cfg->get('listing_config_access'))) or !strlen($entity_cfg->get('listing_config_access'))))
      	{
      		$html = '
			      <div class="btn-group" style="float:right">
							<a class="btn dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown">
							<i class="fa fa-gear"></i></i>
							</a>
							<ul class="dropdown-menu pull-right">
								<li>
			            ' . link_to_modalbox('<i class="fa fa-sort-amount-asc"></i> ' . TEXT_HEADING_REPORTS_SORTING,url_for('reports/sorting','reports_id=' . $reports_info['id'] . '&redirect_to=listing' . (strlen($app_path)>0 ? '&path=' . $app_path : '') )). '
			            ' . link_to_modalbox('<i class="fa fa-wrench"></i> ' . TEXT_NAV_LISTING_CONFIG,url_for('reports/configure','reports_id=' . $reports_info['id'] . '&redirect_to=listing'  . (strlen($app_path)>0 ? '&path=' . $app_path : '') )) . '
			      		
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

<div class="row">
  <div class="col-sm-<?php echo (12-$filters_panels->vertical_width)?>">
    <div id="<?php echo $listing_container ?>" class="entity_items_listing entity_items_listing_loading"></div>
  </div>
  
  <?php echo $filters_panels->render_vertical(); ?>
</div>

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
    load_items_listing('<?php echo $listing_container ?>',<?php echo $gotopage ?>);                                                                         
  });          
</script> 