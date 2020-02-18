
<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo  TEXT_RECORDS_VISIBILITY . ' <i class="fa fa-angle-right"></i> ' . $current_rules_info['id'] . ' <i class="fa fa-angle-right"></i> ' .  TEXT_FILTERS ?></h3>

<?php 

$entity_info = db_find('app_entities',$current_reports_info['entities_id']);

$reports_list[] = $current_reports_info['id'];

foreach($reports_list as $reports_id)
{

$report_info = db_find('app_reports',$reports_id);
$entity_info = db_find('app_entities',$report_info['entities_id']);

$parent_reports_param = '&entities_id=' . _get::int('entities_id');
if($current_reports_info['id']!=$reports_id)
{
  $parent_reports_param .= '&parent_reports_id=' . $reports_id;    
}
?>

<div class="panel panel-default">
  <div class="panel-heading"><?php echo TEXT_FILTERS_FOR_ENTITY . ': <b>' . $entity_info['name'] . '</b>' ?></div>
    <div class="panel-body"> 

    <?php echo button_tag(TEXT_BUTTON_ADD_NEW_REPORT_FILTER,url_for('records_visibility/filters_form','rules_id=' . $current_rules_info['id'] . '&reports_id=' . $current_reports_info['id'] . $parent_reports_param)) ?>
    
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
    <?php if(db_count('app_reports_filters',$reports_id,'reports_id')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
    <?php  
      $filters_query = db_query("select rf.*, f.name, f.type from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.reports_id='" . db_input($reports_id) . "' order by rf.id");
      while($v = db_fetch_array($filters_query)):
    ?>
      <tr>
        <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('records_visibility/filters_delete','rules_id=' . $current_rules_info['id'] . '&id=' . $v['id'] . '&reports_id=' . $current_reports_info['id'] . $parent_reports_param)) . ' ' . button_icon_edit(url_for('records_visibility/filters_form','rules_id=' . $current_rules_info['id'] . '&id=' . $v['id'] . '&reports_id=' . $current_reports_info['id'] . $parent_reports_param))  ?></td>    
        <td><?php echo fields_types::get_option($v['type'],'name',$v['name']) ?></td>
        <td><?php echo reports::get_condition_name_by_key($v['filters_condition']) ?></td>
        <td class="nowrap"><?php echo reports::render_filters_values($v['fields_id'],$v['filters_values'],'<br>',$v['filters_condition']) ?></td>            
      </tr>
    <?php endwhile?>  
    </tbody>
    </table>
    </div>
    
  </div>
</div>  
  
<?php } ?>  


<?php echo '<a href="' . url_for('records_visibility/rules','entities_id=' . _get::int('entities_id')) . '" class="btn btn-default">' . TEXT_BUTTON_BACK. '</a>' ?>
  


