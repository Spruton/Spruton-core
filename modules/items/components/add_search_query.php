<?php

$search_fields = fields::get_search_feidls($current_entity_id);

$use_search_fields = array();

if(strlen($_POST['use_search_fields'])>0)
{
  $use_search_fields = explode(',',$_POST['use_search_fields']);
}

if(count($search_fields)>0)
{  
  if(app_parse_search_string($_POST['search_keywords'], $search_keywords))
  {
    //print_r($search_keywords);
    
    $sql_query = array();
    
    /**
     *  search in fields
     */         
    foreach($search_fields as $field)
    {
      //skip fileds that are not checked for search
      if(count($use_search_fields)>0 and !in_array($field['id'],$use_search_fields)) continue;
    
      //handle search by ID
      if($field['type']=='fieldtype_id')
      {
      	if(is_numeric($search_keywords[0]))
      		$sql_query[] = "e.id='" . db_input($search_keywords[0]) . "'";
      }
      //handle search by phone
      elseif($field['type']=='fieldtype_phone')
      {
          if(strlen(preg_replace('/\D/', '', $_POST['search_keywords'])))
          {
              $sql_query[] = "rukovoditel_regex_replace('[^0-9]','',e.field_" . $field['id'] . ") like '%" . db_input(preg_replace('/\D/', '', $_POST['search_keywords'])) . "%'";
          }
      }
      //handle search by entity
      elseif($field['type']=='fieldtype_entity')
      {      	
      	$cfg = new fields_types_cfg($field['configuration']);      	
      	if($heading_field_id = fields::get_heading_id($cfg->get('entity_id')))
      	{	          		
      		$where_str = "select es.id from app_entity_" . $cfg->get('entity_id') . " as es where es.id='" . (int)$_POST['search_keywords'] . "'";
      		
      		$where_str .= " or (";
      		for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ )
      		{
      			switch ($search_keywords[$i])
      			{
      				case '(':
      				case ')':
      					$where_str .= " " . $search_keywords[$i] . " ";
      					break;
      				case 'and':
      				case 'or':
      					$search_type = ($_POST['search_type_and']=='true' ? 'and' : $search_keywords[$i]);
      					$where_str .= " " . $search_type . " ";
      					break;
      				default:
      					$keyword = $search_keywords[$i];
      		
      					if($_POST['search_type_match']=='true')
      					{
      						$where_str .= "es.field_" . $heading_field_id . " REGEXP '[[:<:]]" . db_input($keyword) . "[[:>:]]'";
      					}
      					else
      					{
      						$where_str .= "es.field_" . $heading_field_id . " like '%" . db_input($keyword) . "%'";
      					}
      					break;
      			}
      		}
      		$where_str .= ")";
      	}
      	else
      	{
      		$where_str = (int)$_POST['search_keywords'];
      	}
      	
      	$sql_query[] = "(select count(*) from app_entity_" . $current_entity_id . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($field['id'])  . "' and cv.value in (" . $where_str . "))>0" ;
      }
      elseif (isset($search_keywords) && (sizeof($search_keywords) > 0)) 
      {
        $where_str = "(";
        for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) 
        {
          switch ($search_keywords[$i]) 
          {
            case '(':
            case ')':
            	$where_str .= " " . $search_keywords[$i] . " ";
            	break;
            case 'and':
            case 'or':
            	$search_type = ($_POST['search_type_and']=='true' ? 'and' : $search_keywords[$i]);
              $where_str .= " " . $search_type . " ";
              break;
            default:
              $keyword = $search_keywords[$i];
              
              if($_POST['search_type_match']=='true')
              {
              	$where_str .= "e.field_" . $field['id'] . " REGEXP '[[:<:]]" . db_input($keyword) . "[[:>:]]'";
              }
              else 
              {
              	$where_str .= "e.field_" . $field['id'] . " like '%" . db_input($keyword) . "%'";
              }
              break;
          }
        }
        $where_str .= ")";
        
        $sql_query[] = $where_str;
      }                            
    }
               
    
    /**
     *  Search in comments
     */
    if(isset($_POST['search_in_comments']))
    if($_POST['search_in_comments']=='true')
    {
      $where_str = "(select count(*) as total from app_comments as ec where ec.entities_id='" . $current_entity_id . "' and ec.items_id=e.id";
      
      if (isset($search_keywords) && (sizeof($search_keywords) > 0)) 
      {
        $where_str .= " and (";
        for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) 
        {
          switch ($search_keywords[$i]) 
          {
            case '(':
            case ')':
            	$where_str .= " " . $search_keywords[$i] . " ";
            	break;
            case 'and':
            case 'or':
              $search_type = ($_POST['search_type_and']=='true' ? 'and' : $search_keywords[$i]);
              $where_str .= " " . $search_type . " ";
              break;
            default:
              $keyword = $search_keywords[$i];
              $where_str .= "ec.description like '%" . db_input($keyword) . "%'";
              break;
          }
        }
        $where_str .= ")";                
      }      
      
      $where_str .= ")>0";
      
      $sql_query[] = $where_str;
    }
    
    if(count($sql_query)>0)
    {                  
      //print_r($sql_query);
                        	    
      $listing_sql_query .= ' and (' . implode(' or ', $sql_query) . ')';
      
      //echo $listing_sql_query;
    }        
  }
  else
  {
    $html .= '<div class="alert alert-danger">' . TEXT_ERROR_INVALID_KEYWORDS . '</div>';
    
    echo $html;
    exit();
  } 
}