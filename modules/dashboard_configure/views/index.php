<h3 class="page-title"><?php echo TEXT_DASHBOARD_CONFIGURATION ?></h3>

<p><?php echo TEXT_DASHBOARD_CONFIGURATION_INFO ?></p>

<?php echo button_tag(TEXT_ADD_INFO_BLOCK,url_for('dashboard_configure/form','type=info_block')) . ' <a class="btn btn-default" href="' . url_for('dashboard_configure/sections') . '">' . TEXT_SECTIONS. '</a>' ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>
    <th><?php echo TEXT_POSITION ?></th>            
    <th><?php echo TEXT_COLOR ?></th>   
    <th width="100%"><?php echo TEXT_TITLE ?></th>
    <th><?php echo TEXT_FIELDS ?></th>          
    <th><?php echo TEXT_ASSIGNED_TO ?></th>
    <th><?php echo TEXT_IS_ACTIVE ?></th>            
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php
	$access_groups_cache = access_groups::get_cache();
		
  $pages_query = db_query("select dp.*, ds.name as section_name from app_dashboard_pages dp left join app_dashboard_pages_sections ds on ds.id=dp.sections_id  where dp.type='info_block' order by ds.sort_order, ds.name, dp.sort_order, dp.name");
  while($pages = db_fetch_array($pages_query)):  
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('dashboard_configure/delete','id=' . $pages['id'])) . ' ' . button_icon_edit(url_for('dashboard_configure/form','id=' . $pages['id'] . '&type=info_block')); ?></td>
    <td><?php echo ($pages['sections_id']>0 ? $pages['section_name'] : TEXT_DEFAULT) ?></td>        
    <td><?php echo '<span class="label label-' . $pages['color'] . '">' . dashboard_pages::get_color_by_name($pages['color']) . '</span>' ?></td>
    <td>
    	<?php echo $pages['name'] ?>
    	<div><i><small><?php echo (strlen($pages['description'])>64 ? substr($pages['description'],0,64) . '...' : $pages['description'] )?></small></i></div>
    </td>            
    <td><?php
    	if(strlen($pages['users_fields']))
    	{
    		$fields = [];
    		foreach(explode(',',$pages['users_fields']) as $field_id)
    		{
    			$field_info_query = db_query("select id, type, name from app_fields where id='" . $field_id. "'");
    			if($field_info = db_fetch_array($field_info_query))
    			{	
    				$fields[] = fields_types::get_option($field_info['type'], 'name',$field_info['name']);
    			}
    		}
    		
    		//print_r($fields);
    		
    		echo implode('<br>',$fields);
    	}
    ?></td>
    <td>
<?php
  if(strlen($pages['users_groups'])>0)
  {
    $users_groups = array();
    foreach(explode(',',$pages['users_groups']) as $id)
    {
      $users_groups[] = $access_groups_cache[$id];
    }
    
    if(count($users_groups)>0)
    {        
      echo '<span style="display:block" data-html="true" data-toggle="tooltip" data-placement="left" title="' . addslashes(implode(', ',$users_groups)). '">' . TEXT_USERS_GROUPS . ' (' . count($users_groups) . ')</span>';
    }
  }
    
?>    
    </td>
    <td><?php echo render_bool_value($pages['is_active']) ?></td>        
    <td><?php echo $pages['sort_order'] ?></td>
  </tr>
<?php endwhile?>
<?php if(db_num_rows($pages_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';?>  
</tbody>
</table>
</div>

<br>

<?php echo button_tag(TEXT_ADD_PAGE,url_for('dashboard_configure/form','type=page')) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th> 
    <th><?php echo TEXT_COLOR ?></th>              
    <th width="100%"><?php echo TEXT_TITLE ?></th>      
    <th><?php echo TEXT_ASSIGNED_TO ?></th>
    <th><?php echo TEXT_IS_ACTIVE ?></th>        
    <th><?php echo TEXT_CREATED_BY ?></th>
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php
	$access_groups_cache = access_groups::get_cache();
	
  $pages_query = db_query("select * from app_dashboard_pages where type='page' order by sort_order, name");
  while($pages = db_fetch_array($pages_query)):  
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('dashboard_configure/delete','id=' . $pages['id'])) . ' ' . button_icon_edit(url_for('dashboard_configure/form','id=' . $pages['id'] . '&type=page')); ?></td>        
    <td><?php echo '<span class="label label-' . $pages['color'] . '">' . dashboard_pages::get_color_by_name($pages['color']) . '</span>' ?></td>
    <td><?php echo $pages['name'] ?></td>            
    <td>
<?php
  if(strlen($pages['users_groups'])>0)
  {
    $users_groups = array();
    foreach(explode(',',$pages['users_groups']) as $id)
    {
      $users_groups[] = $access_groups_cache[$id];
    }
    
    if(count($users_groups)>0)
    {        
      echo '<span style="display:block" data-html="true" data-toggle="tooltip" data-placement="left" title="' . addslashes(implode(', ',$users_groups)). '">' . TEXT_USERS_GROUPS . ' (' . count($users_groups) . ')</span>';
    }
  }
    
?>    
    </td>
    <td><?php echo render_bool_value($pages['is_active']) ?></td>    
    <td><?php echo users::get_name_by_id($pages['created_by']) ?></td>
    <td><?php echo $pages['sort_order'] ?></td>
  </tr>
<?php endwhile?>
<?php if(db_num_rows($pages_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';?>  
</tbody>
</table>
</div>
