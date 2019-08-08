
<div id="mind_map_options" data-url="<?php echo url_for('mind_map/single','entities_id=' . $current_entity_id. '&items_id=' .  $current_item_id. '&fields_id=' . $fields_id . '&action=save') ?>" data-is-editable="<?php echo $mind_map->is_editable() ?>"></div>

<script>	
	var mind_map_json = '<?php echo $mind_map->get_json() ?>';
</script>
