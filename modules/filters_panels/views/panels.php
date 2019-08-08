
<?php require(component_path('entities/navigation')) ?>

<?php $entities_cfg = new entities_cfg(_get::int('entities_id')); ?>

<h3 class="page-title"><?php echo  TEXT_DEFAULT_FILTER_PANEL ?></h3>

<p><?php echo TEXT_DEFAULT_FILTER_PANEL_INFO; ?></p>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th width="120"><?php echo TEXT_IS_ACTIVE ?></th>                  
    <th width="210"><?php echo TEXT_USERS_GROUPS ?></th>
    <th width="210"><?php echo TEXT_NAV_LISTING_CONFIG ?></th>
    <th ><?php echo TEXT_NAV_LISTING_FILTERS_CONFIG ?></th>
        
  </tr>
</thead>
<tbody>

<tr>  
  <td>
  
  			<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-sm btn-default <?php echo $entities_cfg->get('default_filter_panel_status',1)==1 ? 'active':'' ?>">
					<input name="default_panel_status" type="radio" value="1" class="toggle default_panel_status"><?php echo TEXT_YES ?></label>
					<label class="btn btn-sm btn-default <?php echo $entities_cfg->get('default_filter_panel_status',1)==0 ? 'active':'' ?>">
					<input name="default_panel_status" type="radio" value="0" class="toggle default_panel_status"><?php echo TEXT_NO ?></label>				
				</div>
  </td>     
  <td><?php echo '<a href="javascript: open_dialog(\'' . url_for('filters_panels/access','entities_id=' . $_GET['entities_id']) . '\')" class="btn btn-default">' . TEXT_CONFIGURE . '</a>';?></td>
  <td><?php echo '<a href="javascript: open_dialog(\'' . url_for('filters_panels/listing_config_access','entities_id=' . $_GET['entities_id']) . '\')" class="btn btn-default">' . TEXT_CONFIGURE . '</a>';?></td>
  <td><?php echo '<a href="' . url_for('entities/listing_filters','entities_id=' . $_GET['entities_id']) . '" class="btn btn-default">' . TEXT_CONFIGURE . '</a>';?></td>
</tr>  
</tbody>
</table>
</div>

<script>
 $('.default_panel_status').change(function(){
		 $.ajax({
				method: 'POST',
				url: '<?php echo url_for('filters_panels/panels','action=set_default_status&entities_id=' . $_GET['entities_id']) ?>',
				data: {status: $(this).val()}
			 })
	 })
</script>

<h3 class="page-title"><?php echo  TEXT_QUICK_FILTERS_PANELS ?></h3>

<p><?php echo TEXT_QUICK_FILTER_PANEL_INFO; ?></p>

<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('filters_panels/form','entities_id=' . $_GET['entities_id'])) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_ID ?></th>    
    <th width="100%"><?php echo TEXT_POSITION ?></th>
    <th><?php echo TEXT_ACTIVE_FILTERS ?></th>
    <th><?php echo TEXT_WIDHT ?></th>                   
    <th><?php echo TEXT_USERS_GROUPS ?></th>    
    <th><?php echo TEXT_IS_ACTIVE ?></th>
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php

$panels_query = db_query("select * from app_filters_panels where entities_id='" . _get::int('entities_id') . "' order by position, sort_order");

if(db_num_rows($panels_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($panels = db_fetch_array($panels_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('filters_panels/delete','id=' . $panels['id'] . '&entities_id=' . $_GET['entities_id'])) . ' ' . button_icon_edit(url_for('filters_panels/form','id=' . $panels['id']. '&entities_id=' . $_GET['entities_id'])) ?></td>
  <td><?php echo $panels['id'] ?></td>      
  <td><?php echo '<a href="' . url_for('filters_panels/fields','panels_id=' . $panels['id'] . '&entities_id=' . $_GET['entities_id']) . '">' . filters_panels::get_position_name($panels['position']) . '</a>' ?>
  <?php 
  $fields_list = [];
  $fields_query = db_query("select pf.*, f.name as field_name, f.type as field_type from app_filters_panels_fields pf, app_fields f where pf.fields_id=f.id and pf.panels_id='" . $panels['id'] . "' order by pf.sort_order");      
  while($fields = db_fetch_array($fields_query))
  {
  	$fields_list[] = fields_types::get_option($fields['field_type'],'name',$fields['field_name']);
  }
  echo '<br><i>' . implode(', ',$fields_list) . '</i>';
  ?>
  </td>
  <td><?php echo render_bool_value($panels['is_active_filters']) ?></td>
  <td><?php echo ($panels['position']=='vertical' ? filters_panels::get_width_name($panels['width']) : '100%') ?></td>     
  <td>
      <?php
      if(strlen($panels['users_groups']))
      {	
        $users_groups_list = array();
        foreach(explode(',',$panels['users_groups']) as $users_groups_id)
        {
          $users_groups_list[] = access_groups::get_name_by_id($users_groups_id);
        }
        
        echo implode('<br>',$users_groups_list);
      }
      ?>
    </td>
    <td><?php echo render_bool_value($panels['is_active']) ?></td>  
    <td><?php echo $panels['sort_order'] ?></td>
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>