<link href="template/css/pages/pricing-tables.css" rel="stylesheet" type="text/css"/>

<div class="row margin-bottom-40">
	<!-- Pricing -->
	<div class="col-md-4">
		<div class="pricing hover-effect">
			<div class="pricing-head">
				<h3><?php echo TEXT_HEADING_EXTENSION ?>
				<span>
					 <?php echo TEXT_NEW_FEATURES_FOR_YOUR_BUSINESS ?>
				</span>
				</h3>
				<h4>
				<?php echo ($app_user['language']=='russian.php' ? '<a href="https://spruton.com/ru/extension" target="_blank"><img src="images/spruton_ext_box_ru.png"></a>':'<a href="https://spruton.com/extension" target="_blank"><img src="images/spruton_ext_box_en.png"></a>') ?>				
				</h4>
			</div>
			<ul class="pricing-content list-unstyled">
				<li>
					<i class="fa fa-thumbs-o-up"></i> <?php echo TEXT_ONE_OFF_CHARGE ?>
				</li>
				<li>
					<i class="fa fa-heart"></i> <?php echo TEXT_UPDATES_FOR_FREE?>
				</li>
				<li>
					<i class="fa fa-smile-o"></i> <?php echo TEXT_FREE_SUPPORT?>
				</li>				
			</ul>
			<div class="pricing-footer">
				<p style="padding: 7px 0;">
					 <?php echo sprintf(TEXT_EXTENSION_LICENSE_KEY_IFNO,str_replace('www.','',$_SERVER['HTTP_HOST'])) ?>
				</p>
				
				<?php echo '<a target="_balnk" href="https://spruton.com/' . ($app_user['language']=='russian.php' ? 'ru/':''). 'shopping_cart.php" class="btn btn-success">' . TEXT_BUY_EXTENSION . '</a>'?>				
			</div>
		</div>
	</div>
	
	<div class="col-md-4">
	
	<div class="portlet">
						<div class="portlet-title">
							<div class="caption">
								<b><?php echo TEXT_EXTENSION_FEATURES ?></b>
							</div>
							
						</div>
						<div class="portlet-body" style="display: block; min-height: 440px;">
							<p><?php echo TEXT_EXTENSION_FEATURES_INFO ?></p>
							
							<h4><?php echo TEXT_MAIN_FEATURES ?></h4>
							
							<ul style="list-style:none; padding-left: 0;">
								<?php 
								$html  = '';
								
								foreach(explode(',',TEXT_EXT_FEATURES_LIST) as $k=>$v)
								{
									
									$p = [
											'0'=>45,
											'1'=>36,
											'2'=>40,
											'3'=>41,
											'4'=>38,
											'5'=>51,
											'6'=>32,
											'7'=>62,
											'8'=>58,
											'9'=>52,
									];
																		
									
									$url = 'https://help.spruton.com/' . ($app_user['language']=='russian.php' ? 'ru/':''). 'index.php?p=' . (isset($p[$k]) ? $p[$k]:4);
					
									$html .= '
											<li style="padding: 5px 0;"><a href="' . $url . '" target="_blank"><i class="fa fa-check" aria-hidden="true"></i> ' . $v. '</a></li>
										';
								}
								
								echo $html;
								?>															
							</ul>
							<center><a href="https://spruton.com//<?php echo ($app_user['language']=='russian.php' ? 'ru/':'')?>extension.html" target="_blank" class="btn btn-primary"><?php echo TEXT_FULL_LIST_OF_FEATURES ?></a></center>
						</div>
					</div>
					
	</div>
	
	<div class="col-md-4">
									
	</div>
	
</div>

<ul class="list-inline">
<?php	
	if($app_user['language']=='russian.php')
	{
		echo '
				<li style="padding: 10px;">
						<a href="http://help.spruton.com/ru/info/kanban/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ru/ext-home-1.jpg" alt="" /></a>
				</li>
				<li style="padding:10px;">
						<a href="http://help.spruton.com/ru/info/gantt-chart/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ru/ext-home-2.jpg" alt="" /></a>
				</li>
	      <li style="padding:10px;">
						<a href="http://help.spruton.com/ru/info/link-diagram/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ru/ext-home-3.jpg" alt="" /></a>
				</li>
			';
	}
	else
	{
		echo '
				<li style="padding: 10px;">
						<a href="http://help.spruton.com/info/gantt-chart/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ext-home-1.jpg" alt="" /></a>
				</li>
				<li style="padding:10px;">
						<a href="http://help.spruton.com/info/funnel-chart/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ext-home-2.jpg" alt="" /></a>
				</li>												
	      <li style="padding:10px;">
						<a href="http://help.spruton.com/info/graphic-report/" target="_blank"><img style="border: 1px solid #adadad" src="images/sliders/ext-home-3.jpg" alt="" /></a>
				</li>
			';
	}
?>
</ul>


