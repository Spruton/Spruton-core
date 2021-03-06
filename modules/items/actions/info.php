<?php

//check if item it not empty
if($current_item_id==0 and !strlen($app_module_action))
{
	redirect_to('dashboard/page_not_found');
}

//keep current listing page
if(isset($_GET['gotopage']))
{
	$listing_page_keeper[key($_GET['gotopage'])] = current($_GET['gotopage']);
}

$entity_info = db_find('app_entities',$current_entity_id);
$entity_cfg = new entities_cfg($current_entity_id);
$item_info = db_find('app_entity_' . $current_entity_id,$current_item_id);

if($app_redirect_to=='subentity' and $entity_cfg->get('redirect_after_click_heading','subentity')=='subentity')
{  	
  $entity_query = db_query("select id from app_entities where parent_id='" . db_input($current_entity_id) . "' order by sort_order, name");    
  while($entity = db_fetch_array($entity_query))
  {
  	if(isset($app_users_access[$entity['id']]) or $app_user['group_id']==0)
  	{	
    	redirect_to('items/items','path=' . $_GET['path'] . '/' . $entity['id']);
  	}
  }
}

//reset users notifications
users_notifications::reset($current_entity_id,$current_item_id);

$app_title = app_set_title($app_breadcrumb[count($app_breadcrumb)-1]['title']); 

switch($app_module_action)
{
  case 'preview_attachment_exel':
      
      $file = attachments::parse_filename(base64_decode($_GET['file'])); 
      require('includes/libs/PHPExcel/PHPExcel.php');
        
      $objPHPExcel = new PHPExcel();
              
      $objPHPExcel = PHPExcel_IOFactory::load($file['file_path']);
      
      $htmlfile = DIR_FS_UPLOADS . $file['file_sha1'] . '.html'; 
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
      $objWriter->save($htmlfile);
      
      $html = file_get_contents($htmlfile);
      
      $css = '
      <style type="text/css">
        .style1{
          white-space:nowrap;
        }
        
        table{
          border: 1px solid lightGray;
        }
        
        table td{
          border: 1px solid lightGray !important;
          vertical-align: top !important;
          padding: 2px;
        }
      </style>  
      ';
      
      $html = str_replace('</head>',$css . '</head>',$html);
      
      echo $html;
      
      @unlink($htmlfile);
      
      exit();
      
    break;
    
  case 'preview_user_photo':
  		$file = attachments::parse_filename(base64_decode($_GET['file']));
  		
  		if(is_file(DIR_WS_USERS . $file['file_sha1']))
  		{
  			$size = getimagesize(DIR_WS_USERS . $file['file_sha1']);
  			echo '<img width="' . $size[0] . '" height="' . $size[1] . '" src="' . DIR_WS_USERS . $file['file_sha1'] . '">';
  		}
  		exit();
  	break;
  case 'download_user_photo':
  	$file = attachments::parse_filename(base64_decode($_GET['file']));
    		  	
  	if(is_file(DIR_WS_USERS . $file['file_sha1']))
  	{  		
	  	header('Content-Description: File Transfer');
	  	header('Content-Type: application/octet-stream');
	  	header('Content-Disposition: attachment; filename='.$file['name']);
	  	header('Content-Transfer-Encoding: binary');
	  	header('Expires: 0');
	  	header('Cache-Control: must-revalidate');
	  	header('Pragma: public');
	  	
	  	flush();
	  		  	
	  	readfile(DIR_WS_USERS . $file['file_sha1']);
  	}
  	
  	exit();
  	break;
  case 'preview_attachment_image':
      $file = attachments::parse_filename(base64_decode($_GET['file']));
                                                                                                                                      
      if(is_file($file['file_path']))
      {
        $size = getimagesize($file['file_path']);
        echo '<img width="' . $size[0] . '" height="' . $size[1] . '"  src="' . url_for('items/info&path=' . $_GET['path']  ,'&action=download_attachment&preview=1&file=' . urlencode($_GET['file'])) . '">';
      }
      
      exit();
    break;
  case 'download_attachment':
      $file = attachments::parse_filename(base64_decode($_GET['file']));
      
      //check if using file storage for feild
      if(class_exists('file_storage') and isset($_GET['field']))
      {      	
      	file_storage::download_file(_get::int('field'), base64_decode($_GET['file']));      	
      }
                       
      if(is_file($file['file_path']))
      {
        if($file['is_image'] and isset($_GET['preview']))
        {                          
          $size = getimagesize($file['file_path']);                    
          header("Content-type: " . $size['mime']);
          header('Content-Disposition: filename="' . $file['name'] . '"');
          
          flush();
          
          readfile($file['file_path']);
        }
        elseif($file['is_pdf'] and isset($_GET['preview']))
        {                                                        
          header("Content-type: application/pdf");
          header('Content-Disposition: filename="' . $file['name'] . '"');
          
          flush();
          
          readfile($file['file_path']);
        }
        else
        {                     
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename='.$file['name']);
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($file['file_path']));
          
          flush();
                
          readfile($file['file_path']);          
        }
      }
      else
      {
        echo TEXT_FILE_NOT_FOUD;
      }
        
      exit();
    break;
    
  case 'download_all_attachments':
      $item_info = db_find('app_entity_' . $current_entity_id, $current_item_id);
      
      //check if attachments exist
      if(strlen($attachments = $item_info['field_' . $_GET['id']])>0)
      {
      	//check if using file storage for feild
      	if(class_exists('file_storage'))
      	{
      		file_storage::download_files(_get::int('id'), $attachments);
      	}
      	
        $zip = new ZipArchive();
        $zip_filename = "attachments-{$current_item_id}.zip";
        $zip_filepath = DIR_FS_UPLOADS . $zip_filename;                
        
        //open zip archive        
        $zip->open($zip_filepath, ZipArchive::CREATE);
                        
        //add files to archive                
        foreach(explode(',',$attachments) as $filename)
        {
          $file = attachments::parse_filename($filename);                                                                    
          $zip->addFile($file['file_path'],"/" . $file['name']);                                      
        }
        
        $zip->close();
        
        //check if zip archive created
        if (!is_file($zip_filepath)) 
        {
            exit("Error: cannot create zip archive in " . $zip_filepath );
        }
        
        //download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$zip_filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_filepath));
        
        flush();
              
        readfile($zip_filepath);   
        
        //delete temp zip archive file
        @unlink($zip_filepath);                      
      }
            
      exit();
    break;
  
}  