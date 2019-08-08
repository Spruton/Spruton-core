
<?php echo ajax_modal_template_header(TEXT_LINK_RECORD) ?>

<?php $entity_info = db_find('app_entities',$_GET['related_entities']); ?>

<?php echo form_tag('add_related_items', url_for('items/related_item','action=add_related_item&path=' . $_GET['path']), array('class'=>'form-horizontal')) ?>
    
<div class="modal-body ajax-modal-width-790">
  <div class="form-body">  
<?php echo input_hidden_tag('related_entities_id',$entity_info['id']) ?>

  <div class="form-group">
  	<label class="col-md-4 control-label"><?php echo $entity_info['name'] ?>:</label>
    <div class="col-md-8">	  	        
      <?php echo select_tag('items[]',[],'',['class'=>'form-control required', 'data-placeholder'=>TEXT_ENTER_VALUE,'multiple'=>'multiple'])?>  
    </div>			
  </div>


	</div>   
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_LINK) ?>

</form>

<script>
$(function(){
    
  $("#add_related_items").validate({
		submitHandler: function(form){
			app_prepare_modal_action_loading(form)
			form.submit();
		}
  });   

  $("#items").select2({		      
    width: "100%",		      
    dropdownParent: $("#ajax-modal"),
    "language":{
      "noResults" : function () { return "<?php echo addslashes(TEXT_NO_RESULTS_FOUND)  ?>"; },
  		"searching" : function () { return "<?php echo addslashes(TEXT_SEARCHING) ?>"; },
  		"errorLoading" : function () { return "<?php echo addslashes(TEXT_RESULTS_COULD_NOT_BE_LOADED) ?>"; },
  		"loadingMore" : function () { return "<?php echo addslashes(TEXT_LOADING_MORE_RESULTS) ?>"; }		    				
    },		
    ajax: {
  		url: "<?php echo url_for('items/select2_related_items','action=select_items&entity_id=' . $entity_info['id'] . '&field_id=' . _get::int('field_id') . '&path=' . $entity_info['id'] . '&parent_entity_item_id=' . $parent_entity_item_id) ?>",
  		dataType: "json",
  		data: function (params) {
	      var query = {
	        search: params.term,
	        page: params.page || 1
	      }
	
	      // Query parameters will be ?search=[term]&page=[page]
	      return query;
	    },        				        				
  	},        				
		templateResult: function (d) { return $(d.html); },      		        			
	});

  $("#items").change(function (e) {
		$("#items-error").remove();
	});   
  
})  
</script>
    
    
 
