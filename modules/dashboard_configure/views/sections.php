<ul class="page-breadcrumb breadcrumb noprint">
   <li>
       <a href="<?php echo url_for('dashboard_configure/index')?>"><?php echo TEXT_DASHBOARD_CONFIGURATION ?></a><i class="fa fa-angle-right"></i>          
   </li>
   <li>
       <?php echo TEXT_SECTIONS ?>          
   </li>
</ul>

<h3 class="page-title"><?php echo TEXT_SECTIONS ?></h3>

<p><?php echo TEXT_ADD_INFO_SECTIONS_INFO ?></p>

<?php echo button_tag(TEXT_ADD_SECTION,url_for('dashboard_configure/section_form')) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>                  
    <th width="100%"><?php echo TEXT_NAME ?></th>                  
    <th><?php echo TEXT_GRID ?></th>
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php		
  $sections_query = db_query("select * from app_dashboard_pages_sections order by sort_order, name");
  while($sections = db_fetch_array($sections_query)):  
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('dashboard_configure/section_delete','id=' . $sections['id'])) . ' ' . button_icon_edit(url_for('dashboard_configure/section_form','id=' . $sections['id'] )); ?></td>            
    <td><?php echo $sections['name'] ?></td>                 
    <td><?php echo dashboard_pages::get_section_grid_name($sections['grid']) ?></td>
    <td><?php echo $sections['sort_order'] ?></td>
  </tr>
<?php endwhile?>
<?php if(db_num_rows($sections_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';?>  
</tbody>
</table>
</div>

<?php echo '<a class="btn btn-default" href="' . url_for('dashboard_configure/index') . '">' . TEXT_BUTTON_BACK. '</a>'?>