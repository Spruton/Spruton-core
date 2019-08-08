
<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('entities_form', url_for('entities/listing_sections','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') . '&listing_types_id=' . $_GET['listing_types_id'] . '&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_TITLE ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large')) ?>
  	  <?php echo tooltip_text(TEXT_NOT_REQUIRED_FIELD) ?>
    </div>			
  </div>  
  
  
<?php

$listing_types_info = db_find("app_listing_types",_get::int('listing_types_id'));

$fields_sql_query = '';

$entity_info = db_find('app_entities',$_GET['entities_id']);

//include fieldtype_parent_item_id only for sub entities
if($entity_info['parent_id']==0)
{
	$fields_sql_query .= " and f.type not in ('fieldtype_parent_item_id')";
}

$reserverd_fields_types = array_merge(fields_types::get_reserved_data_types(),fields_types::get_users_types());
$reserverd_fields_types_list = "'" . implode("','", $reserverd_fields_types). "'";

$choices = [];
$fields_query = db_query("select f.*, t.name as tab_name, if(f.type in (" . $reserverd_fields_types_list . "),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_section') " . $fields_sql_query . " and  f.entities_id='" . db_input($_GET['entities_id']) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
while($v = db_fetch_array($fields_query))
{
	$choices[$v['id']] = strip_tags(fields_types::get_option($v['type'],'name',$v['name'])); 
}

?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="fields"><?php echo TEXT_FIELDS ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('fields[]',$choices,$obj['fields'],array('class'=>'form-control input-xlarge chosen-select chosen-sortable required','chosen_order'=>$obj['fields'],'multiple'=>'multiple')) ?>
  	  <?php echo tooltip_text(TEXT_SORT_ITEMS_IN_LIST) ?>
    </div>			
  </div> 
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="display_as"><?php echo TEXT_DISPLAY_AS ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('display_as',listing_types::get_sections_display_choices(), $obj['display_as'],array('class'=>'form-control input-small')) ?>
    </div>			
  </div>
  
  <div class="form-group" id="is-heading-container">
   	<label class="col-md-3 control-label" for="display_field_names"><?php echo TEXT_DISPLAY_FIELD_NAMES ?></label>
    <div class="col-md-9">	
  	  <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('display_field_names','1',array('checked'=>$obj['display_field_names'])) ?></label></div>        
    </div>			
  </div>  
   
  <div class="form-group">
  	<label class="col-md-3 control-label" for="text_align"><?php echo TEXT_ALIGN ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('text_align',listing_types::get_sections_align_choices(), $obj['text_align'],array('class'=>'form-control input-medium')) ?>
    </div>			
  </div>
  
<?php if($listing_types_info['type']=='list'): ?>  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo tooltip_icon(TEXT_SECTION_WIDHT_TIP) . TEXT_WIDHT ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('width',$obj['width'],array('class'=>'form-control input-small')) ?>  	  
    </div>			
  </div>
<?php endif ?>  
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-small')) ?>
    </div>			
  </div>
  
   </div>
</div>

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#entities_form').validate({ ignore: '',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                                                  
  });
  
</script>   
    
 
