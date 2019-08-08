<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('entities/listing_sections','action=delete&id=' . $_GET['id'] . '&listing_types_id=' . $_GET['listing_types_id'] . '&entities_id=' . $_GET['entities_id'])) ?>
    
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  