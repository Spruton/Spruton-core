<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_USERS_GROUPS ?></h4>
</div>

<?php echo form_tag('menu_form', url_for('filters_panels/panels','action=set_default_panel_access&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">

	<p><?php echo TEXT_FILTERS_PANELS_ACCESS_INFO ?></p>

<?php 
$choices = [];
$choices[0] = TEXT_ADMINISTRATOR;

$groups_query = db_fetch_all('app_access_groups','','sort_order, name');
while($groups = db_fetch_array($groups_query))
{
	$entities_access_schema = users::get_entities_access_schema($_GET['entities_id'],$groups['id']);

	if(!in_array('view',$entities_access_schema) and  !in_array('view_assigned',$entities_access_schema)) continue;
		
	$choices[$groups['id']] = $groups['name'];
}

$entities_cfg = new entities_cfg(_get::int('entities_id'));

?>  
  <div class="form-group">  	
    <div class="col-md-12">	
  	  <?php echo select_tag('users_groups[]',$choices,$entities_cfg->get('default_filter_panel_access'),array('class'=>'form-control chosen-select','multiple'=>'multiple')) ?>
    </div>			
  </div>
  
  <?php echo ajax_modal_template_footer() ?>

</form> 