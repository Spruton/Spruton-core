<?php

$reports_info = db_find('app_reports',$_POST['reports_id']);

//print_rr($reports_info);

$fields_access_schema = users::get_fields_access_schema($current_entity_id,$app_user['group_id']);
$current_entity_info = db_find('app_entities',$current_entity_id);
$entity_cfg = entities::get_cfg($current_entity_id);

$listing = new items_listing($_POST['reports_id'],$entity_cfg);

$user_has_comments_access = users::has_comments_access('view');
      
$html = '';

$listing_sql_query_select = '';
$listing_sql_query = '';
$listing_sql_query_join = '';
$listing_sql_query_from = '';
$listing_sql_query_having = '';
$sql_query_having = array();

if(!isset($_POST['search_keywords'])) $_POST['search_keywords'] = '';
if(!isset($_POST['search_reset'])) $_POST['search_reset'] = '';
if(!isset($_POST['force_display_id'])) $_POST['force_display_id'] = '';
if(!isset($_POST['force_popoup_fields'])) $_POST['force_popoup_fields'] = '';
if(!isset($_POST['force_filter_by'])) $_POST['force_filter_by'] = '';

//prepare forumulas query
$listing_sql_query_select = fieldtype_formula::prepare_query_select($current_entity_id, $listing_sql_query_select,false,array('reports_id'=>$_POST['reports_id']));

//prepare count of related items in listing
$listing_sql_query_select = fieldtype_related_records::prepare_query_select($current_entity_id, $listing_sql_query_select, $reports_info);


//add search query and skip filters to search in all items
if(strlen($_POST['search_keywords'])>0)
{
  $html .= '<div class="note note-info search-notes">' . sprintf(TEXT_SEARCH_RESULT_FOR,htmlspecialchars($_POST['search_keywords'])) . ' <span onClick="listing_reset_search(\'' . $_POST['listing_container'] . '\')" class="reset_search">' . TEXT_RESET_SEARCH . '</span></div>';
  require(component_path('items/add_search_query')); 
}

if(strlen($_POST['search_keywords'])>0 or $_POST['search_reset']=='true')
{
	//save search settings for current report
	listing_search::save($_POST['reports_id']);
}

//default search include reports fitlers
//if flga "search_in_all" = true we exlude fitlers from search
if((strlen($_POST['search_keywords'])>0 and $_POST['search_in_all']=='true') or strlen($_POST['force_display_id']))
{
	//skip filters if there is search keyworkds and option search_in_all in 
}
else
{
  //add filters query
  if(isset($_POST['reports_id']))
  {  	
    $listing_sql_query = reports::add_filters_query($_POST['reports_id'],$listing_sql_query);
        
    //prepare having query for formula fields
    if(isset($sql_query_having[$current_entity_id]))
    {    	
    	$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$current_entity_id]);
    }
  }
}


//filter items by parent
if($parent_entity_item_id>0)
{
  $listing_sql_query .= " and e.parent_item_id='" . db_input($parent_entity_item_id) . "'";
}

//exclude admin users from listing for not admin users
if($current_entity_id==1 and $app_user['group_id']>0)
{
	$listing_sql_query .= " and e.field_6>0";
}

//force display items by ID
if(strlen($_POST['force_display_id']))
{
	$listing_sql_query .= " and e.id in (" . $_POST['force_display_id'] . ")";
}

//force extra filter
if(strlen($_POST['force_filter_by']))
{
	$listing_sql_query .= reports::force_filter_by($_POST['force_filter_by']);
}	

//check view assigned only access
$listing_sql_query = items::add_access_query($current_entity_id,$listing_sql_query,$listing->force_access_query);

//add having query
$listing_sql_query .= $listing_sql_query_having;

//add order_query
$listing_order_fields_id = array();
$listing_order_fields = array();
$listing_order_clauses = array();

if(strlen($_POST['listing_order_fields'])>0)
{  
  $info = reports::add_order_query($_POST['listing_order_fields'],$current_entity_id);
      
  $listing_order_fields_id = $info['listing_order_fields_id'];
  $listing_order_fields = $info['listing_order_fields'];
  $listing_order_clauses = $info['listing_order_clauses'];
  
  $listing_sql_query .= $info['listing_sql_query'];
  $listing_sql_query_join .= $info['listing_sql_query_join'];  
  $listing_sql_query_from .= $info['listing_sql_query_from'];
    
  if(isset($_POST['listing_order_fields_changed']))
  if($_POST['listing_order_fields_changed']==1 and $reports_info['reports_type']!='default')
  {
  	db_query("update app_reports set listing_order_fields = '" . db_input($_POST['listing_order_fields']) . "' where id='" . $_POST['reports_id'] . "'");  	
  }
}

$reports_entities_id = (isset($_POST['reports_entities_id']) ? $_POST['reports_entities_id'] : 0);

$has_with_selected = (isset($_POST['has_with_selected']) ? $_POST['has_with_selected'] : 0);

if(!isset($app_selected_items[$_POST['reports_id']]))
{
	$app_selected_items[$_POST['reports_id']] = array();
}

//setup unread items
$users_notifications = new users_notifications($current_entity_id);

//render listing body
$listing_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . $current_entity_id . " e "  . $listing_sql_query_join . $listing_sql_query_from . " where e.id>0 " . $listing_sql_query;

//if there is having query then use db_num_rows function to calculate num rows
if(strlen($listing_sql_query_having)>0)
{
	$count_sql = 'query_num_rows';
}
else
{
	$count_sql = "select count(e.id) as total from app_entity_" . $current_entity_id . " e "  . $listing_sql_query_join . " where e.id>0 " . $listing_sql_query;
}

//$count_sql = 'query_num_rows';

$listing_split = new split_page($listing_sql,$_POST['listing_container'],$count_sql, $listing->rows_per_page);

$items_query = db_query($listing_split->sql_query,false);


switch($listing->get_listing_type())
{
	case 'list':
		require(component_path('items/_listing_list'));
		break;
	case 'grid':
		require(component_path('items/_listing_grid'));
		break;
	case 'mobile':
		require(component_path('items/_listing_mobile'));
		break;
	case 'table':
		require(component_path('items/_listing_table'));
		break;
}


