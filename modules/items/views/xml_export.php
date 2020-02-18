

<?php echo ajax_modal_template_header($template_info['name']) ?>

<?php echo form_tag('export-form', url_for('items/xml_export','path=' . $_GET['path'] . '&templates_id=' . $_GET['templates_id'])) . input_hidden_tag('action','export')  ?>

<div class="modal-body ">    

<p>
<?php
	
	$filename = $template_info['name'] .  ' ' . $app_entities_cache[$current_entity_id]['name'] . ' ' . $current_item_id;
	
  echo TEXT_FILENAME . '<br>' . input_tag('filename',$filename,array('class'=>'form-control input-large required')); 
?>
</p>


</div> 

<?php  
  echo ajax_modal_template_footer(TEXT_EXPORT) 
?>

</form>  

