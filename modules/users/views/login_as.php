<?php echo ajax_modal_template_header(TEXT_BUTTON_LOGIN) ?>

<?php echo form_tag('login_as', url_for('users/login_as','action=login&users_id=' . _get::int('users_id'))) ?>
<div class="modal-body">    
<?php 
 echo sprintf(TEXT_LOGIN_AS,$app_users_cache[$user_info['id']]['name']);  
?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_LOGIN) ?>

</form>   
    