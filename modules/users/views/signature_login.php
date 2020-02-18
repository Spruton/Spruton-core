<h3 class="form-title"><?php echo TEXT_DIGITAL_SIGNATURE_LOGIN ?></h3>

<?php $module->select_certificate() ?>

 <div class="form-actions">
	<button type="button" id="back-btn" class="btn btn-default" onClick="location.href='<?php echo url_for('users/login')?>'"><i class="fa fa-arrow-circle-left"></i> <?php echo TEXT_BUTTON_BACK ?></button>	
</div>