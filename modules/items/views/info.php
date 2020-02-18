<?php require(component_path('items/navigation')) ?>

<?php 
$item_page_columns  = explode('-',$entity_cfg->get('item_page_columns_size','8-4'));
?>

<!-- include form fields display rules in info page  -->
<?php require(component_path('items/forms_fields_rules.js')); ?>
 
<div class="row">

<!-- First Column  -->
    <div class="col-md-<?php echo $item_page_columns[0] ?> project-info">
      
    <div class="portlet portlet-item-description">
			<div class="portlet-title">
				<div class="caption">        
          <?php echo $app_breadcrumb[count($app_breadcrumb)-1]['title'] ?>             
        </div>
        <div class="tools">
        	<?php
        		$help_pages = new help_pages($current_entity_id);
        		echo $help_pages->render_icon('info'); 
        	?>
        	
					<a href="javascript:;" class="collapse"></a>
				</div>
			</div>
			<div class="portlet-body">
      
        
          
<!-- Inlucde timer from Extension -->          
<?php
	$access_rules = new access_rules($current_entity_id, $item_info);
	
	$item_actions_menu = '';

  if(is_ext_installed())
  {
    $timer = new timer($current_entity_id, $current_item_id);
    $item_actions_menu .= $timer->render_button();
  }
                                           
  if(users::has_access('update',$access_rules->get_access_schema()))
  { 
   	$item_actions_menu .= '<li>' . button_tag(TEXT_BUTTON_EDIT,url_for('items/form','id=' . $current_item_id. '&entity_id=' . $current_entity_id . '&path=' . $_GET['path'] . '&redirect_to=items_info'),true,array('class'=>'btn btn-primary btn-sm'),'fa-edit')  .'</li>';
  }
                         
	if(is_ext_installed())
	{
		$processes = new processes($current_entity_id);
		$item_actions_menu .= $processes->render_buttons('default');
		
		//print templates
		$item_actions_menu .= export_templates::get_users_templates_by_position($current_entity_id, 'default');
		$item_actions_menu .= export_templates::get_users_templates_by_position($current_entity_id, 'menu_print');
		
		$item_actions_menu .= xml_export::get_users_templates_by_position($current_entity_id, 'default');
		$item_actions_menu .= xml_export::get_users_templates_by_position($current_entity_id, 'menu_export');
  }
                                
$more_actions_menu = '';

if(is_ext_installed()) $more_actions_menu .= $processes->render_buttons('menu_more_actions');

$more_actions_menu .= plugins::render_simple_menu_items('more_actions');

if(users::has_access('export',$access_rules->get_access_schema()))
{	
	$more_actions_menu .= '<li>' . link_to_modalbox('<i class="fa fa-file-pdf-o"></i> ' . TEXT_BUTTON_EXPORT,url_for('items/single_export','path=' . $_GET['path'])) . '</li>';
}

if(users::has_access('update',$access_rules->get_access_schema()) and $current_entity_id==1)
{	
	$more_actions_menu .=  '<li>' . link_to('<i class="fa fa-unlock-alt"></i> ' .TEXT_CHANGE_PASSWORD, url_for('items/change_user_password','path=' . $_GET['path'])) . '</li>';
}    

if(users::has_access('delete',$access_rules->get_access_schema()))
{	
	$check = true;
	 
	if(users::has_access('delete_creator',$access_rules->get_access_schema()) and $item_info['created_by']!=$app_user['id'])
	{
		$check = false;
	}
	 
	if($check)
	{
  	$more_actions_menu .=  '<li><a href="#" onClick="open_dialog(\'' . url_for('items/delete','id=' .$current_item_id . '&entity_id=' . $current_entity_id . '&path=' . $_GET['path']) . '\'); return false;"><i class="fa fa-trash-o"></i> ' . TEXT_BUTTON_DELETE . '</a></li>';
	}
}   

//check access to action with assigned only
if(users::has_access('action_with_assigned'))
{
	if(!users::has_access_to_assigned_item($current_entity_id,$current_item_id))
	{
		$item_actions_menu = $more_actions_menu = '';
	}
}

if(strlen($more_actions_menu))
{	
	$item_actions_menu .=  '
			<li>
	  	 <div class="btn-group">
					<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">
					' . TEXT_MORE_ACTIONS . ' <i class="fa fa-angle-down"></i>
					</button>
					<ul class="dropdown-menu" role="menu">                                       
					' . $more_actions_menu . '												
					</ul>
				</div>
			</li>
	';
}

if(strlen($item_actions_menu))
{
	echo '
		<div class="prolet-body-actions">        
          <ul class="list-inline">
					' . $item_actions_menu . '
          </ul>
        </div>
		';
}	


//Stages panels
echo stages_panel::render($current_entity_id,$item_info);

?>    
              
<!-- Inlucde timer from Extension -->          
<?php
  if(class_exists('timer'))
  {    
    echo $timer->render();
  }
?>        
          
        <div class="item-content-box ckeditor-images-content-prepare">
          <?php 
	         if($entity_cfg->get('item_page_details_columns','2')==1)
	         {
	         	 echo items::render_info_box($current_entity_id,$current_item_id,false,false);
	         }	
	         else 
	         {	
          	 echo items::render_content_box($current_entity_id,$current_item_id); 
	         }
          ?>
        </div>
        
      </div>
    </div>
    
    <?php

//include related emails    
    if(is_ext_installed())
    {
    	$mail_related = new mail_related($current_entity_id,'left_column');
    	echo $mail_related->render_list($current_item_id);
    }
    
//include reladed records that displays as single list    
      $reladed_records = new related_records($current_entity_id,$current_item_id);
      echo $reladed_records->render_as_single_list();
    ?>
    
    
    <?php 
//includes subentity imtes listins if configure for item info page    
    	$subentities_items_position = 'left_column';
    	require(component_path('items/load_subentities_items'));
    	
//includes field entity imtes listins if configure for item info page
    	$field_entity_items_position = 'left_column';
    	require(component_path('items/load_field_entity_items'));
    	
    	if(is_ext_installed())
    	{
    		$item_pivot_tables = new item_pivot_tables($current_entity_id,'left_column');
    		echo $item_pivot_tables->render();
    	}
    ?>
    
    <?php
//include items comments if user have access and comments enabled     
    if(users::has_comments_access('view', $access_rules->get_comments_access_schema()) and $entity_cfg->get('use_comments')==1 and $entity_cfg->get('item_page_comments_position','left_column')=='left_column') 
    {
      require(component_path('items/comments'));
    } 
    ?>
    
    </div>

<!-- Second Column  -->
    <div class="col-md-<?php echo $item_page_columns[1] ?>" style="position:static">

	    <?php 
//include related emails	    
	    if(is_ext_installed())
	    {
	    	$mail_related = new mail_related($current_entity_id,'right_column');
	    	echo $mail_related->render_list($current_item_id);
	    }
	    
	//include related records in box    
	    echo $reladed_records->render_as_single_list(false); 
	    ?>
	        	    	
			<?php if($entity_cfg->get('item_page_details_columns','2')==2 and strlen($info_box = items::render_info_box($current_entity_id,$current_item_id))): ?>
	    <div class="panel panel-info item-details">
	  		<div class="panel-body item-details">            
	      <?php echo $info_box ?>
	      </div>
	    </div>
	    <?php endif ?>
	    
	    <?php 
//includes subentity imtes listins if configure for item info page    
    		$subentities_items_position = 'right_column';
    		require(component_path('items/load_subentities_items'));
    		
//includes field entity imtes listins if configure for item info page
    		$field_entity_items_position = 'right_column';
    		require(component_path('items/load_field_entity_items'));
    		
    		if(is_ext_installed())
    		{
    			$item_pivot_tables = new item_pivot_tables($current_entity_id,'right_column');
    			echo $item_pivot_tables->render();
    		}
    		
    	?>
	    
      <?php
	//include items comments if user have access and comments enabled     
	    if(users::has_comments_access('view') and $entity_cfg->get('use_comments')==1 and $entity_cfg->get('item_page_comments_position','')=='right_column') 
	    {
	      require(component_path('items/comments'));
	    } 
	    ?>
    
    </div>
</div>  

<script>
  $(function(){
    ckeditor_images_content_prepare();
  })
</script>

<!-- inluce js to load item listing -->
<?php require(component_path('items/load_items_listing.js')); ?>

