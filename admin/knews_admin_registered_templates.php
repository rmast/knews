<?php
//Security for CSRF attacks
$knews_nonce_action='kn-register-templates';
$knews_nonce_name='_registemp';
if (!empty($_POST)) $w=check_admin_referer($knews_nonce_action, $knews_nonce_name);
//End Security for CSRF attacks

global $Knews_plugin, $knewsOptions;
require_once( KNEWS_DIR . '/includes/knews_util.php');

$registered_templates = apply_filters('knews_registered_templates','');

if ($Knews_plugin->post_safe('action')=='save_registration_templates') {

	foreach ($registered_templates as $template) {

		if ($Knews_plugin->post_safe('registered_email_' . $template['id']) != '' || $Knews_plugin->post_safe('registered_serial_' . $template['id']) != '') {

			$register=array('email'=> trim($Knews_plugin->post_safe('registered_email_' . $template['id'])), 'serial'=> trim($Knews_plugin->post_safe('registered_serial_' . $template['id'])) );
			update_option('knews_template_' . $template['id'], $register);
		}
	}
	?>
	<div class="updated"><p><?php _e('Registration info saved.','knews'); ?></p></div>
	<?php
}
?>	
<div class=wrap>
			<form method="post" action="admin.php?page=knews_registered_templates">
				<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2>Registered Templates</h2>
				<?php
				foreach ($registered_templates as $template) {
					knews_examine_template($template['id'], $template['folder'], $template['url'], false, 'registration');
				}
				?>
				<div style="clear:both;"></div>
				<div class="submit">
					<input type="submit" name="update_KnewsAdminSettings" id="update_KnewsAdminSettings" value="<?php _e('Save','knews');?>" class="button-primary" />
				</div>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
				<input type="hidden" name="action" value="save_registration_templates" />
			</form>
</div>
