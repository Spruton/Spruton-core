<?php

$html = '
				<div class="table-scrollable">
			    <table class="table table-striped table-bordered table-hover" data-fixed-head="1">
			      <thead>
			        <tr>
								<th>' . TEXT_ENTITY . '</th>
								<th width="100%">' . TEXT_TITLE . '</th>
							</tr>
						</thead>
						<tbody>';
	
while($items = db_fetch_array($items_query))
{

	if($app_entities_cache[$items['entities_id']]['parent_id']>0)
	{
		$path_info = items::get_path_info($items['entities_id'],$items['id']);
		$title = $path_info['parent_name'] . ' <i class="fa fa-angle-right"></i> ' . $items['title'];
	}
	else
	{
		$title = $items['title'];
	}
	$html .= '
						<tr>
							<td>' . $app_entities_cache[$items['entities_id']]['name'] . '</td>
							<td style="white-space:normal"><a href="' . url_for('items/info','path=' . $items['entities_id'] . '-' . $items['id']) . '">' . $title . '</a>
									<small>' . global_search::render_fields_in_listing(
											$items['entities_id'],
											$items['id'],
											$entities_cfg_holder[$items['entities_id']]['fields_in_listing'],
											$_POST['search_in_comments'],
											$entities_cfg_holder[$items['entities_id']]['cfg'],
											$entities_cfg_holder[$items['entities_id']]['heading_field_id']). '</small>
							</td>
						</tr>
						';
}
	
if($listing_split->number_of_rows==0)
{
	$html .= '
			    <tr>
			      <td colspan="100">' . TEXT_NO_RECORDS_FOUND . '</td>
			    </tr>
			  ';
}
	
$html .= '
						</tbldy>
					</table>
				</div>
					';
	
//add pager
$html .= '
				<div class="row">
				  <div class="col-md-4 col-sm-12">' . $listing_split->display_count() . '</div>
				  <div class="col-md-8 col-sm-12">' . $listing_split->display_links(). '</div>
				</div>
				';