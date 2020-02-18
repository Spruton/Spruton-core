
<div class="row">
  <div class="col-md-6">

<?php echo form_tag('search_form',url_for('global_search/search')) ?>  
		<div class="input-group">

<?php 
$entities_query = db_query("select gs.*, e.name from app_ext_global_search_entities gs, app_entities e where gs.entities_id=e.id order by gs.sort_order,gs.id");

if(db_num_rows($entities_query)>1)
{
?>			
			<div class="input-group-btn">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo TEXT_ENTITY ?> <i class="fa fa-angle-down"></i></button>
				<div class="dropdown-menu hold-on-click dropdown-checkboxes" role="menu">
					<?php 
					while($entities = db_fetch_array($entities_query))
					{
						if(!users::has_users_access_name_to_entity('view',$entities['entities_id'])) continue;
						
						echo '<label>' . input_checkbox_tag('search_by_entities[]',$entities['entities_id'],['class'=>'search_by_entities']) . $entities['name'] . '</label>';
					}
					
					echo '
						<label class="divider"></label>
						<label><a href="javascript: unchecked_all_by_classname(\'search_by_entities\')">' . TEXT_RESET . '</a></label>
						';
					
					?>				
					
					
				</div>
			</div>						
<?php 
}

$attributes = ['class'=>'form-control','autocomplete'=>'off','required'=>'required'];

$attributes['placeholder'] = (defined('CFG_GLOBAL_SEARCH_INPUT_TOOLTIP') ? CFG_GLOBAL_SEARCH_INPUT_TOOLTIP : TEXT_SEARCH);

if(strlen(CFG_GLOBAL_SEARCH_INPUT_MIN))
	$attributes['minlength'] = CFG_GLOBAL_SEARCH_INPUT_MIN;

if(strlen(CFG_GLOBAL_SEARCH_INPUT_MAX))
	$attributes['maxlength'] = CFG_GLOBAL_SEARCH_INPUT_MAX;
	
echo input_tag('keywords',(isset($_POST['keywords']) ? $_POST['keywords']:''),$attributes);
?>			
			<!-- /btn-group -->
			
			
			<span class="input-group-btn">
				<button class="btn btn-info" type="submit"><?php echo TEXT_SEARCH?></button>
			</span>
		</div>
		<div style="padding-top: 3px;">
			<?php 
				echo '<label style="padding-right: 15px;">' . input_checkbox_tag('search_type_match',1,['checked'=>true]) . ' ' . TEXT_SEARCH_TYPE_MATCH . '</label>';
				
				if(global_search::has_search_in_comments())
				{
					echo '<label>' . input_checkbox_tag('search_in_comments',1) . ' ' . TEXT_SEARCH_IN_COMMENTS . '</label>';
				}
				 
			?>
		</div>
</form>		
		
  </div>
</div>		


<div class="row">
  <div class="col-md-12">
    <div id="search_result"></div>
  </div>
</div>

<script>
  function load_items_listing(listing_container,page,search_keywords)
  {      
    $('#'+listing_container).append('<div class="data_listing_processing"></div>');
    $('#'+listing_container).css("opacity", 0.5);


    var search_by_entities = [];
    $(".search_by_entities:checked").each(function(){
    	search_by_entities.push($(this).val());
    });

    //alert(search_by_entities);
        
    $('#'+listing_container).load('<?php echo url_for("global_search/search",'action=listing') ?>',{
      	page:page,
      	keywords:$('#keywords').val(),
      	search_by_entities: search_by_entities,
      	search_type_match: $('#search_type_match').prop('checked'),
      	search_in_comments: $('#search_in_comments').prop('checked')
      },
      function(response, status, xhr) {
        if (status == "error") {                                 
           $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
        }
        
        $('#'+listing_container).css("opacity", 1);

        appHandleUniformInListing()                                                                                                                    
      }
    );
  }


  $(function() {     
    load_items_listing('search_result',1,'');

    $('#search_form').submit(function(){
    	load_items_listing('search_result',1,'');
    	return false;
    })                                                                         
  });
  
    
</script> 
