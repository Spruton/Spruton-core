
<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo  TEXT_NAV_LISTING_CONFIG ?></h3>

<p><?php echo TEXT_LISGIN_CONFIGURATION_INFO; ?></p>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    
    <th><?php echo TEXT_ACTION?></th>               
    <th width="100%"><?php echo TEXT_TYPE ?></th>
    <th><?php echo TEXT_IS_ACTIVE ?></th>    
    <th><?php echo TEXT_IS_DEFAULT ?></th>    
  </tr>
</thead>
<tbody>
<?php
$listing_types_query = db_query("select * from app_listing_types where entities_id='" . _get::int('entities_id'). "'");

while($v = db_fetch_array($listing_types_query)):

$url = ($v['type']=='table' ? url_for('entities/listing','entities_id=' . $v['entities_id']) : url_for('entities/listing_sections','listing_types_id=' . $v['id'] . '&entities_id=' . $v['entities_id']))
?>
<tr>  
  <td style="white-space: nowrap;"><?php echo button_icon_edit(url_for('entities/listing_types_form','id=' . $v['id']. '&entities_id=' . $_GET['entities_id'])) ?></td>
  <td><?php echo link_to(listing_types::get_type_title($v['type']),$url)  ?></td>
  <td><?php echo $v['type']=='table' ? render_bool_value(1) : render_bool_value($v['is_active']) ?></td>
  <td><?php echo ($v['type']!='mobile' ? render_bool_value($v['is_default']) : '') ?></td>
     
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>