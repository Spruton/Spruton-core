<h3 class="page-title"><?php echo TEXT_USERS_LOGIN_LOG ?></h3>


<form class="form-inline" role="form" id="users_login_log_filters" action="#" method="post">
	<div class="form-group">
		<label for="type"><?php echo TEXT_TYPE ?></label>
		<?php echo select_tag('type',users_login_log::get_type_choiсes(),'',array('class'=>'form-control')) ?>
	</div>
			
	<div class="form-group">
		<table>
			<tr>
				<td><label for="created_by">&nbsp;<?php echo TEXT_USERS ?></label></td>
				<td><?php echo select_tag('users_id',[''=>TEXT_NONE]+users::get_choices(),(isset($_GET['users_id']) ? (int)$_GET['users_id'] : ''),array('class'=>'form-control input-large chosen-select')) ?></td>
			</tr>
		</table>		
	</div>
	
	<div class="form-group" style="float:right">
		<?php echo '<a class="btn btn-default" href="' . url_for("tools/users_login_log",'action=reset') . '" onclick="return confirm(\'' . htmlspecialchars(TEXT_ARE_YOU_SURE). '\')">' . TEXT_DELETE_DATA. '</a>'; ?>
	</div>
	
</form>

<script>
$(function(){
	$('#users_login_log_filters .form-control').change(function(){
		load_items_listing('users_login_log_listing',1)
	})
})
</script>

<div class="row">
  <div class="col-md-12">
    <div id="users_login_log_listing"></div>
  </div>
</div>

<script>
  function load_items_listing(listing_container,page,search_keywords)
  {      
    $('#'+listing_container).append('<div class="data_listing_processing"></div>');
    $('#'+listing_container).css("opacity", 0.5);

    var filters = $('#users_login_log_filters').serializeArray();
    
    $('#'+listing_container).load('<?php echo url_for("tools/users_login_log",'action=listing') ?>',{page:page,filters:filters},
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
    load_items_listing('users_login_log_listing',1,'');                                                                         
  });
  
    
</script> 