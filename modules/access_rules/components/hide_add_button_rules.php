
<br>

<h3 class="page-title"><?php echo  TEXT_HIDE_ADD_BUTTON_RULES ?></h3>

<p><?php echo sprintf(TEXT_HIDE_ADD_BUTTON_RULES_INFO,$parent_entities_info['name'],$entities_info['name']) ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_REPORT_FILTER,url_for('access_rules/parent_filters_form','reports_id=' . $reports_info['id'] . '&entities_id=' . $_GET['entities_id'])) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th width="100%"><?php echo TEXT_FIELD ?></th>
    <th><?php echo TEXT_FILTERS_CONDITION ?></th>    
    <th><?php echo TEXT_VALUES ?></th>
            
  </tr>
</thead>
<tbody>
<?php if(db_count('app_reports_filters',$reports_info['id'],'reports_id')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php  
  $filters_query = db_query("select rf.*, f.name, f.type from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.reports_id='" . db_input($reports_info['id']) . "' order by rf.id");
  while($v = db_fetch_array($filters_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('access_rules/parent_filters_delete','id=' . $v['id'] . '&reports_id=' . $reports_info['id']. '&entities_id=' . $_GET['entities_id'])) . ' ' . button_icon_edit(url_for('access_rules/parent_filters_form','id=' . $v['id'] . '&reports_id=' . $reports_info['id']. '&entities_id=' . $_GET['entities_id']))  ?></td>    
    <td><?php echo fields_types::get_option($v['type'],'name',$v['name']) ?></td>
    <td><?php echo reports::get_condition_name_by_key($v['filters_condition']) ?></td>    
    <td class="nowrap"><?php echo reports::render_filters_values($v['fields_id'],$v['filters_values'],'<br>',$v['filters_condition']) ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>
