<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0" />
		<title></title>
		<link rel="icon" href="favicon.ico" />
		
		
		<?php echo i18n_js() ?>
		
		<script src="template/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
		
		<?php 
		if($mind_map->is_report())
		{
			echo '<script src="js/mindmap-master/my-mind-reports.js"></script>';
		}
		else
		{
			echo '<script src="js/mindmap-master/my-mind.js"></script>';
		}
		?>
		
		 
		<link href="template/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
		 
		 
		<link rel="stylesheet" href="js/mindmap-master/css/font.css" />		
		<link rel="stylesheet" href="js/mindmap-master/css/style.css" />		
		<link rel="stylesheet" href="js/mindmap-master/css/print.css" media="print" />
		<link href="template/plugins/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet" type="text/css"/>
				
		
		
				
	</head>
	<body>
					
		<ul id="port">
			<div id="tip"><?php echo TEXT_MIND_MAP_START_TIP ?></div>
		</ul>

		<div class="ui" id="mm_ui_settings" style="display:none">			
			<p>
				<button  class="btn btn-default" id="mm_btn_save" style="width: auto" title="<?php echo TEXT_SAVE ?>"><i class="fa fa-floppy-o"></i> </button>
				
				<?php if(!$mind_map->is_report()): ?>
					<button data-command="New" class="btn btn-default"  style="width: auto"><i class="fa fa-file-o"></i> <?php echo TEXT_RESET ?></button>
				<?php endif; ?>
			</p>

			<p>
				<span><?php echo TEXT_LAYOUT ?></span>
				<select id="layout">
					<option value="">(<?php echo TEXT_INHERIT ?>)</option>
				</select>
			</p>
			<p>
				<span><?php echo TEXT_SHAPE ?></span>
				<select id="shape">
					<option value="">(<?php echo TEXT_AUTOMATIC ?>)</option>
				</select>
			</p>
			
<?php 
	if($mind_map->is_report())
	{
		echo $mind_map->render_legend();
	}
?>			
			
<div <?php echo ($mind_map->is_report() ? 'style="display:none"':'') ?> >			
			<p>
				<span><?php echo TEXT_VALUE ?></span>
				<select id="value">
					<option value=""><?php echo TEXT_NONE ?></option>
					<option value="num"><?php echo TEXT_NUMBER ?></option>
					<optgroup label="<?php echo TEXT_FORMULA ?>">
						<option value="sum"><?php echo TEXT_SUM ?></option>
						<option value="avg"><?php echo TEXT_AVERAGE ?></option>
						<option value="min"><?php echo TEXT_MINIMUM ?></option>
						<option value="max"><?php echo TEXT_MAXIMUM ?></option>
					</optgroup>
				</select>
			</p>
			<p style="display:none">
				<span>Status</span>
				<select id="status">
					<option value="">None</option>
					<option value="yes">Yes</option>
					<option value="no">No</option>
					<option value="computed">Autocompute</option>
				</select>
			</p>
			<p>
				<span><?php echo TEXT_COLOR ?></span>
				<span id="color">
					<a data-color="" href="#"></a>
					<a data-color="#000" href="#"></a>
					<a data-color="#930" href="#"></a>
					<a data-color="#e33" href="#"></a>
					<a data-color="#f60" href="#"></a>
					<a data-color="#396" href="#"></a>
					<a data-color="#9c0" href="#"></a>
					<a data-color="#008080" href="#"></a>															
					<a data-color="#3e3" href="#"></a>
					
					<a data-color="#33e" href="#"></a>
					<a data-color="#3dd" href="#"></a>
					<a data-color="#3cc" href="#"></a>
					<a data-color="#fa3" href="#"></a>
					<a data-color="#f9c" href="#"></a>
					<a data-color="#fc9" href="#"></a>										
					<a data-color="#dd3" href="#"></a>				
					<a data-color="#d3d" href="#"></a>
					
					<a data-color="#800080" href="#"></a>
				</span>
			</p>

			<p>
				<span><?php echo TEXT_ICON ?></span>
				
				<ol class="icons">
					<li><i class="fa fa-file-o"></i></li>
					<li><i class="fa fa-check"></i></li>
					<li><i class="fa fa-close"></i></li>
					<li><i class="fa fa-star"></i></li>
					<li><i class="fa fa-warning"></i></li>
					<li><i class="fa fa-bell"></i></li>
					<li><i class="fa fa-question"></i></li>
					<li><i class="fa fa-info-circle"></i></li>
					<li><i class="fa fa-heart"></i></li>					
					<li><i class="fa fa-comment"></i></li>
					<li><i class="fa fa-handshake-o"></i></li>
					<li><i class="fa fa-cog"></i></li>					
					<li><i class="fa fa-phone"></i></li>
					<li><i class="fa fa-search"></i></li>
					<li><i class="fa fa-user"></i></li>
					<li><i class="fa fa-smile-o"></i></li>					
				</ol>
				
				<select id="icons" class="fa-select" style="display:none">						
						<option value=''></option>											
						<option value='fa-check'>&#xf00c; </option>																		
						<option value='fa-close'>&#xf00d; </option>
						<option value='fa-star'>&#xf005; </option>
						<option value='fa-warning'>&#xf071; </option>
						<option value='fa-bell'>&#xf0f3; </option>
						<option value='fa-flag'>&#xf024; </option>
						<option value='fa-info-circle'>&#xf05a; </option>
						<option value='fa-heart'>&#xf004; </option>
						<option value='fa-question'>&#xf128; </option>
						<option value='fa-comment'>&#xf075; </option>					
						<option value='fa-handshake-o'>&#xf2b5; </option>
						<option value='fa-cog'>&#xf013; </option>																							
						<option value='fa-phone'>&#xf095; </option>						
						<option value='fa-search'>&#xf002; </option>
						<option value='fa-user'>&#xf007; </option>
						<option value='fa-smile-o'>&#xf118; </option>																													
					</select>
			</p>
</div>	
			<button id="toggle" title=""></button>
			

			<div id="throbber"></div>
		</div>


		<div id="io" class="ui">
			<h3></h3>
			<p>
				<span>Storage</span>
				<select id="backend"></select>
			</p>
			
			<div id="file">
				<p class="desc">Local files are suitable for loading/saving files from other mindmapping applications.</p>
				<p data-for="save">
					<span>Format</span>
					<select class="format"></select>
				</p>
				<p data-for="save load">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
			</div>

			<div id="image">
				<p class="desc">Export your design as a PNG image.</p>
				<p data-for="save">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
			</div>

			<div id="local">
				<p class="desc">Your browser's localStorage can handle many mind maps and creates a permalink, but this URL cannot be shared.</p>
				<p data-for="load">
					<span>Saved maps</span>
					<select class="list"></select>
				</p>
				<p data-for="save load">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
				<p data-for="load">
					<button class="remove">Delete</button>
				</p>
			</div>

			<div id="firebase">
				<p class="desc">Firebase offers real-time synchronization for true multi-user collaboration.</p>
				<p data-for="save load">
					<span>Server</span>
					<input type="text" class="server" />
				</p>
				<p data-for="save load">
					<span>Auth</span>
					<select class="auth">
						<option value="">(None)</option>
						<option value="facebook">Facebook</option>
						<option value="twitter">Twitter</option>
						<option value="github">GitHub</option>
						<option value="persona">Persona</option>
					</select>
				</p>
				<p data-for="load">
					<span>Saved maps</span>
					<select class="list"></select>
				</p>
				<p data-for="save load">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
				<p data-for="load">
					<button class="remove">Delete</button>
				</p>
			</div>

			<div id="webdav">
				<p class="desc">Use this to access a generic DAV-like REST API.</p>
				<p data-for="save load">
					<span>URL</span>
					<input type="text" class="url" />
				</p>
				<p data-for="save load">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
			</div>

			<div id="gdrive">
				<p class="desc">Maps stored in Google Drive have a permalink URL and can be shared with other users, if you allow this by setting proper permissions (inside Google Drive itself).</p>
				<p data-for="save">
					<span>Format</span>
					<select class="format"></select>
				</p>
				<p data-for="save load">
					<button class="go"></button><button class="cancel">Cancel</button>
				</p>
			</div>
		</div>

		<div id="help" class="ui">
			<h3><?php //echo TEXT_HELP ?></h3>

			<p><span><?php //echo TEXT_NAVIGATION ?></span></p>
			<table class="navigation"></table>

			<p><span><?php //echo TEXT_MANIPULATION ?></span></p>
			<table class="manipulation"></table>

			<p><span><?php //echo TEXT_EDITING ?></span></p>
			<table class="editing"></table>

			<p><span><?php //echo TEXT_OTHER ?></span></p>
			<table class="other"></table>
		</div>
		
		<div id="menu" style="display:none">
			<button data-command="InsertChild"></button>
			<button data-command="InsertSibling"></button>
			<button data-command="Delete"></button>
			<span></span>
			<button data-command="Edit"></button>
			<button data-command="Value"></button>
			<span></span>
			<button data-command="Undo"></button>
			<button data-command="Redo"></button>
			<button data-command="Center"></button>
		</div>


<?php 
      
//include module views  
  if(is_file($path = $app_plugin_path . 'modules/' . $app_module . '/views/' . $app_action . '.php'))
  {  	
    require($path);
  }   
?>

		<input type="hidden" id="is_mind_map_updated" value="0">
		
		<script>
		  			 
			window.onload = function() {

				//check if map editable
				MM.App.is_editable = $('#mind_map_options').attr('data-is-editable')=='0' ? false : true;

				//show menu if editable
				if(MM.App.is_editable)
				{
					$('#mm_ui_settings').show();
				}
								
				MM.App.init();				
				MM.App.io.restore();

				//load map
				if(mind_map_json.length>0)
				{	
					MM.UI.Backend._loadDone(JSON.parse(mind_map_json))
				}

				appHandlePopover();

				if(MM.App.is_editable)
				{	
		      setInterval("mind_map_auto_save()",10000);
				}
				else
				{
					$('#tip').hide();
				}

				//reset save button
				$('#is_mind_map_updated').val(0)		
				$('#mm_btn_save').removeClass('btn-primary')				
				window.onbeforeunload = '';		
				
			}

			function mind_map_auto_save()
			{
				if($('#is_mind_map_updated').val()==1)
				{
					//reset save button
					$('#is_mind_map_updated').val(0)
					$('#mm_btn_save').removeClass('btn-primary')
					window.onbeforeunload = '';

					//get map json
					data = MM.App.map.toJSON();
					console.log(data);

					//save data					
					$.ajax({
					  method: "POST",
					  url: $('#mind_map_options').attr('data-url'),
					  data: data
					})
					
				}				
			}
			
			$(function(){

				//Stop double-scrolling propagation
				$(window).on("DOMMouseScroll mousewheel", function(ev) {
					ev.stopPropagation();
	        ev.preventDefault();
	        ev.returnValue = false;
	        return false;
				})

				//btn save click
				$('#mm_btn_save').click(function(){
					mind_map_auto_save();
				})  

				//ctr+s click to save map
				$(window).bind('keydown', function(event) {
			    if (event.ctrlKey || event.metaKey) {
			        switch (String.fromCharCode(event.which).toLowerCase()) {
			        case 's':
			            event.preventDefault();
			            mind_map_auto_save();
			            break;
			        }
			    }
				});

				//handle icons click
				$('.icons .fa').click(function(){
					value = $(this).attr('class').replace('fa ','')
					value = (value=='fa-file-o' ? '':value)
					$('#icons').val(value).trigger("change");
					
				})

				$('#icons').change(function(){					
					var action = new MM.Action.SetIcon(MM.App.current, $(this).val() || null);
				  MM.App.action(action);
				})

				$('body').click(function(){
					$('#tip').hide();
				});
				                                                               
		  });
		</script>
<!--
TODO:
  shortterm:

  longterm:
    - firebase realtime
    - (custom) icons

  bugs:

  only as a request:
	- firebase multiserver
    - l11n
    - custom css
-->

	<script src="template/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="template/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.js?v=2.2.1" type="text/javascript"></script>
	
<script>

function appHandlePopover()
{
  $('[data-toggle="popover"]').popover({trigger:'hover',html:true,
     placement: function (context, source) {
        var position = $(source).position();
         
        return "bottom";
    }  
  })
}
</script>	

	</body>
</html>
