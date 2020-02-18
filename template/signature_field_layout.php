<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
    
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">

  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  
  <link href="template/plugins/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet" type="text/css"/>
  <link href="template/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

  <link rel="stylesheet" href="js/signature_pad-master/css/signature-pad.css?v=1">

  <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="js/signature_pad-master/css/ie9.css">
  <![endif]-->
  
  <script src="template/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>


</head>
<body onselectstart="return false">

<?php 
	$cfg = new fields_types_cfg($app_fields_cache[$current_entity_id][_get::int('fields_id')]['configuration']);
	
	$button_title = (strlen($cfg->get('button_title')) ? $cfg->get('button_title') : TEXT_APPROVE);
?>

  <div id="signature-pad" class="signature-pad">
  	<div style="margin-bottom: 15px;"><?php echo input_tag('name','',['class'=>'form-control','placeholder'=>TEXT_ENTER_YOUR_NAME]) ?></div>
  	
    <div class="signature-pad--body">
      <canvas></canvas>
    </div>
    <div class="signature-pad--footer">
      
      <div class="signature-pad--actions">
        <div>
          <button type="button" class="btn btn-default btn-sm" data-action="clear"><i class="fa fa-undo" aria-hidden="true"></i> <?php echo TEXT_CLEAR ?></button>          
          <button type="button" class="btn btn-default btn-sm" data-action="undo"><i class="fa fa-angle-left" aria-hidden="true"></i> <?php echo TEXT_UNDO ?></button>

        </div>
        <div> 
        	<div class="fa fa-spinner fa-spin primary-modal-action-loading" style="display:none; color: black; font-size: 16px;"></div>        
          <button type="button" class="btn btn-primary" id="save-png" data-action="save-png"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $button_title ?></button>
        </div>
      </div>
    </div>
  </div>
  <div id="load"></div>

  <script src="js/signature_pad-master/js/signature_pad.umd.js"></script>
  <script src="js/signature_pad-master/js/app.js?v=1"></script>
  
<script>
	savePNGButton.addEventListener("click", function (event) {
		if($('#name').val().trim().length==0)
		{
			alert("<?php echo addslashes(TEXT_ENTER_YOUR_NAME) ?>");
		}
		else if (signaturePad.isEmpty()) {
	    alert("<?php echo addslashes(TEXT_PLEASE_PROVIDE_SIGNATURE) ?>");
	  } else {
	    var dataURL = signaturePad.toDataURL();

	    $('#save-png').hide()
	    $('.primary-modal-action-loading').show()
	    
	    
	    $('#load').load('<?php echo url_for('items/signature_field','action=singature&fields_id=' . _get::int('fields_id') . '&path=' . $app_path . '&redirect_to=' . $app_redirect_to) ?>',{signature:dataURL,name:$('#name').val()})
	    	    
	  }
	});
</script>
</body>
</html>