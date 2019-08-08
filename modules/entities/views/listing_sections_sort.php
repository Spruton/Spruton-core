<?php echo ajax_modal_template_header(TEXT_SORT_VALUES) ?>

<?php echo form_tag('choices_form', url_for('entities/listing_sections','action=sort&entities_id=' . $_GET['entities_id'] . '&listing_types_id=' . $_GET['listing_types_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">      
<ol class="dd-list">
<?php 
	$filters_query = db_query("select * from app_listing_sections where listing_types_id='" . db_input(_get::int('listing_types_id')) . "' order by sort_order, name");
	while($v = db_fetch_array($filters_query))
	{
		$title = '';
		
		if(strlen($v['name']))
		{
			$title = $v['name'];
		}
		elseif(strlen($v['fields']))
		{
			$choices = [];
			$fields_query = db_query("select * from app_fields where id in (" . $v['fields'] . ") order by field(id," . $v['fields'] . ")");
			while($fields = db_fetch_array($fields_query))
			{
				$choices[] = fields_types::get_option($fields['type'],'name',$fields['name']);
			}
			 
			$title = implode(', ',$choices);
		}
		
		echo '<li class="dd-item" data-id="' . $v['id'] . '"><div class="dd-handle">' . $title . '</div></li>';
	}
?>
</ol>
</div>
      
   </div>
</div> 
<?php echo input_hidden_tag('choices_sorted') ?> 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
$(function(){
  $('#choices_sort').nestable({
      group: 0,
      maxDepth:1,
  }).on('change',function(e){
    output = $(this).nestable('serialize');
    
    if (window.JSON) 
    {
      output = window.JSON.stringify(output);
      $('#choices_sorted').val(output);
    } 
    else 
    {
      alert('JSON browser support required!');      
    }    
  })
})

</script>