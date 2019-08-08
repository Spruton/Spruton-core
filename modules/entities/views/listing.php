<?php require(component_path('entities/navigation')) ?>


<h3 class="page-title"><?php echo link_to(TEXT_NAV_LISTING_CONFIG, url_for('entities/listing_types','entities_id=' . _get::int('entities_id'))) ?> <i class="fa fa-angle-right"></i> <?php echo TEXT_TABLE ?></h3>

<div><?php echo TEXT_LISTING_CFG_INFO ?></div>


<?php

$fields_sql_query = '';

$entity_info = db_find('app_entities',$_GET['entities_id']);

//include fieldtype_parent_item_id only for sub entities
if($entity_info['parent_id']==0)
{
  $fields_sql_query .= " and f.type not in ('fieldtype_parent_item_id')";
}

$fields_sql_query .= " and f.type not in ('fieldtype_section')";

?>
<div class="row">
  <div class="col-md-8">
    <table style="width: 100%;">
      <tr>
        <td valign="top" width="50%">
          <fieldset>
            <legend><?php echo TEXT_FIELDS_IN_LISTING ?></legend>
    <div class="cfg_listing">        
      <ul id="fields_in_listing" class="sortable">
      <?php
      $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.listing_status = 1 " . $fields_sql_query . " and  f.entities_id='" . db_input($_GET['entities_id']) . "' and f.forms_tabs_id=t.id order by f.listing_sort_order");
      while($v = db_fetch_array($fields_query))
      {
        echo '<li id="form_fields_' . $v['id'] . '"><div>' . fields_types::get_option($v['type'],'name',$v['name']) . '</div></li>';
      }
      ?> 
      </ul>         
    </div>
                  
          </fieldset>
        
        </td>
        <td style="padding-left: 25px;" valign="top">
        
          <fieldset>
            <legend><?php echo TEXT_FIELDS_EXCLUDED_FROM_LISTING ?></legend>
    <div class="cfg_listing">        
    <ul id="fields_excluded_from_listing" class="sortable">
    <?php
    $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.listing_status = 0 " . $fields_sql_query . " and  f.entities_id='" . db_input($_GET['entities_id']) . "' and f.forms_tabs_id=t.id and f.type not in ('fieldtype_mapbbcode') order by t.sort_order, t.name, f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
      echo '<li id="form_fields_' . $v['id'] . '"><div>' . fields_types::get_option($v['type'],'name',$v['name']). '</div></li>';
    }
    ?> 
    </ul>
    </div>                     
          </fieldset>
          
          
        </td>
      </tr>
    </table>
  </div>
</div>  

<br><br>


<?php

$cfg = new entities_cfg($_GET['entities_id']);

//select allowed fields for heading
$choices = array();
$choices[''] = '';    
$fields_query = db_query("select f.*, t.name as tab_name,if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_created_by','fieldtype_parent_item_id'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_action','fieldtype_parent_item_id','fieldtype_mapbbcode','fieldtype_section','fieldtype_input_numeric_comments','fieldtype_input_url','fieldtype_attachments','fieldtype_input_file','fieldtype_image','fieldtype_textarea_wysiwyg','fieldtype_formula','fieldtype_related_records','fieldtype_user_status','fieldtype_user_accessgroups','fieldtype_user_language','fieldtype_user_skin','fieldtype_user_photo')  and f.entities_id='" . db_input($_GET["entities_id"]) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
while($v = db_fetch_array($fields_query))
{
  $choices[$v['id']] = fields_types::get_option($v['type'],'name',$v['name']);
}
?>

<legend><?php echo tooltip_icon(TEXT_IS_HEADING_INFO) . TEXT_SELECT_HEADING_FIELD ?></legend>
<div class="row">
  <div class="col-md-3" style="min-width: 350px;">        
    <div><?php echo select_tag('heading_field_id',$choices,fields::get_heading_id($_GET["entities_id"]),array('class'=>'form-control input-large'))?></div>                         
  </div>
  <div class="col-md-9">
  	<form class="form-inline" role="form">
				<div class="form-group">
					<label for="number_fixed_field_in_listing"><?php echo tooltip_icon(TEXT_HEADING_WIDTH_BASED_CONTENT_INFO) . TEXT_HEADING_WIDTH_BASED_CONTENT ?></label>
					<?php echo select_tag('heading_width_based_content',array('1'=>TEXT_YES,'0'=>TEXT_NO),(int)$cfg->get('heading_width_based_content'),array('class'=>'form-control input-small')) ?>
				</div>
			</form>
			
			<form class="form-inline" role="form" style="padding-top: 5px;">
				<div class="form-group">
					<label for="number_fixed_field_in_listing"><?php echo TEXT_CHANGE_COL_WIDTH_IN_LISTIN ?></label>
					<?php echo select_tag('change_col_width_in_listing',array('1'=>TEXT_YES,'0'=>TEXT_NO),(int)$cfg->get('change_col_width_in_listing'),array('class'=>'form-control input-small')) ?>
				</div>
			</form>   	
  </div>
</div>


<br><br>

<div class="row">
  <div class="col-md-8">
    <legend><?php echo TEXT_LISTING_HORISONTAL_SCROLL ?></legend>
    
    <div><?php echo TEXT_LISTING_HORISONTAL_SCROLL_INFO ?></div>
    
    <div>
      <form class="form-inline" role="form">
				<div class="form-group">
					<label for="number_fixed_field_in_listing"><?php echo TEXT_NUMBER_FIXED_FIELD ?></label>
					<?php echo input_tag('number_fixed_field_in_listing',(int)$cfg->get('number_fixed_field_in_listing'),array('class'=>'form-control input-xsmall')) ?>
				</div>
			</form>    
    </div>
    
    <div><?php echo TEXT_NUMBER_FIXED_FIELD_INFO ?></div>
                  
  </div>
</div>

<br>
<?php echo link_to(TEXT_BUTTON_BACK, url_for('entities/listing_types','entities_id=' . _get::int('entities_id')),array('class'=>'btn btn-default'))?>


<script>
  $(function() {         
    	$( "ul.sortable" ).sortable({
    		connectWith: "ul",
    		update: function(event,ui){  
          data = '';  
          $( "ul.sortable" ).each(function() {data = data +'&'+$(this).attr('id')+'='+$(this).sortable("toArray") });                            
          data = data.slice(1)                      
          $.ajax({type: "POST",url: '<?php echo url_for("entities/fields","action=sort_fields&entities_id=" . $_GET["entities_id"])?>',data: data});
        }
    	});


    	$("#heading_width_based_content").change(function(){
        $.ajax({type: "POST",
                url: '<?php echo url_for("entities/fields","action=set_heading_field_width&entities_id=" . $_GET["entities_id"])?>',
                data: {heading_width_based_content:$(this).val()}
               });
     })
     
     $("#change_col_width_in_listing").change(function(){
        $.ajax({type: "POST",
                url: '<?php echo url_for("entities/fields","action=set_change_col_width_in_listing&entities_id=" . $_GET["entities_id"])?>',
                data: {change_col_width_in_listing:$(this).val()}
               });
     })
      
      $("#number_fixed_field_in_listing").keyup(function(){
         $.ajax({type: "POST",
                 url: '<?php echo url_for("entities/fields","action=set_number_fixed_field_in_listing&entities_id=" . $_GET["entities_id"])?>',
                 data: {number_fields:$(this).val()}
                });
      })
      
      $("#heading_field_id").change(function(){
         $.ajax({type: "POST",
                 url: '<?php echo url_for("entities/fields","action=set_heading_field_id&entities_id=" . $_GET["entities_id"])?>',
                 data: {heading_field_id:$(this).val()}
                });
      })
      
  });  
</script>



