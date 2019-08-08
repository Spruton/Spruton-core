<?php
  
  $ch = curl_init();
  
  curl_setopt($ch, CURLOPT_URL, "https://spruton.com/current_version/version.txt");  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
  
  $response = curl_exec($ch);
  
  if(strlen($response)<10)
  {  	  
  	$app_current_version = $response;
  }
    
  curl_close($ch);
  
  