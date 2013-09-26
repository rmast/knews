<?php
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
		$knewsOptions['from_mail_knews'] = $Knews_plugin->post_safe('from_mail_knews');
		$knewsOptions['from_name_knews'] = $Knews_plugin->post_safe('from_name_knews');
		$knewsOptions['write_logs'] = $Knews_plugin->post_safe('write_logs_knews', 'no');
		$knewsOptions['def_autom_post'] = $Knews_plugin->post_safe('def_autom_post_knews', '0');
		$knewsOptions['edited_autom_post'] = $Knews_plugin->post_safe('edited_autom_post_knews', '0');
		$knewsOptions['check_bot'] = $Knews_plugin->post_safe('check_bot_knews', '0');
		$knewsOptions['apply_filters_on'] = $Knews_plugin->post_safe('apply_filters_on_knews', '0');
		$knewsOptions['config_knews'] = 'yes';
		
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
		
		$knewsOptions['from_mail_knews'] = $Knews_plugin->post_safe('from_mail_knews');
		$knewsOptions['from_name_knews'] = $Knews_plugin->post_safe('from_name_knews');
		$knewsOptions['knews_cron'] = $Knews_plugin->post_safe('knews_cron');
		$knewsOptions['smtp_knews'] = $Knews_plugin->post_safe('smtp_knews');
		$knewsOptions['smtp_host_knews'] = $Knews_plugin->post_safe('smtp_host_knews');
		$knewsOptions['smtp_port_knews'] = $Knews_plugin->post_safe('smtp_port_knews');
		$knewsOptions['smtp_user_knews'] = $Knews_plugin->post_safe('smtp_user_knews');
		$knewsOptions['smtp_pass_knews'] = $Knews_plugin->post_safe('smtp_pass_knews');
		$knewsOptions['smtp_secure_knews'] = $Knews_plugin->post_safe('smtp_secure_knews');
		$knewsOptions['is_sendmail'] = $Knews_plugin->post_safe('is_sendmail_knews');

	} elseif (isset($_POST['update_KnewsAdminRegister'])) {

		$knewsOptions['registration_email'] = $Knews_plugin->post_safe('registration_email_knews');
		$knewsOptions['registration_serial'] = $Knews_plugin->post_safe('registration_serial_knews');

	}

	if (isset($_POST['update_KnewsAdminSettings']) || isset($_POST['update_KnewsAdminSettingsAdv']) || isset($_POST['update_KnewsAdminSettingsPro']) || isset($_POST['update_KnewsAdminRegister']) || $Knews_plugin->post_safe('knews_cron_callme_h')!=0) {

		update_option($Knews_plugin->adminOptionsName, $knewsOptions);
	
		if (isset($_POST['update_KnewsAdminRegister'])) {
			echo '<div class="updated"><p>' . sprintf(__("<strong>Knews is now registered</strong>. Go to %s plugins administration %s and click on Knews 'Check for updates' link for upgrade. Thank you very much.",'knews'), '<a href="plugins.php">', '</a>') . '</strong></p></div>';		
		} else {
			echo '<div class="updated"><p><strong>' . __('Saved.','knews') . '</strong></p></div>';
		}

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

if ($Knews_plugin->get_safe('tab')=='pro' && $Knews_plugin->im_pro()) {
	require_once(KNEWS_DIR . '/includes/knews_roles.php');
	if (isset($_POST['update_KnewsAdminRoles'])) knews_admin_save_caps();
}

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
	<link href="<?php echo KNEWS_URL; ?>/admin/styles.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	function view_lang(n_custom, n_lang) {
		jQuery('div.pestanyes_'+n_custom+' a').removeClass('on');
		jQuery('a.link_'+n_custom+'_'+n_lang).addClass('on');
	
		target='div.pregunta_'+n_custom+' textarea.on';
		save_height=jQuery(target).innerHeight() + parseInt(jQuery(target).css('marginTop'), 10) + parseInt(jQuery(target).css('marginBottom'), 10);
		
		save_width=jQuery(target).innerWidth() + parseInt(jQuery(target).css('marginLeft'), 10) + parseInt(jQuery(target).css('marginRight'), 10);
			
		jQuery('div.pregunta_'+n_custom+' textarea').css('display','none').removeClass('on');
		jQuery('textarea.custom_lang_'+n_custom+'_'+n_lang).css({display:'block', height:save_height, width:save_width}).addClass('on');
	}	
	</script>
	
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
				<p><input type="checkbox" name="reset_all_messages" id="reset_all_messages" value="1" /> <?php _e('Reset all messages to default values (all languages at once)','knews'); ?></p>
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
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#knews_cron_callme').click(function() {
			jQuery('#knews_cron_callme_status').html('<p><?php echo $Knews_plugin->escape_js(sprintf(__('Sorry, this is a premium feature. Please, %s click here and see all the Knews Pro features.','knews'), '<a href=\"http://www.knewsplugin.com/knews-free-vs-knews-pro\" target=\"_blank\">')) . '</a>'; ?></p>');
		});
	});
	
	</script>
	<div class=wrap>
		<form method="post" action="admin.php?page=knews_config&tab=advanced" id="form_advanced">
			<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="admin.php?page=knews_config"><?php _e('Main options','knews'); ?></a>
				<a class="nav-tab nav-tab-active" href="admin.php?page=knews_config&tab=advanced"><?php _e('Advanced options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=pro"><?php _e('Knews Pro options','knews'); ?></a>
				<a class="nav-tab" href="admin.php?page=knews_config&tab=custom"><?php _e('Customised messages','knews'); ?></a>
			</h2>
			<h3><?php _e('CRON','knews'); ?></h3>
			<h3><input type="radio" name="knews_cron" value="cronjob"<?php if ($knewsOptions['knews_cron']=='cronjob') echo ' checked="checked"'; ?> /> <?php _e('Use the CRON server (recommended)','knews'); ?> <a href="<?php _e('http://www.knewsplugin.com/configure-webserver-cron/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px; font-weight:normal;" target="_blank"><?php _e('Configure CRON tutorial','knews'); ?></a></h3>
			<?php
				$last_cron_time=$Knews_plugin->get_last_cron_time();
				$now_time = time();
				if ($now_time - $last_cron_time < 800) {
			?>
				<div class="updated">
					<p><strong><?php _e('CRON is properly configured','knews'); ?></strong></p>
					<p><?php _e('The last execution was done','knews'); ?> <?php echo round(($now_time - $last_cron_time) / 60); ?> <?php _e('minutes ago','knews');?></p>
				</div>
			<?php
				} else {
			?>
				<div class="<?php if ($knewsOptions['knews_cron']=='cronjob') { echo ' error';} else {echo ' updated';} ?>">
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
					if( is_multisite() ) {
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
			
			/*<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="knews_cron_callme" id="knews_cron_callme" value="1"<?php if ($knewsOptions['cron_callme']=='1') echo ' checked="checked"'; ?> /> <?php _e('Use Knews CRON without configuration (We will trigger your script remotely)','knews'); ?></p><input type="hidden" name="knews_cron_callme_h" id="knews_cron_callme_h" value="0" />
			<div id="knews_cron_callme_status"></div>*/
			?>
			<h3 style="margin-bottom:0"><input type="radio" name="knews_cron" value="cronwp"<?php if ($knewsOptions['knews_cron']=='cronwp') echo ' checked="checked"'; ?> /> <?php echo __("Use WordPress's built-in CRON framework.",'knews') . '</h3><p style="margin-top:0;">' .  __('This option no requires configuration, but in less traffic sites can be slow to submit','knews'); ?></h3>
			<h3 style="margin-bottom:0"><input type="radio" name="knews_cron" value="cronjs"<?php if ($knewsOptions['knews_cron']=='cronjs') echo ' checked="checked"'; ?> /> <?php echo __('Use the JavaScript CRON emulation.','knews') . '</h3><p style="margin-top:0;">' .   __("This option requires you to keep a window open during submission and does not allow deferred submits",'knews'); ?></p>
			<hr />
			<script type="text/javascript">
			jQuery(document).ready( function () {
				jQuery('input#test_smtp').click(function() {
					jQuery('div.resultats_test').html('<p><blink><?php _e('Sending','knews');?>...</blink></p>');
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
			<h3><?php _e('Submit method','knews');?><a id="submitmethod">&nbsp;</a></h3>
			<p><input type="radio" name="smtp_knews" value="0"<?php if ($knewsOptions['smtp_knews']!='1') echo ' checked="checked"'; ?> /> <?php _e('E-mails sent internally using WordPress (wp_mail() function)','knews');?></p>
			<p><input type="radio" name="smtp_knews" value="1"<?php if ($knewsOptions['smtp_knews']=='1') echo ' checked="checked"'; ?> /> <?php _e('Send e-mails using SMTP (recommended)','knews');?> <a href="<?php _e('http://www.knewsplugin.com/configure-smtp-submits/','knews');?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Configure SMTP tutorial','knews');?></a></p>
			<?php 
			$knews_smtp_editing = $Knews_plugin->get_safe('editsmtp', 1, 'int');
			if ($Knews_plugin->post_safe('knews_smtp_editing') != 'x') $knews_smtp_editing = $Knews_plugin->post_safe('knews_smtp_editing', $knews_smtp_editing, 'int');
			$knews_smtp_default = $knewsOptions['smtp_default'];
			if (!$knews_smtp_multiple = get_option('knews_smtp_multiple')) {
				$knews_smtp_multiple = $Knews_plugin->get_smtp_multiple();
				$knews_smtp_default=0;
			}
			?>
			<div style="width:420px; float:left; padding-left:30px;">
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
					<option value="1"<?php if ($knews_smtp_multiple[$knews_smtp_editing]['is_sendmail']=='1') echo ' selected="selected"'; ?>>IsSendmail()</option></select></td></tr></table>
				<p>* <?php _e('Pay attention: If your SMTP server is anonymous leave SMTP User and SMTP Password fields blank','knews');?></p>
			</div>
			<div style="width:300px; float:left; padding-left:20px;">
				<p><?php _e('Before enabling the sending SMTP, enter the values and your e-mail and then click on TEST button','knews');?>:</p>
				<p><?php _e('Recipient','knews'); ?>: <input type="text" name="email_test" id="email_test" class="regular-text" /></p>
				<div class="submit">
					<div class="resultats_test"></div>
					<input type="button" name="test_smtp" id="test_smtp" class="button" value="<?php _e('Test SMTP config','knews');?>" />
				</div>
			</div>
			<div style="clear:both"></div>
			<p><?php _e('Load SMTP defaults:','knews'); ?> <a href="#" onclick="knews_conf('gmail')"><?php _e('Gmail SMTP','knews'); ?></a> | <a href="#" onclick="knews_conf('1and1')"><?php _e('My hosting is 1&1','knews'); ?></a> | <a href="#" onclick="knews_conf('godaddy')"><?php _e('My Hosting is GoDaddy','knews'); ?></a> |  <a href="#" onclick="knews_conf('yahoo')"><?php _e('Yahoo SMTP','knews'); ?></a></p>
			<div class="updated"><p><?php printf(__('The e-mails submited to any e-mail terminated with @knewstest.com (like testing001@knewstest.com or xxx@knewstest.com) will be submited to: %s for your testing purposes','knews'), get_option('admin_email')); ?></p></div>
			<hr />
			<div class="submit">
				<input type="submit" name="update_KnewsAdminSettingsAdv" id="update_KnewsAdminSettingsAdv" value="<?php _e('Save','knews');?>" class="button-primary" />
			</div>
			<?php 
			//Security for CSRF attacks
			wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
			?>
		</form>
	</div>
	<script type="text/javascript">
		function knews_conf(w) {
			if (w=='gmail') {
				user='youremail@gmail.com';
				host='smtp.gmail.com';
				port='587';
				secure='ssl';
				comnn='0';
			} else if (w=='1and1') {
				user='';
				host='';
				port='';
				secure='';
				comnn='1';
			} else if (w=='godaddy') {
				user='your@email.com';
				host='relay-hosting.secureserver.net';
				port='25';
				secure='';
				comnn='0';
			} else if (w=='yahoo') {
				user='youryahooname';
				host='smtp.mail.yahoo.com';
				port='465';
				secure='ssl';
				comnn='0';
			}
			jQuery('#smtp_host_knews').val(host);
			jQuery('#smtp_port_knews').val(port);
			jQuery('#smtp_user_knews').val(user);
			jQuery('#smtp_secure_knews').val(secure);
			jQuery('#is_sendmail_knews').val(comnn);			
		}
	</script>
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
		<h3><?php _e('Registration','knews');?></h3>
		<?php 
		if (!$Knews_plugin->im_pro()) {
			$look = wp_remote_get( 'http://www.knewsplugin.com/shop/be_pro.php' );
			if (!is_wp_error($look)) {
				if (isset($look['body'])) echo $look['body'];
			} else {
			?>
			<script type="text/javascript">
				if ('https:' == document.location.protocol) {
					document.write('<p>Please, go to <a href="http://www.knewsplugin.com/shop" target="_blank">our shop</a> and see our latest premium templates</p>');
				} else {
					var knewsscript = document.createElement('script'); knewsscript.type = 'text/javascript'; knewsscript.async = true;
					knewsscript.src = 'http://www' + '.knewsplugin.com/shop/look.js?w=<?php echo urlencode(get_bloginfo('version'));?>&v=<?php echo urlencode(KNEWS_VERSION); ?>&l=<?php echo WPLANG; ?>';
					var knewsscript_s = document.getElementsByTagName('script')[0]; knewsscript_s.parentNode.insertBefore(knewsscript, knewsscript_s);
				}
			</script>
			<?php
			}
		} else {
			echo '<h3>' . __('Knews Pro is currently installed.','knews') . '</h3>';	
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
			<h3><?php _e('Alerts and logs','knews'); ?></h3>
			<p><input type="checkbox" name="reset_alerts_knews" value="1" id="reset_alerts_knews" /> <?php _e('Reset all alerts','knews'); ?></p>
			<p><input type="checkbox" name="write_logs_knews" value="yes" id="write_logs_knews"<?php if ($knewsOptions['write_logs']=='yes') echo ' checked="checked"'; ?> /> <?php _e('Write logs (in /wp-content/plugins/knews/tmp directory) in submits','knews'); ?></p>
			<hr />

			<h3><?php _e('Automated options','knews'); ?></h3>
			<p><input type="checkbox" name="def_autom_post_knews" value="1" id="def_autom_post_knews"<?php if ($knewsOptions['def_autom_post']=='1') echo ' checked="checked"'; ?> /> <?php _e('Include the posts in the automated newsletters (default value for the new created posts)','knews'); ?></p>

			<p><input type="checkbox" name="edited_autom_post_knews" value="1" id="edited_autom_post_knews"<?php if ($knewsOptions['edited_autom_post']=='1') echo ' checked="checked"'; ?> /> <?php _e('Use post edition date instead post creation date for the automated newsletters (older posts never included in automation, will be included if you edit it and activate this option)','knews'); ?></p>

			<hr />

			<h3><?php _e('Compatibility options','knews'); ?></h3>
			<p><input type="checkbox" name="apply_filters_on_knews" value="1" id="apply_filters_on_knews"<?php if ($knewsOptions['apply_filters_on']=='1') echo ' checked="checked"'; ?> /> <?php _e('Apply filter the_content in the newsletter post insertion (Deactivate for compatibility issues with some plugins like NextGen Gallery)','knews'); ?><br /><?php _e('<strong>Note</strong>: if you are using <strong>qTranslate</strong> you cant deactivate this option, because it uses this filter to divide the post contents into different languages.','knews'); ?></p>

			<p><input type="checkbox" name="check_bot_knews" value="1" id="check_bot_knews"<?php if ($knewsOptions['check_bot']=='1') echo ' checked="checked"'; ?> /> <?php _e('Prevent bot registrations. Some Cache Plugins can need deactivate this option (Subscribe always fails "wrong e-mail adress" message).','knews'); ?></p>

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