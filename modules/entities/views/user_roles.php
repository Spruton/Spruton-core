<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo   $field_info['name'] . ' <i class="fa fa-angle-right"></i> '.  TEXT_USER_ROLES ?></h3>

<p><?php echo TEXT_USER_ROLES_INFO ?></p>

<?php 
	if(!entities::has_subentities($field_info['entities_id']))
	{
		echo '<div class="alert alert-warning">' . TEXT_USER_ROLES_ENTITIES_WARNING . '</div>';
	}
	else 
	{
?>

<?php echo button_tag(TEXT_BUTTON_ADD,url_for('entities/user_roles_form','fields_id=' . $field_info['id'] . '&entities_id=' . $_GET['entities_id'])) . ' ' . button_tag(TEXT_BUTTON_SORT,url_for('entities/user_roles_sort','entities_id=' . $_GET['entities_id'] . '&fields_id=' . $field_info['id']),true,array('class'=>'btn btn-default'))?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th width="100%"><?php echo TEXT_TITLE ?></th>                     
    <th><?php echo TEXT_SORT_ORDER ?></th>            
  </tr>
</thead>
<tbody>
<?php if(db_count('app_user_roles',$field_info['id'],'fields_id')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php  
	
  $filters_query = db_query("select * from app_user_roles where fields_id='" . db_input($field_info['id']) . "' order by sort_order, name");
  while($v = db_fetch_array($filters_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php 
    	echo button_icon_delete(url_for('entities/user_roles_delete','id=' . $v['id'] . '&fields_id=' . $field_info['id'] . '&entities_id=' . $_GET['entities_id'])) 
    	. ' ' . button_icon_edit(url_for('entities/user_roles_form','id=' . $v['id']  . '&fields_id=' . $field_info['id'] . '&entities_id=' . $_GET['entities_id']))  ?></td>    
    <td><?php echo link_to($v['name'], url_for('entities/user_roles_access','role_id=' . $v['id'] . '&fields_id=' . $field_info['id'] . '&entities_id=' . $_GET['entities_id'])) ?></td>            
    <td><?php echo $v['sort_order'] ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>

<?php } ?>

<?php echo link_to(TEXT_BUTTON_BACK, url_for('entities/fields','entities_id=' . _get::int('entities_id')),array('class'=>'btn btn-default'))?>
