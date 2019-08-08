<?php echo ajax_modal_template_header(TEXT_FIELDS_EXPORT) ?>

<?php echo form_tag('form-copy-to', url_for('entities/fields','action=export&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('selected_fields') ?>
<div class="modal-body" >
  <div id="modal-body-content">    

	<p><?php echo TEXT_FIELDS_EXPORT_INFO ?></p>
	
	<div class="form-group">
    	<label class="col-md-4 control-label" for="type"><?php echo TEXT_FILENAME ?></label>
      <div class="col-md-8">	
    	  <?php echo input_tag('filename',TEXT_FIELDS . '_' . $app_entities_cache[_get::int('entities_id')]['name'],array('class'=>'form-control input-large required')) ?>        
      </div>			
    </div>
        
      
  </div>
</div> 
<?php echo ajax_modal_template_footer(TEXT_EXPORT) ?>

</form>  

<script>
  $(function(){
     if($('.fields_checkbox:checked').length==0)
     {
       $('#modal-body-content').html('<?php echo TEXT_PLEASE_SELECT_FIELDS ?>')
       $('.btn-primary-modal-action').hide()
     }
     else
     {
       selected_fields_list = $('.fields_checkbox:checked').serialize().replace(/fields%5B%5D=/g,'').replace(/&/g,',');
       $('#selected_fields').val(selected_fields_list);              
     } 
     
              
  })     
</script>