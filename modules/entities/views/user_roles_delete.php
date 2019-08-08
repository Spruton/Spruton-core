<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('entities/user_roles','action=delete&id=' . $_GET['id'] . '&entities_id=' .$_GET['entities_id'] . '&fields_id=' .$_GET['fields_id'])) ?>
     
<div class="modal-body">    
<?php echo sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,user_roles::get_name_by_id($_GET['id']))?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>   