<?php
		
		global $Knews_plugin, $knewsOptions;
		if (! $Knews_plugin->initialized) $Knews_plugin->init();

		if ($Knews_plugin->knews_admin_messages != '') {
			echo '<div class="updated"><p>' . $Knews_plugin->knews_admin_messages . '</p></div>';
		} else {


				if (version_compare( KNEWS_VERSION, get_option('knews_version' )) < 0 || get_option('knews_pro') == 'yes') {
					if ($knewsOptions['update_knews'] == 'no' && version_compare( KNEWS_VERSION, get_option('knews_version' )) < 0) {
						echo '<div class="error"><p>' . __('You are downgraded the version of Knews, you can lose data, please update quickly','knews');
						echo ' <a href="' . get_admin_url() . 'admin-ajax.php?action=knewsOffWarn&w=update_knews&b=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" style="float:right">' . __("Don't show this message again [x]",'knews') . '</a></p></div>';
					} else {
						if (get_option('knews_pro') == 'yes') {
							if ($knewsOptions['update_pro'] == 'no') {
								echo '<div class="updated"><p>';
								printf( __('You are downgraded to the free version of Knews, you can lose data, please update quickly! You can get the professional version %s here','knews'), '<a href="http://www.knewsplugin.com" target="_blank">');
								echo '</a>';
								echo ' <a href="' . get_admin_url() . 'admin-ajax.php?action=knewsOffWarn&w=update_pro&b=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" style="float:right">' . __("Don't show this message again [x]",'knews') . '</a></p></div>';
							}
						}
					}
				}
			
			if (strpos($_SERVER['REQUEST_URI'],'knews_config') === false) {
					
			
				if (!$Knews_plugin->check_multilanguage_plugin() && $knewsOptions['multilanguage_knews'] != 'off' && $knewsOptions['no_warn_ml_knews'] == 'no') {
	
					printf('<div class="error"><p>' . __('The multilanguage plugin has stopped working.','knews') . ' ' . __('Please, go to %s configuration page','knews') . "</a>", 
						'<a href="' . get_admin_url() . 'admin.php?page=knews_config">');
					echo ' <a href="' . get_admin_url() . 'admin-ajax.php?action=knewsOffWarn&w=no_warn_ml_knews&b=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" style="float:right">' . __("Don't show this message again [x]",'knews') . '</a></p></div>';
	
				} elseif ($knewsOptions['knews_cron']=='cronjob') {
					$last_cron_time = $Knews_plugin->get_last_cron_time();
					$now_time = time();
					if ($now_time - $last_cron_time > 1000 && $last_cron_time != 0 && $knewsOptions['no_warn_cron_knews'] == 'no') {
	
						printf('<div class="error"><p>' . __('CRON has stopped working.','knews') . ' ' . __('Please, go to %s configuration page','knews') . "</a>", 
							'<a href="' . get_admin_url() . 'admin.php?page=knews_config">');
						echo ' <a href="' . get_admin_url() . 'admin-ajax.php?action=knewsOffWarn&w=no_warn_cron_knews&b=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" style="float:right">' . __("Don't show this message again [x]",'knews') . '</a></p></div>';
					}
				}
			}
		}
