<!DOCTYPE html>
<html lang="en" >
  <head>
    <meta charset="utf-8">
    <title></title>	
		<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />

		<link href="template/plugins/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet" type="text/css"/>
		
    <link href="js/image-map/common/css/bootstrap.min.css" rel="stylesheet">
    <link href="js/image-map/common/css/bootstrap-modal.css" rel="stylesheet">
    <link href="js/image-map//common/css/leaflet.css" rel="stylesheet">    
	
		<link href="js/image-map/common/css/map-elements.css" rel="stylesheet">
	  <link href="js/image-map/common/css/common.css" rel="stylesheet">
		<link href="js/image-map/admin/css/admin.css" rel="stylesheet">	
	
	<!-- head js library  -->
    <script src="js/image-map/common/js/head.min.js"></script>
    
       
  </head>

  <body>
  
	<div class="navbar navbar-fixed-top">			
		<div class="navbar navbar-fixed-top">
		  <div id="mainNavigation" class="navbar-inner hide">
			<div class="container" >    
							
			<ul  class="nav">		
				<li class="hide">
					<a href="#mapViewTab" ></a>
				</li>			
			 </ul>
		  </div>
		  </div>
	  </div>
		 	 
<?php 
      
//include module views  
  if(is_file($path = $app_plugin_path . 'modules/' . $app_module . '/views/' . $app_action . '.php'))
  {  	
    require($path);
  }   
?>	 

	</div>
<?php echo i18n_js() ?>

	
  </body>
</html>
