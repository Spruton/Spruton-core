<?php

if(isset($_POST['field_type']))
{
  $field_type = new $_POST['field_type'];
    
  if(method_exists($field_type,'get_ajax_configuration'))
  {  
    echo fields_types::render_configuration($field_type->get_ajax_configuration($_POST['name'],$_POST['value']),$_POST['id']);    
  }
  
}

exit();