
<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('filters_delete', url_for('records_visibility/filters','action=delete&rules_id=' . $_GET['rules_id'] . '&entities_id=' . $_GET['entities_id'] . '&id=' . $_GET['id'] . '&reports_id=' . $_GET['reports_id']. (isset($_GET['parent_reports_id']) ? '&parent_reports_id=' . $_GET['parent_reports_id']:''))) ?>
<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php if(isset($_GET['path'])) echo input_hidden_tag('path',$_GET['path']) ?>
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  
    
 
