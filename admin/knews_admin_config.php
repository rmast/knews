<?php
add_thickbox();

global $Knews_plugin;

//Security for CSRF attacks
$knews_nonce_action='kn-config-page';
$knews_nonce_name='_config';
if (!empty($_POST)) $w=check_admin_referer($knews_nonce_action, $knews_nonce_name);
//End Security for CSRF attacks

global $Knews_plugin, $knewsOptions;

function knews_save_prefs() {
	global $knewsOptions, $Knews_plugin;
	if (isset($_POST['update_KnewsAdminSettings'])) {
		
		$knewsOptions['multilanguage_knews'] = $Knews_plugin->post_safe('multilanguage_knews');
		$knewsOptions['from_mail_knews'] = trim($Knews_plugin->post_safe('from_mail_knews'));
		$knewsOptions['from_name_knews'] = trim($Knews_plugin->post_safe('from_name_knews'));
		$knewsOptions['double_optin'] = $Knews_plugin->post_safe('double_optin_knews', '0');
		$knewsOptions['notify_signups_email'] = trim($Knews_plugin->post_safe('notify_signups_knews', ''));
		$knewsOptions['write_logs'] = $Knews_plugin->post_safe('write_logs_knews', 'no');
		$knewsOptions['def_autom_post'] = $Knews_plugin->post_safe('def_autom_post_knews', '0');
		$knewsOptions['edited_autom_post'] = $Knews_plugin->post_safe('edited_autom_post_knews', '0');
		$knewsOptions['check_bot'] = $Knews_plugin->post_safe('check_bot_knews', '0');
		$knewsOptions['email_blacklist'] = $Knews_plugin->post_safe('email_blacklist_knews', '0');
		$knewsOptions['apply_filters_on'] = $Knews_plugin->post_safe('apply_filters_on_knews', '0');
		$knewsOptions['config_knews'] = 'yes';
		$knewsOptions['allowed_content_tags'] = $Knews_plugin->post_safe('allowed_content_tags_knews', '');
		$knewsOptions['excerpt_length'] = $Knews_plugin->post_safe('excerpt_length_knews', '');
		$knewsOptions['crop_knews'] = $Knews_plugin->post_safe('crop_knews', '');
		
		if ($Knews_plugin->post_safe('reset_alerts_knews')=='1') {
			$knewsOptions['no_warn_cron_knews'] = 'no';
			$knewsOptions['no_warn_ml_knews'] = 'no';
			$knewsOptions['config_knews'] = 'no';
			$knewsOptions['update_knews'] = 'no';
			$knewsOptions['update_pro'] = 'no';
			$knewsOptions['videotutorial'] = 'no';
			$knewsOptions['newsletter'] = 'no';
		}

	} elseif (isset($_POST['update_KnewsAdminSettingsAdv'])) {
		
		$knewsOptions['from_mail_knews'] = trim($Knews_plugin->post_safe('from_mail_knews'));
		$knewsOptions['from_name_knews'] = trim($Knews_plugin->post_safe('from_name_knews'));
		$knewsOptions['knews_cron'] = $Knews_plugin->post_safe('knews_cron');
		$knewsOptions['smtp_knews'] = $Knews_plugin->post_safe('smtp_knews');
		$knewsOptions['smtp_host_knews'] = trim($Knews_plugin->post_safe('smtp_host_knews'));
		$knewsOptions['smtp_port_knews'] = trim($Knews_plugin->post_safe('smtp_port_knews'));
		$knewsOptions['smtp_user_knews'] = trim($Knews_plugin->post_safe('smtp_user_knews'));
		$knewsOptions['smtp_pass_knews'] = trim($Knews_plugin->post_safe('smtp_pass_knews'));
		$knewsOptions['smtp_secure_knews'] = $Knews_plugin->post_safe('smtp_secure_knews');
		$knewsOptions['is_sendmail'] = $Knews_plugin->post_safe('is_sendmail_knews');
		$knewsOptions['pixel_tracking'] = $Knews_plugin->post_safe('knews_pixel_tracking', 0, 'int');

	} elseif (isset($_POST['update_KnewsAdminRegister'])) {

		$knewsOptions['registration_email'] = trim($Knews_plugin->post_safe('registration_email_knews'));
		$knewsOptions['registration_serial'] = trim($Knews_plugin->post_safe('registration_serial_knews'));

	}

	if (isset($_POST['update_KnewsAdminSettings']) || isset($_POST['update_KnewsAdminSettingsAdv']) || isset($_POST['update_KnewsAdminSettingsPro']) || isset($_POST['update_KnewsAdminRegister'])) {

		update_option($Knews_plugin->adminOptionsName, $knewsOptions);
	
		if (isset($_POST['update_KnewsAdminRegister'])) {
			echo '<div class="updated"><p>' . sprintf(__("<strong>Knews is now registered</strong>. Go to %s plugins administration %s and click on Knews 'Check for updates' link for upgrade. Thank you very much.",'knews'), '<a href="plugins.php">', '</a>') . '</strong></p></div>';		
		} else {
			echo '<div class="updated"><p><strong>' . __('Saved.','knews') . '</strong></p></div>';
		}
	}
	if (isset($_POST['update_KnewsAdminSettingsAdv'])) {

		if (!wp_next_scheduled('knews_wpcron_function_hook')) {
			if ($knewsOptions['knews_cron']=='cronwp') {
				wp_schedule_event( time(), 'knewstime', 'knews_wpcron_function_hook' );
			}
		} else {
			if ($knewsOptions['knews_cron']!='cronwp') {
				wp_clear_scheduled_hook('knews_wpcron_function_hook');
			}
		}

	}
}

$subtab = $Knews_plugin->post_safe('subtab',0,'int');
if ($subtab==0) $subtab = $Knews_plugin->get_safe('subtab',1,'int');

if ($Knews_plugin->get_safe('tab')=='pro' && $Knews_plugin->im_pro()) {
	require_once(KNEWS_DIR . '/includes/knews_roles.php');
	if (isset($_POST['update_KnewsAdminRoles'])) knews_admin_save_caps();
}
?>
<link href="<?php echo KNEWS_URL; ?>/admin/styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo KNEWS_URL; ?>/admin/scripts.js"></script>

<?php
if ($Knews_plugin->get_safe('tab')=='custom') {
	
	$languages = $Knews_plugin->getLangs(true);	
	$KnewsDefaultMessages=$Knews_plugin->get_default_messages();
	
	if (isset($_POST['update_KnewsAdminCustom'])) {
		
		foreach($KnewsDefaultMessages as $CustomMessage) {
			foreach($languages as $l) {

				if ($Knews_plugin->post_safe('reset_all_messages') == '1') {
	
					$reset = $Knews_plugin->get_custom_text($CustomMessage['name'],$l['localized_code'], true);
				
				} else {

					if (isset($_POST['custom_lang_' . $CustomMessage['name'] . '_' . $l['localized_code']])) {
						
						$message = $_POST['custom_lang_' . $CustomMessage['name'] . '_' . $l['localized_code']];
						$message = str_replace('\"','"',$message);
						$message = str_replace("\'","'",$message);
						
						$compat_lang = str_replace('-','_',$l['localized_code']);
						update_option('knews_custom_' . $CustomMessage['name'] . '_' . $compat_lang, $message);
					}
				}
			}
		}

		if ($Knews_plugin->post_safe('reset_all_messages') == '1') {
			echo '<div class="updated"><p><strong>' . __('Messages reset to defaults.','knews') . '</strong></p></div>';
		} else {
			echo '<div class="updated"><p><strong>' . __('Saved.','knews') . '</strong></p></div>';
		}
	}
	
	?>
	
<div class=wrap>
			<form method="post" action="admin.php?page=knews_config&tab=custom">
				<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2 class="nav-tab-wrapper">
					<a class="nav-tab" href="admin.php?page=knews_config"><?php _e('Main options','knews'); ?></a>
					<a class="nav-tab" href="admin.php?page=knews_config&tab=advanced"><?php _e('Advanced options','knews'); ?></a>
					<a class="nav-tab" href="admin.php?page=knews_config&tab=pro"><?php _e('Knews Pro options','knews'); ?></a>
					<a class="nav-tab nav-tab-active" href="admin.php?page=knews_config&tab=custom"><?php _e('Customised messages','knews'); ?></a>
				</h2>
				<?php
				$n_custom=0;
				foreach($KnewsDefaultMessages as $CustomMessage) {
					$n_custom++;
					echo '<h3>' . $CustomMessage['label'] .'</h3>';
					
					echo '<div class="pestanyes pestanyes_' . $n_custom . '">';
					$n_lang=0;
					foreach($languages as $l){
						$n_lang++;
						echo '<a href="#" class="link_' . $n_custom . '_' . $n_lang . ' ' . (($n_lang==1)? ' on' : '') . '" onclick="view_lang(' . $n_custom . ',' . $n_lang . '); return false;">' . $l['native_name'] . '</a>';
					}
					echo '</div>';
					
					echo '<div class="pregunta pregunta_' . $n_custom . '">';
					$n_lang=0;
					foreach($languages as $l){
						$n_lang++;
						echo '<textarea class="custom_lang_' . $n_custom . '_' . $n_lang . (($n_lang==1)? ' on' : '') . '" name="custom_lang_' . $CustomMessage['name'] . '_' . $l['localized_code'] . '" style="width:100%;';
						if ($Knews_plugin->get_custom_text('text_direction',$l['localized_code'])=='rtl' &&
							($CustomMessage['name'] != 'text_direction' && $CustomMessage['name'] != 'default_alignment' && $CustomMessage['name'] != 'inverse_alignment')) echo ' unicode-bidi:bidi-override; direction:rtl;';
						echo '">';
						echo $Knews_plugin->get_custom_text($CustomMessage['name'],$l['localized_code']);
						echo '</textarea>';
					}
	
					echo '</div><hr />';
				}
				?>
				<p><input type="checkbox" name="reset_all_messages" id="reset_all_messages" value="1" class="knews_on_off align_left" /><label><?php _e('Reset all messages to default values (all languages at once)','knews'); ?></label></p>
				<div class="submit">
					<input type="submit" name="update_KnewsAdminCustom" id="update_KnewsAdminCustom" value="<?php _e('Save','knews'); ?>" class="button-primary" />
				</div>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
			</form>
		</div>
<?php
} elseif ($Knews_plugin->get_safe('tab')=='advanced') {
	$cron_main_url = $Knews_plugin->get_main_admin_url() . 'admin-ajax.php?action=knewsCron';
	knews_save_prefs();
?>

	<div class=wrap>
		<form method="post" action="admin.php?page=knews_config&tab=advanced" id="form_advanced">
			<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="admin.php?page=knews_config"><?php _e('Main options','knews'); ?></a>
				<a class="nav-tab nav-tab-active" href="admin.php?page=knews_config&tab=advanced"><?php _e('Advanced options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=pro"><?php _e('Knews Pro options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=custom"><?php _e('Customised messages','knews'); ?></a>
			</h2>
			<div class="knews_cooltabs_wrapper">
				<div class="knews_cooltabs">
					<a href="admin.php?page=knews_config&tab=advanced"<?php if ($subtab==1) echo ' class="active"'; ?>><?php _e('CRON','knews'); ?></a>
					<a href="admin.php?page=knews_config&tab=advanced&subtab=2"<?php if ($subtab==2) echo ' class="active"'; ?>><?php _e('Submit method','knews');?></a>
					<a href="admin.php?page=knews_config&tab=advanced&subtab=3"<?php if ($subtab==3) echo ' class="active"'; ?>><?php _e('Tracking pixel','knews');?></a>
				</div>
			</div>
			<div class="tabbed_content tab_1"<?php if ($subtab!=1) echo ' style="display:none"'; ?>>
			<h3><input type="radio" name="knews_cron" value="cronjob"<?php if ($knewsOptions['knews_cron']=='cronjob') echo ' checked="checked"'; ?> /> <?php _e('Use the CRON server (recommended)','knews'); ?> <a href="<?php _e('http://www.knewsplugin.com/configure-webserver-cron/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px; font-weight:normal;" target="_blank"><?php _e('Configure CRON tutorial','knews'); ?></a></h3>
				<div style="background:#eee url(<?php echo KNEWS_URL; ?>/images/remote.jpg) no-repeat 10px 10px; border:#CCC 1px solid; border-radius:2px; padding:0 15px 0 65px; min-height:65px; margin-left:30px;">
<?php if (!$Knews_plugin->im_pro()) { ?>
				<p style="font-size:13px; line-height:20px;"><a href="admin.php?page=knews_config&tab=pro" class="knews_on_off knews_on_off_left" style="background-position: -50px 0px;">&nbsp;</a><label class="knews_processed knews_on_off_left"><?php _e('Use Knews CRON <strong>without configuration</strong> (We will trigger your script remotely)','knews'); ?><br /><strong>This is a Knews Pro feature</strong></label></p>
<?php } else { ?>
				<p style="font-size:13px; line-height:20px;"><a href="#" onclick="knews_cron_callme(); return false;"><img src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://www.'); ?>knewsplugin.com/look_cron.php?email=<?php echo $knewsOptions['registration_email']; ?>&serial=<?php echo $knewsOptions['registration_serial']; ?>&url=<?php echo urlencode($cron_main_url); ?>" style="vertical-align:middle; margin-right:10px;" /></a><label><?php _e('Use Knews CRON <strong>without configuration</strong> (We will trigger your script remotely)','knews'); ?></label></p>
<?php } ?>
				<div id="knews_cron_callme_status"></div>
			</div>
			<?php
				$last_cron_time=$Knews_plugin->get_last_cron_time();
				$now_time = time();
				if ($now_time - $last_cron_time < 800) {
			?>
					<div class="knews_updated" style="margin-left:30px;">
					<p><strong><?php _e('CRON is properly configured','knews'); ?></strong></p>
					<p><?php _e('The last execution was done','knews'); ?> <?php echo round(($now_time - $last_cron_time) / 60); ?> <?php _e('minutes ago','knews');?></p>
				</div>
			<?php
				} else {
			?>
					<div class="<?php if ($knewsOptions['knews_cron']=='cronjob') { echo ' knews_error';} else {echo ' knews_updated';} ?>" style="margin-left:30px;">
					<p><strong>
					<?php if ($last_cron_time == 0) {
						echo __('CRON has not yet been configured','knews') . '</strong></p>';
					} else {
						echo __('CRON has stopped working.','knews') . '</strong></p>';
						echo '<p>' . __('The last execution was done','knews') . ' ' . round(($now_time - $last_cron_time) / 60) . ' ' . __('minutes ago','knews') . '</p>';
					}
					//if (ini_get('safe_mode')) echo '<p><strong>' . __('Please note: PHP Safe Mode enabled. Without cron, this directive will fail the bulk of mails.','knews') . '</strong></p>';
					?>
					<p><?php _e('Instructions for setting up CRON','knews');?>:</p>
					<?php
					if( $Knews_plugin->im_networked() ) {
						echo '<p><strong>' . sprintf(__('Multisite detected. Only one instance of %s must be called for all websites.','knews'), $cron_main_url) . '</strong></p>';
						//switch_to_blog($Knews_plugin->KNEWS_MAIN_BLOG_ID);
						//restore_current_blog();
					}
					?>
					<p><?php _e('You must add this line in your webserver CRONTAB:','knews'); ?></p>
					<p><strong>*/10 * * * * wget -q -O/dev/null <?php echo $cron_main_url; ?></strong></p>
					<?php /*<p><?php printf( __('The file location is: %s','knews'),  KNEWS_DIR . '/direct/knews_cron.php'); ?></p>*/ ?>
				</div>
			<?php
				}
			?>
			<h3 style="margin-bottom:0"><input type="radio" name="knews_cron" value="cronwp"<?php if ($knewsOptions['knews_cron']=='cronwp') echo ' checked="checked"'; ?> /> <?php echo __("Use WordPress's built-in CRON framework.",'knews') . '</h3><p style="margin-top:0;">' .  __('This option no requires configuration, but in less traffic sites can be slow to submit','knews'); ?></h3>
			<h3 style="margin-bottom:0"><input type="radio" name="knews_cron" value="cronjs"<?php if ($knewsOptions['knews_cron']=='cronjs') echo ' checked="checked"'; ?> /> <?php echo __('Use the JavaScript CRON emulation.','knews') . '</h3><p style="margin-top:0;">' .   __("This option requires you to keep a window open during submission and does not allow deferred submits",'knews'); ?></p>
			</div>
			<script type="text/javascript">
			jQuery(document).ready( function () {
				jQuery('a#test_smtp_pro').click(function() {
					jQuery('div.resultats_test_pro').html('<p><blink><?php echo $Knews_plugin->escape_js(__('Sending','knews'), "'");?>...</blink></p>');
					jQuery.ajax({
						data: {
							//email_test: jQuery('input#email_test').val(),
							from_mail_knews: jQuery('input#from_mail_knews').val(),
							from_name_knews: jQuery('input#from_name_knews').val(),
							smtp_host_knews: jQuery('input#smtp_host_knews').val(),
							smtp_port_knews: jQuery('input#smtp_port_knews').val(),
							smtp_user_knews: jQuery('input#smtp_user_knews').val(),
							smtp_pass_knews: jQuery('input#smtp_pass_knews').val(),
							smtp_secure_knews: jQuery('select#smtp_secure_knews').val(),
							is_sendmail_knews: jQuery('select#is_sendmail_knews').val(),
							bounce_header_knews: jQuery('select#bounce_header_knews').val(),
							action: 'knewsSpamcheck'
						},
						type: "POST",
						cache: false,
						url: "<?php echo get_admin_url(); ?>admin-ajax.php",
						success: function(data) {
							if (data==0) {
								jQuery('div.resultats_test_pro').html('<p><?php echo $Knews_plugin->escape_js(__('Submit error.','knews'), "'"); ?></p>');
							} else {
								jQuery('div.resultats_test_pro').html('');
								tb_show('', '<?php echo get_admin_url(); ?>admin-ajax.php?action=knewsSpamcheck2&unique=' + data + '&amp;TB_iframe=true&amp;width=640&amp;height=' + (parseInt(jQuery(parent.window).height(), 10)-100));
							}
						}
					});
					return false;
				});

				jQuery('input#test_smtp').click(function() {
					jQuery('div.resultats_test').html('<p><blink><?php echo $Knews_plugin->escape_js(__('Sending','knews'), "'");?>...</blink></p>');
					jQuery.ajax({
						data: {
							email_test: jQuery('input#email_test').val(),
							from_mail_knews: jQuery('input#from_mail_knews').val(),
							from_name_knews: jQuery('input#from_name_knews').val(),
							smtp_host_knews: jQuery('input#smtp_host_knews').val(),
							smtp_port_knews: jQuery('input#smtp_port_knews').val(),
							smtp_user_knews: jQuery('input#smtp_user_knews').val(),
							smtp_pass_knews: jQuery('input#smtp_pass_knews').val(),
							smtp_secure_knews: jQuery('select#smtp_secure_knews').val(),
							is_sendmail_knews: jQuery('select#is_sendmail_knews').val(),
							action: 'knewsTestSMTP'
						},
						type: "POST",
						cache: false,
						url: "<?php echo get_admin_url(); ?>admin-ajax.php",
						success: function(data) {
							jQuery('div.resultats_test').html(data);
						}
					});
					return false;
				});
			});
			</script>
			<div class="tabbed_content tab_2"<?php if ($subtab!=2) echo ' style="display:none"'; ?>>
			<p><input type="radio" name="smtp_knews" value="0"<?php if ($knewsOptions['smtp_knews']!='1') echo ' checked="checked"'; ?> /> <?php _e('E-mails sent internally using WordPress (wp_mail() function)','knews');?></p>
			<p><input type="radio" name="smtp_knews" value="1"<?php if ($knewsOptions['smtp_knews']=='1') echo ' checked="checked"'; ?> /> <?php _e('Send e-mails using SMTP (recommended)','knews');?> <a href="<?php _e('http://www.knewsplugin.com/configure-smtp-submits/','knews');?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Configure SMTP tutorial','knews');?></a></p>
			<?php 
			$knews_smtp_editing = $Knews_plugin->get_safe('editsmtp', 1, 'int');
			if ($Knews_plugin->post_safe('knews_smtp_editing') != 'x') $knews_smtp_editing = $Knews_plugin->post_safe('knews_smtp_editing', $knews_smtp_editing, 'int');
			$knews_smtp_default = $knewsOptions['smtp_default'];
			if (!$Knews_plugin->im_pro() || !$knews_smtp_multiple = get_option('knews_smtp_multiple')) {
				$knews_smtp_multiple = $Knews_plugin->get_smtp_multiple();
				$knews_smtp_default=1;
			}
?>
			<table class="widefat" style="max-width:700px; margin-left:30px;margin-bottom:15px">
				<thead><tr><th>&nbsp;</th><th>SMTP Sender</th><th>Host</th><th>Default</th></tr></thead>
				<tbody>
				<?php 
				$n=0;
				while ($knews_smtp_once = current($knews_smtp_multiple)) {
					$n++;
					if (!isset($knews_smtp_once['deleted'])) {
						echo '<tr' . (($n%2==0)? ' class="alt"' : '') . '>';
						if (key($knews_smtp_multiple)==$knews_smtp_editing || key($knews_smtp_multiple)=='x') {
							echo '<td><img src="' . KNEWS_URL . '/images/pencil.png" width="16" height="16"></td>';
							echo '<td><strong>' . $knews_smtp_once['from_name_knews'] . '</strong><div class="row-actions"><span>&nbsp;';
						} else {
							echo '<td>&nbsp;</td><td>' . $knews_smtp_once['from_name_knews'] . '<div class="row-actions"><span><a href="admin.php?page=knews_config&tab=advanced&subtab=2&editsmtp=' . key($knews_smtp_multiple) . '#submitmethod">Edit</a>';
						}
						echo ((key($knews_smtp_multiple)==1) ? '' : ' | <a href="admin.php?page=knews_config&tab=advanced&subtab=2&delsmtp=' . key($knews_smtp_multiple) . '#submitmethod">Delete</a>') . '</span></div></td>';
						echo '<td>' . $knews_smtp_once['smtp_host_knews'] . '</td>';
						echo '<td><input type="radio" name="knews_smtp_default"' . ((key($knews_smtp_multiple)==$knews_smtp_default) ? ' checked="checked"' : '') . ' autocomplete="off" value="' . key($knews_smtp_multiple) . '" /></td></tr>';
					}
					next($knews_smtp_multiple);

				}
				//if ($knews_smtp_editing == -1) $knews_smtp_editing = count($knews_smtp_multiple)-1;
				?>
				<tr><td>&nbsp;</td><td colspan="3"><a href="<?php echo (($Knews_plugin->im_pro()) ? 'admin.php?page=knews_config&tab=advanced&subtab=2&action=addsmtp' : 'admin.php?page=knews_config&tab=pro');?>">+ Add SMTP</a></td></tr>
				</tbody>
			</table>

			<div style="width:440px; float:left; padding-left:30px;">
				<table cellpadding="0" cellspacing="0" border="0" style="font-size:12px">
				<tr><td><?php _e('Sender name','knews');?>:</td><td><input type="text" name="from_name_knews" id="from_name_knews" class="regular-text" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['from_name_knews']; ?>" /></td></tr>
				<tr><td><?php _e('Sender e-mail','knews');?>:</td><td><input type="text" name="from_mail_knews" id="from_mail_knews" class="regular-text" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['from_mail_knews']; ?>" /></td></tr>
				<tr><td><?php _e('Host SMTP','knews');?>:</td><td><input type="text" name="smtp_host_knews" id="smtp_host_knews" class="regular-text" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['smtp_host_knews']; ?>" /></td></tr>
				<tr><td><?php _e('Port SMTP','knews');?>:</td><td><input type="text" name="smtp_port_knews" id="smtp_port_knews" style="width:100px" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['smtp_port_knews']; ?>" /></td></tr>
				<tr><td><?php _e('SMTP User','knews');?>: *</td><td><input type="text" name="smtp_user_knews" id="smtp_user_knews" class="regular-text" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['smtp_user_knews']; ?>" /></td></tr>
				<tr><td><?php _e('SMTP Password','knews');?>: *</td><td><input type="password" name="smtp_pass_knews" id="smtp_pass_knews" class="regular-text" autocomplete="off" value="<?php echo $knews_smtp_multiple[$knews_smtp_editing]['smtp_pass_knews']; ?>" /></td></tr>
				<tr><td><?php _e('SMTP Secure','knews');?>: </td><td><select name="smtp_secure_knews" id="smtp_secure_knews" autocomplete="off" >
					<option value=""<?php if ($knews_smtp_multiple[$knews_smtp_editing]['smtp_secure_knews']=='') echo ' selected="selected"'; ?>>none</option>
					<option value="tls"<?php if ($knews_smtp_multiple[$knews_smtp_editing]['smtp_secure_knews']=='tls') echo ' selected="selected"'; ?>>tls</option>
					<option value="ssl"<?php if ($knews_smtp_multiple[$knews_smtp_editing]['smtp_secure_knews']=='ssl') echo ' selected="selected"'; ?>>ssl</option></select></td></tr>
				<tr><td><?php _e('Conn mode:','knews');?> </td><td><select name="is_sendmail_knews" id="is_sendmail_knews" autocomplete="off" >
					<option value="0"<?php if ($knews_smtp_multiple[$knews_smtp_editing]['is_sendmail']=='0') echo ' selected="selected"'; ?>>IsSMTP()</option>
					<option value="1"<?php if ($knews_smtp_multiple[$knews_smtp_editing]['is_sendmail']=='1') echo ' selected="selected"'; ?>>IsSendmail()</option></select></td></tr>
				</table>
				<p>* <?php _e('Pay attention: If your SMTP server is anonymous leave SMTP User and SMTP Password fields blank','knews');?></p>
			</div>
			<div style="width:300px; float:left; padding-left:20px;">
				<h3 style="margin-top:0">Real Spam Test <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://'); ?>knewsplugin.com/real-spam-test-for-smtp-configuration-and-newsletters/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank" rel="noreferrer" title="Learn Real Spam Test"></a></h3>
				<p>Knews will use this values to send a test email and give you a full live report: <strong>Spam Assassin</strong> real results + <strong>Knews values</strong> analysis.</p>
				<div class="resultats_test_pro"></div>
				<p><a href="#" class="button" id="test_smtp_pro">Real Spam Test</a></p>
				
				<hr>
				
				<h3>Knews basic test</h3>
				<p><?php _e('This test is obsolete since the above is better. However, you can use it: enter your email a test emai will be sent to you:','knews');?></p>
				<p><?php _e('Recipient','knews'); ?>: <input type="text" name="email_test" id="email_test" class="regular-text" /></p>
				<div class="resultats_test"></div>
				<input type="button" name="test_smtp" id="test_smtp" class="button" value="<?php _e('Test SMTP config','knews');?>" />
					
			</div>
			<div style="clear:both"></div>
			<p><?php _e('Load SMTP defaults:','knews'); ?> <a href="#" onclick="knews_conf('gmail')"><?php _e('Gmail SMTP','knews'); ?></a> | <a href="#" onclick="knews_conf('1and1')"><?php _e('My hosting is 1&1','knews'); ?></a> | <a href="#" onclick="knews_conf('godaddy')"><?php _e('My Hosting is GoDaddy','knews'); ?></a> |  <a href="#" onclick="knews_conf('yahoo')"><?php _e('Yahoo SMTP','knews'); ?></a></p>
				<div class="knews_updated"><p><?php printf(__('The e-mails submited to any e-mail terminated with @knewstest.com (like testing001@knewstest.com or xxx@knewstest.com) will be submited to: %s for your testing purposes','knews'), get_option('admin_email')); ?></p></div>
			</div>
			<div class="tabbed_content tab_3"<?php if ($subtab!=3) echo ' style="display:none"'; ?>>
				<?php 
				$rewrite_base = is_multisite() ? network_site_url() : get_bloginfo('url');
				if (substr($rewrite_base, -1) != '/') $rewrite_base .= '/';
				$rewrite_base = substr($rewrite_base, strpos($rewrite_base, '/', 8) );
				?>
				<h3>This feature needs modification of your <strong>.htaccess</strong> file.</h3>
				<p>Please, take care: any modification in the WordPress permalinks options can overwrite this file.</p>
				<p>A mistake in this file can leave your WordPress down, <strong>please, make a backup of .htaccess file before do this modification</strong></p>
				<p>You should insert the red lines <strong>before</strong> # BEGIN Wordpress in your .htaccess file, should be in your WordPress root folder:</p>
				<div style="border:#ddd 1px solid; background:#eee; border-radius:5px; padding:10px; overflow:auto; font-family:'Open Sans', sans-serif; font-size:13px; margin-bottom:20px;"><span style="color:#e00;"># Begin Knews<br />&lt;IfModule mod_rewrite.c&gt;<br />RewriteEngine On<br />RewriteBase <?php echo $rewrite_base; ?><br />RewriteRule ^<?php echo KNEWS_WP_CONTENT; ?>/uploads/knewsimages/([^/]+).(jpg|jpeg|gif|png)$ <?php echo $rewrite_base . KNEWS_WP_CONTENT; ?>/plugins/knews/direct/track.php?img=$1 [L,NC]<br /><?php
				
				//if ($Knews_plugin->im_networked()) {
				if (is_multisite()) {
				?><br />#Multisite support rule<br />RewriteRule ^([^/]+)/<?php echo KNEWS_WP_CONTENT; ?>/uploads/sites/([^/]+)/knewsimages/([^/]+).(jpg|jpeg|gif|png)$ <?php echo $rewrite_base . KNEWS_WP_CONTENT; ?>/plugins/knews/direct/track.php?site=$1&site_id=$2&img=$3 [L,NC]<br /><?php
				}
				
				?>&lt;/IfModule&gt;<br /># End Knews</span><br /><br /># BEGIN WordPress<br />.<br />.<br />.</div>
				<?php
				$wp_dirs = wp_upload_dir();
				$img_track = $wp_dirs['baseurl'] . '/knewsimages/testcat.jpg';
				?>
				<img src="<?php echo $img_track; ?>" width="155" height="100" alt=".htaccess Test, you should see a cat here" style="float:left; margin-right:10px; border:#000 1px solid;" />
				<p>If you can see the cat into the left sqare, your .htaccess is well configured, and you can activate the tracking pixel feature.</p>
				<p><input type="checkbox" name="knews_pixel_tracking" class="knews_on_off align_left" value="1" autocomplete="off"<?php if ($knewsOptions['pixel_tracking']==1) echo ' checked="checked"'; ?> /><label>Allow tracking pixel (yes, I'm seeing the cat)</label></p>
				<p><strong>Attention:</strong> if you can't see the cat, please, don't activate tracking pixel, otherwise the first image URL in every sent newsletter will be broken.</p>
				<div style="clear:both; font-size:1px; line-height:1px;"></div>
			</div>
			<div class="submit">
				<input type="submit" name="update_KnewsAdminSettingsAdv" id="update_KnewsAdminSettingsAdv" value="<?php _e('Save','knews');?>" class="button-primary" />
			</div>
			<input type="hidden" name="subtab" id="subtab" value="<?php echo $subtab; ?>" />
			<?php 
			//Security for CSRF attacks
			wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
			?>
		</form>
	</div>
<?php


} elseif ($Knews_plugin->get_safe('tab')=='pro') {
	knews_save_prefs();
?>
	<div class=wrap>
		<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2 class="nav-tab-wrapper">
			<a class="nav-tab" href="admin.php?page=knews_config"><?php _e('Main options','knews'); ?></a>
			<a class="nav-tab" href="admin.php?page=knews_config&tab=advanced"><?php _e('Advanced options','knews'); ?></a>
			<a class="nav-tab nav-tab-active" href="admin.php?page=knews_config&tab=pro"><?php _e('Knews Pro options','knews'); ?></a>
			<a class="nav-tab" href="admin.php?page=knews_config&tab=custom"><?php _e('Customised messages','knews'); ?></a>
		</h2>

		<?php 
		if ($Knews_plugin->im_pro()) {
		?>
		<div class="knews_cooltabs_wrapper">
			<div class="knews_cooltabs">
				<a href="admin.php?page=knews_config&tab=pro"<?php if ($subtab==1) echo ' class="active"'; ?>><?php _e('Registration','knews');?></a>
				<a href="admin.php?page=knews_config&tab=pro&subtab=2"<?php if ($subtab==2) echo ' class="active"'; ?>><?php _e('Bounce configuration','knews');?></a>
				<a href="admin.php?page=knews_config&tab=pro&subtab=3"<?php if ($subtab==3) echo ' class="active"'; ?>><?php _e('Roles and capabilities','knews');?></a>
				<a href="admin.php?page=knews_config&tab=pro&subtab=4"<?php if ($subtab==4) echo ' class="active"'; ?>><?php _e('Custom Post Types configuration','knews');?></a>
			</div>
		</div>
		<div class="tabbed_content tab_1"<?php if ($subtab!=1) echo ' style="display:none"'; ?>>
		<?php
			echo '<h3>' . __('Knews Pro is currently installed.','knews') . '</h3>';	
		} else {
			echo '<h3>' . __('Registration','knews') . '</h3>';
			$look = wp_remote_get( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://www.') . 'knewsplugin.com/shop/be_pro.php' );
			if (!is_wp_error($look)) {
				if (isset($look['body'])) echo $look['body'];
			} else {
			?>
			<script type="text/javascript">
				/*if ('https:' == document.location.protocol) {
					starturl = 'https://';
				} else {
					starturl = 'http://www' + '.';
				}*/
				var knewsscript = document.createElement('script'); knewsscript.type = 'text/javascript'; knewsscript.async = true;
				knewsscript.src = '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://www.'); ?>knewsplugin.com/shop/look.js?w=<?php echo urlencode(get_bloginfo('version'));?>&v=<?php echo urlencode(KNEWS_VERSION); ?>&l=<?php echo WPLANG; ?>';
				var knewsscript_s = document.getElementsByTagName('script')[0]; knewsscript_s.parentNode.insertBefore(knewsscript, knewsscript_s);
				
			</script>
			<?php
			}
		}
} else {
	knews_save_prefs();
?>
	<div class=wrap>
		<form method="post" action="admin.php?page=knews_config">
			<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="admin.php?page=knews_config"><?php _e('Main options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=advanced"><?php _e('Advanced options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=pro"><?php _e('Knews Pro options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=custom"><?php _e('Customised messages','knews'); ?></a>
			</h2>
			<h3><?php _e('Multilingual','knews'); ?></h3>
			<?php 
				if (!$Knews_plugin->check_multilanguage_plugin() && $knewsOptions['multilanguage_knews'] != 'off') {
			 ?>
				<div class="error">
					<p><?php _e('The multilanguage plugin has stopped working.','knews'); ?></p>
				</div>
			<?php
				} else {
					if (!$Knews_plugin->have_wpml() && !$Knews_plugin->have_qtranslate() && !$Knews_plugin->have_polylang()) {
			?>				
				<div class="updated">
					<p><?php _e('No mulilanguage plugins detected. Knews works with qTranslate and WPML plugins','knews'); ?></p>
					<p><a href="http://www.qianqin.de/qtranslate/" target="_blank"><?php _e('qTranslate: free plugin','knews'); ?></a></p>
					<p><a href="http://wpml.org/" target="_blank"><?php _e('WPML: commercial plugin','knews'); ?></a></p>
					<p><a href="http://polylang.wordpress.com/" target="_blank"><?php _e('Polylang: free plugin','knews'); ?></a></p>
					<p><?php _e('Note: Knews authors have no relationship with qTranslate or WMPL authors.','knews'); ?></p>
				</div>
			<?php 
					}
				}
				if ($Knews_plugin->have_wpml()) {
			?>
					<p><input type="radio" name="multilanguage_knews" value="wpml" id="multilanguage_knews_wpml"<?php if ($knewsOptions['multilanguage_knews']=='wpml') echo ' checked="checked"'; ?> /> <?php _e('Use the WPML defined languages to operate Knews in multilanguage mode','knews'); ?></p>
			<?php
				}
				if ($Knews_plugin->have_qtranslate()) {
			?>
					<p><input type="radio" name="multilanguage_knews" value="qt" id="multilanguage_knews_qt"<?php if ($knewsOptions['multilanguage_knews']=='qt') echo ' checked="checked"'; ?> /> <?php _e('Use the qTranslate defined languages to operate Knews in multilanguage mode','knews'); ?></p>
			<?php
				}
                if ($Knews_plugin->have_polylang()) {
			?>
					<p><input type="radio" name="multilanguage_knews" value="pll" id="multilanguage_knews_pll"<?php if ($knewsOptions['multilanguage_knews']=='pll') echo ' checked="checked"'; ?> /> <?php _e('Use the Polylang defined languages to operate Knews in multilanguage mode','knews'); ?></p>
            <?php
                }
			?>
				<p><input type="radio" name="multilanguage_knews" value="off" id="multilanguage_knews_off"<?php if ($knewsOptions['multilanguage_knews']=='off') echo ' checked="checked"'; ?> /> <?php _e('Operate Knews as monolingual','knews'); ?></p>

			<hr />
			<h3><?php _e('Sender','knews');?></h3>
			<table cellpadding="0" cellspacing="0" border="0">
			<tr><td><?php _e('Sender name','knews');?>:</td><td><input type="text" name="from_name_knews" id="from_name_knews" class="regular-text" value="<?php echo $knewsOptions['from_name_knews']; ?>" /></td></tr>
			<tr><td><?php _e('Sender e-mail','knews');?>:</td><td><input type="text" name="from_mail_knews" id="from_mail_knews" class="regular-text" value="<?php echo $knewsOptions['from_mail_knews']; ?>" /></td></tr>
			</table>
			<hr />
			<h3><?php _e('At subscription time','knews'); ?></h3>
			<p><input type="checkbox" name="double_optin_knews" value="1" id="double_optin_knews"<?php if ($knewsOptions['double_optin']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Double optin. (Send email confirmation after subscription). Leave checked if you want to obey the european laws.','knews'); ?></label></p>
			<p><label><?php _e('Advice me:','knews'); ?></label><input type="text" name="notify_signups_knews" value="<?php echo $knewsOptions['notify_signups_email']; ?>" id="notify_signups_knews" class="regular-text" /> <?php _e('Insert your email if you want to be emailed when someone subscribe','knews'); ?></p>
			<hr />
			<h3><?php _e('Alerts and logs','knews'); ?></h3>
			<p><input type="checkbox" name="reset_alerts_knews" value="1" id="reset_alerts_knews" class="knews_on_off align_left" /><label><?php _e('Reset all alerts','knews'); ?></label></p>
			<p><input type="checkbox" name="write_logs_knews" value="yes" id="write_logs_knews"<?php if ($knewsOptions['write_logs']=='yes') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Write logs (in /wp-content/plugins/knews/tmp directory) in submits','knews'); ?></label></p>
			<hr />

			<h3><?php _e('Image Insertion crop','knews'); ?></h3>
			<p><input type="checkbox" name="crop_knews" value="yes" id="crop_knews"<?php if ($knewsOptions['crop_knews']=='yes') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Image crop? (Some templates can override this preference)','knews'); ?></label></p>

			<h3><?php _e('Post insertion options','knews'); ?></h3>
			<p><label><strong><?php _e('Allow this tags in %the_content% post insertion (manual & automated):','knews'); echo '</strong><br />'; _e('Warning: some tags can break the newsletter design, add it at your own risk!','knews'); ?></label>
			<textarea name="allowed_content_tags_knews" id="allowed_content_tags_knews" style="width:100%"><?php echo $knewsOptions['allowed_content_tags']; ?></textarea></p>
			<p><label><strong><?php _e('Length of the excerpt:','knews'); ?>
			<input type="text" name="excerpt_length_knews" id="excerpt_length_knews" style="width:40px" value="<?php echo $knewsOptions['excerpt_length']; ?>" /> <?php _e('words','knews'); ?></p>

			<hr />
			
			<h3><?php _e('Automated options','knews'); ?></h3>
			<p><input type="checkbox" name="def_autom_post_knews" value="1" id="def_autom_post_knews"<?php if ($knewsOptions['def_autom_post']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Include the posts in the automated newsletters (default value for the new created posts)','knews'); ?></label></p>

			<p><input type="checkbox" name="edited_autom_post_knews" value="1" id="edited_autom_post_knews"<?php if ($knewsOptions['edited_autom_post']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Use post edition date instead post creation date for the automated newsletters (older posts never included in automation, will be included if you edit it and activate this option)','knews'); ?></label></p>

			<hr />

			<h3><?php _e('Compatibility options','knews'); ?></h3>
			<p><input type="checkbox" name="apply_filters_on_knews" value="1" id="apply_filters_on_knews"<?php if ($knewsOptions['apply_filters_on']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Apply filter the_content in the newsletter post insertion (Deactivate for compatibility issues with some plugins like NextGen Gallery)','knews'); ?><br /><?php _e('<strong>Note</strong>: if you are using <strong>qTranslate</strong> you cant deactivate this option, because it uses this filter to divide the post contents into different languages.','knews'); ?></label></p>

			<hr />

			<h3><a name="blacklists" id="blacklists"></a><?php _e('Clean lists','knews'); ?></h3>
			<p style="padding-bottom:15px;"><input type="checkbox" name="check_bot_knews" value="1" id="check_bot_knews"<?php if ($knewsOptions['check_bot']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php _e('Prevent bot registrations. Some Cache Plugins can need deactivate this option (Subscribe always fails "wrong e-mail adress" message).','knews'); ?></label></p>
			<?php
			global $email_blacklist; $this->load_blacklist(true);
			?>
			<div class="updated below-h2">
				<p><?php echo sprintf(__('Knews includes now a %s domains blacklist. It includes temporary email domains, used widely by spammers.','knews'), '<strong>' . count($email_blacklist) . '</strong>' ); ?> <a href="#" class="knews_blacklist"><?php _e('Look list','knews'); ?></a></p><p><a href="admin.php?page=knews_users&da=cleanBlackList" class="button" target="_blank"><?php _e('Run Mailing lists clean now','knews'); ?></a></p>
			</div>
			<p><input type="checkbox" name="email_blacklist_knews" value="1" id="email_blacklist_knews"<?php if ($knewsOptions['email_blacklist']=='1') echo ' checked="checked"'; ?> class="knews_on_off align_left" /><label><?php echo __('Block registrations from blacklist domains.','knews'); ?></label></p>
			<div class="knews_blacklist" style="display:none;"><textarea style="width:100%; height:200px"><?php echo implode(', ', $email_blacklist); ?></textarea></div>
			<div class="submit">
				<input type="submit" name="update_KnewsAdminSettings" id="update_KnewsAdminSettings" value="<?php _e('Save','knews');?>" class="button-primary" />
			</div>
			<?php 
			//Security for CSRF attacks
			wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
			?>
		</form>
	</div>
<?php
}
?>