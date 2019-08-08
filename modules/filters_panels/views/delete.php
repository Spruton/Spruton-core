<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_HEADING_DELETE ?></h4>
</div>

<?php echo form_tag('delete', url_for('filters_panels/panels','action=delete&entities_id=' . $_GET['entities_id'] . '&id=' . $_GET['id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
     
<?php 	
	echo TEXT_ARE_YOU_SURE;
?>

  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    