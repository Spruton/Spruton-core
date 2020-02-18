<?php

class fieldtype_input_file
{
  public $options;
  
  function __construct()
  {
    $this->options = array('title' => TEXT_FIELDTYPE_INPUT_FILE_TITLE);
  }   
  
  function get_configuration()
  {
  	$cfg = array();
  
  	$cfg[] = array('title'=>TEXT_ALLOW_SEARCH, 'name'=>'allow_search','type'=>'checkbox','tooltip_icon'=>TEXT_ALLOW_SEARCH_TIP);  	
  	$cfg[] = array('title'=>TEXT_ALLOWED_EXTENSIONS, 'name'=>'allowed_extensions','type'=>'input','tooltip_icon'=>TEXT_ALLOWED_EXTENSIONS_TIP,'params'=>array('class'=>'form-control input-large'));
  	
  	return $cfg;
  }
  	
    
  function render($field,$obj,$params = array())
  {
    $filename = $obj['field_' . $field['id']];
    $html = '';
    if(strlen($filename)>0)
    {
      $file = attachments::parse_filename($filename);
      $html = '
        <div>' .  $file['name'] . input_hidden_tag('files[' . $field['id'] . ']',$filename) . '</div>
        ' . (users::has_access('delete') ? '<div><label class="checkbox">' . input_checkbox_tag('delete_files[' . $field['id'] . ']',1) . ' ' . TEXT_DELETE . '</label></div>':'');
           
    }
    
    $cfg = new fields_types_cfg($field['configuration']);
                
   return input_file_tag('fields[' . $field['id'] . ']',fieldtype_attachments::get_accept_types($cfg)+array('class'=>'form-control input-large fieldtype_input_file field_' . $field['id']  . (($field['is_required']==1 and !strlen($filename)) ? ' required':''))) . $html;   
   
  }
  
  function process($options)
  {    
    global $alerts;
          
    $field_id = $options['field']['id'];  
    
    if(isset($_POST['delete_files'][$field_id]))
    {
      $file = attachments::parse_filename($_POST['files'][$field_id]);
      if(is_file(DIR_WS_ATTACHMENTS . $file['folder'] .'/'. $file['file_sha1']))
      {
        unlink(DIR_WS_ATTACHMENTS . $file['folder']  .'/' . $file['file_sha1']);
      }
      
      //delete files from file storage
      if(class_exists('file_storage'))
      {
      	$file_storage = new file_storage();
      	$file_storage->delete_files($field_id, array($file['file']));
      }
                  
      return '';
    }
    
           
    if(strlen($_FILES['fields']['name'][$field_id])>0)
    {     
      $file = attachments::prepare_filename($_FILES['fields']['name'][$field_id]);
                          
      if(move_uploaded_file($_FILES['fields']['tmp_name'][$field_id], DIR_WS_ATTACHMENTS  . $file['folder']  .'/'. $file['file']))
      {      
      	//autoresize images if enabled
      	attachments::resize(DIR_WS_ATTACHMENTS  . $file['folder']  .'/'. $file['file']);
      	
      	//add file to queue
      	if(class_exists('file_storage'))
      	{
      		$file_storage = new file_storage();
      		$file_storage->add_to_queue($field_id, $file['name']);
      	}
      	
        return $file['name'];
      }
      else
      {
        return '';
      }                         
    }
    elseif(isset($_POST['files'][$field_id]))
    {
      return $_POST['files'][$field_id];
    }
    else
    {
      return '';
    }    
  }
  
  function output($options)
  {  	
  	$options_cfg = new fields_types_options_cfg($options);
    
    if(strlen($options['value'])>0)
    {  
    	$use_file_storage = false;
    	
    	//check if field using file storage
    	if(class_exists('file_storage'))
    	{
    		$use_file_storage = file_storage::check($options['field']['id']);
    	}
    	
    	
      $file = attachments::parse_filename($options['value']);
      
      if(isset($options['is_public_form']))
      {
      	return link_to($file['name'],url_for('ext/public/check','action=download_attachment&id=' . $options['is_public_form'] . '&item=' . $options['item']['id'] . '&field=' . $options['field']['id'] . '&file=' . urlencode(base64_encode($options['value'])) . '&field=' . $options['field']['id']),array('target'=>'_blank')) . (!$use_file_storage ? '  <small>(' . $file['size']. ')</small>' : '');
      }
      elseif(isset($options['is_email']))
      {      	      	
      	if($options_cfg->get('hide_attachments_url')==1)
      	{
      		return $file['name'];
      	}
      	else
      	{
      		return link_to($file['name'],url_for('items/info','path=' . $options['path'] . '&action=download_attachment&file=' . urlencode(base64_encode($options['value'])) . '&field=' . $options['field']['id']),array('target'=>'_blank')) . (!$use_file_storage ? ' <small>(' . $file['size']. ')</small>':'');
      	}
      }
      elseif(isset($options['is_export']))
      {
        return $file['name'];    
      }
      else
      {         
        return '<img src="' . $file['icon'] . '"> ' . link_to($file['name'],url_for('items/info','path=' . $options['path'] . '&action=download_attachment&file=' . urlencode(base64_encode($options['value'])) . '&field=' . $options['field']['id']),array('target'=>'_blank')) . (!$use_file_storage ? ' <small>(' . $file['size']. ')</small>':'');
      }
    	
    }
    else
    {
      return '';
    }
  }
}