<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo link_to(TEXT_NAV_LISTING_CONFIG, url_for('entities/listing_types','entities_id=' . _get::int('entities_id'))) . ' <i class="fa fa-angle-right"></i> ' . listing_types::get_type_title($listing_types['type']) . ' <i class="fa fa-angle-right"></i> ' . TEXT_SECTIONS ?></h3>



<?php echo button_tag(TEXT_BUTTON_ADD,url_for('entities/listing_sections_form','listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id'])) . ' ' . button_tag(TEXT_BUTTON_SORT,url_for('entities/listing_sections_sort','entities_id=' . $_GET['entities_id'] . '&listing_types_id=' . $listing_types['id']),true,array('class'=>'btn btn-default'))?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_TITLE ?></th>
    <th width="100%"><?php echo TEXT_FIELDS ?></th>
    <th><?php echo TEXT_ALIGN ?></th>               
    <th><?php echo TEXT_SORT_ORDER ?></th>            
  </tr>
</thead>
<tbody>
<?php if(db_count('app_listing_sections',$listing_types['id'],'listing_types_id')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php  
	$align_choices = listing_types::get_sections_align_choices();
  $filters_query = db_query("select * from app_listing_sections where listing_types_id='" . db_input($listing_types['id']) . "' order by sort_order, name");
  while($v = db_fetch_array($filters_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php 
    	echo button_icon_delete(url_for('entities/listing_sections_delete','id=' . $v['id'] . '&listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id'])) 
    	. ' ' . button_icon_edit(url_for('entities/listing_sections_form','id=' . $v['id']  . '&listing_types_id=' . $listing_types['id'] . '&entities_id=' . $_GET['entities_id']))  ?></td>    
    <td><?php echo $v['name'] ?></td>
    <td><?php

    if(strlen($v['fields']))
    {
    	$choices = [];
    	$fields_query = db_query("select * from app_fields where id in (" . $v['fields'] . ") order by field(id," . $v['fields'] . ")");
    	while($fields = db_fetch_array($fields_query))
    	{
    		$choices[] = fields_types::get_option($fields['type'],'name',$fields['name']);
    	}
    	
    	echo implode('<br>',$choices);
    }
    
    ?></td>        
    <td><?php echo listing_types::get_sections_align_icon($v['text_align']) . ' ' . $align_choices[$v['text_align']] ?></td>
    <td><?php echo $v['sort_order'] ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>

<?php echo link_to(TEXT_BUTTON_BACK, url_for('entities/listing_types','entities_id=' . _get::int('entities_id')),array('class'=>'btn btn-default'))?>
