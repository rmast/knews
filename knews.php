<?php
/*
Plugin Name: K-news
Plugin URI: http://www.knewsplugin.com
Description: Finally, newsletters are multilingual, quick and professional.
Version: 1.8.2
Author: Carles Reverter
Author URI: http://www.carlesrever.com
License: GPLv2 or later
Text Domain: knews
Domain Path: /languages
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

if (!class_exists("KnewsPlugin")) {
	class KnewsPlugin {
	
		var $adminOptionsName = "KnewsAdminOptions";
		var $knewsOptions = array();
		var $knewsLangs = array();
		var $initialized = false;
		var $basic_initialized = false;
		var $initialized_textdomain = false;
		var $advice='';
		var $KNEWS_MAIN_BLOG_ID = 1;
		var $knews_admin_messages = '';
		var $knews_form_n = 1;
		var $polylang_options = '';
		var $email_blacklist = '';
		var $template_id = 'unknown';
						
		/******************************************************************************************
		/*                                   INICIALITZAR
		******************************************************************************************/
		
		// Carregar opcions de la BBDD / inicialitzar
		function getAdminOptions() {

			$KnewsAdminOptions = array (
				'smtp_knews' => '0',
				'from_mail_knews' => get_bloginfo('admin_email'),
				'from_name_knews' => 'Knews robot',
				'smtp_host_knews' => 'smtp.yourdomain.com',
				'smtp_port_knews' => '25',
				'smtp_user_knews' => '',
				'smtp_pass_knews' => '',
				'smtp_secure_knews' => '',
				'smtp_default' => 1,
				'multilanguage_knews' => 'off',
				'no_warn_ml_knews' => 'no',
				'no_warn_cron_knews' => 'no',
				'cron_callme' => '0',
				'config_knews' => 'no',
				'update_knews' => 'no',
				'write_logs' => 'no',
				'knews_cron' => 'cronwp',
				'update_pro' => 'no',
				'videotutorial' => 'no',
				'def_autom_post' => '0',
				'apply_filters_on' => '1',
				'edited_autom_post' => '0',
				'hide_shop' => '0',
				'hide_templates' => '0',
				'bounce_on' => '0',
				'is_sendmail' => '0',
				'bounce_email' => 'bounce@yourdomain.com',
				'bounce_host' => 'mail.yourdomain.com',
				'bounce_port' => '110',
				'bounce_user' => 'bounce@yourdomain.com',
				'bounce_pass' => '',
				'bounce_ssl' => 'no',
				'bounce_mode' => '0',
				'new_users_list' => '0',
				'registration_email' => '',
				'registration_serial' => '',
				'check_bot' => '1',
				'newsletter' => 'no',
				'notify_signups_email' => '',
				'double_optin' => 1,
				'double_optin_register' => 0,
				'pixel_tracking' => 0,
				'https_conn' => 0,
				'allowed_content_tags' => '<br><i><em><b><strong><ul><li><ol>',
				'email_blacklist' => 1,
				'blacklist_scan' => 0,
				'excerpt_length' => 20,
				'crop_knews' => 'yes',
				'bounce_mode' => 'returnpath',
				'should_advice_bounce_mode' => 0
				);

			$devOptions = get_option($this->adminOptionsName);
			if (!empty($devOptions)) {
				foreach ($devOptions as $key => $option)
					$KnewsAdminOptions[$key] = $option;
			} else {
				update_option($this->adminOptionsName, $KnewsAdminOptions);
			}
			
			//Support for older bounce configs
			if ($KnewsAdminOptions['bounce_email'] == 'bounce@yourdomain.com') $KnewsAdminOptions['bounce_email'] = $KnewsAdminOptions['bounce_user'];
			
			return $KnewsAdminOptions;
		}
	
		function creaSiNoExisteixDB () {
			if (!$this->tableExists(KNEWS_USERS)) {
				require( KNEWS_DIR . "/includes/knews_installDB.php");
				
			} else {
				if (version_compare(get_option('knews_version','0.0.0'), KNEWS_VERSION, '<')) {
					require( KNEWS_DIR . "/includes/knews_updateDB.php");
				}
			}
		}
		
		function get_default_messages() {

			$KnewsDefaultMessages1 = array (
				array ( 'label'=>__('Text direction, Left To Right or Right To Left: put <span style="color:#e00">ltr</span> or <span style="color:#e00">rtl</span>','knews'), 'name'=>'text_direction'),
				array ( 'label'=>__('Widget subscription form title','knews'), 'name'=>'widget_title')
			);
			if ($this->im_pro()) $KnewsDefaultMessages1[] = array ( 'label'=>__('Widget latest newsletters title','knews'), 'name'=>'widgetln_title');

			$extra_fields = $this->get_extra_fields();
			foreach ($extra_fields as $field) {
				$KnewsDefaultMessages1[]=array ( 'label'=> sprintf(__('Widget "%s" label form','knews'), $field->name), 'name'=>'widget_label_' . $field->name);
			}

			$KnewsDefaultMessages2 = array (
				array ( 'label'=>__('Widget subtitle text','knews'), 'name'=>'widget_subtitle'),
				array ( 'label'=> sprintf(__('Widget "%s" label form','knews'), 'email'), 'name'=>'widget_label_email'),
				array ( 'label'=>__('Widget accept terms','knews'), 'name'=>'widget_label_terms'),
				array ( 'label'=>__('Widget required text fields','knews'), 'name'=>'widget_required'),
				array ( 'label'=>__('Widget submit button','knews'), 'name'=>'widget_button'),
				array ( 'label'=>__('Wrong e-mail address, please check (AJAX message)','knews'), 'name'=>'ajax_wrong_email'),
				array ( 'label'=>__('Empty required field, please check (AJAX message)','knews'), 'name'=>'ajax_wrong_fields'),
				array ( 'label'=>__('We have sent you a confirmation e-mail (AJAX message)','knews'), 'name'=>'ajax_subscription'),
				array ( 'label'=>__('Subscription done, you were already subscribed (AJAX message)','knews'), 'name'=>'ajax_subscription_direct'),
				array ( 'label'=>__('You were already a subscriber (AJAX message)','knews'), 'name'=>'ajax_subscription_oops'),
				array ( 'label'=>__('Subscription error (AJAX message)','knews'), 'name'=>'ajax_subscription_error'),
				array ( 'label'=>__('Confirmation E-mail (subject)','knews'), 'name'=>'email_subscription_subject'),
				array ( 'label'=>__('Confirmation E-mail (body)','knews'), 'name'=>'email_subscription_body'),
				array ( 'label'=>__('E-mail on automatically import (title)','knews'), 'name'=>'email_importation_subject'),
				array ( 'label'=>__('E-mail on automatically import (body)','knews'), 'name'=>'email_importation_body'),
				array ( 'label'=>__('Subscription OK Dialog (Title)','knews'), 'name'=>'subscription_ok_title'),
				array ( 'label'=>__('Subscription OK Dialog (Message)','knews'), 'name'=>'subscription_ok_message'),
				array ( 'label'=>__('Subscription Error Dialog (Title)','knews'), 'name'=>'subscription_error_title'),
				array ( 'label'=>__('Subscription Error Dialog (Message)','knews'), 'name'=>'subscription_error_message'),
				array ( 'label'=>__('UnSubscribe Error Dialog (Title)','knews'), 'name'=>'subscription_stop_error_title'),
				array ( 'label'=>__('UnSubscribe Error Dialog (Message)','knews'), 'name'=>'subscription_stop_error_message'),
				array ( 'label'=>__('UnSubscribe OK Dialog (Title)','knews'), 'name'=>'subscription_stop_ok_title'),
				array ( 'label'=>__('UnSubscribe OK Dialog (Message)','knews'), 'name'=>'subscription_stop_ok_message'),
				array ( 'label'=>__('Close Button Caption','knews'), 'name'=>'dialogs_close_button'),
				array ( 'label'=>__('Default alignment (<span style="color:#e00">left</span> for left to right languages and <span style="color:#e00">right</span> for right to left languages)','knews'), 'name'=>'default_alignment'),
				array ( 'label'=>__('Inverse alignment (<span style="color:#e00">right</span> for left to right languages and <span style="color:#e00">left</span> for right to left languages)','knews'), 'name'=>'inverse_alignment'),
				array ( 'label'=>__('Cant read text 1','knews'), 'name'=>'cant_read_text_1'),
				array ( 'label'=>__('Cant read text link','knews'), 'name'=>'cant_read_text_link'),
				array ( 'label'=>__('Cant read text 2','knews'), 'name'=>'cant_read_text_2'),
				array ( 'label'=>__('Mobile version text link','knews'), 'name'=>'mobile_version_text_link'),
				array ( 'label'=>__('Desktop version text link','knews'), 'name'=>'desktop_version_text_link'),
				array ( 'label'=>__('Unsubscribe text 1','knews'), 'name'=>'unsubscribe_text_1'),
				array ( 'label'=>__('Unsubscribe text link','knews'), 'name'=>'unsubscribe_text_link'),
				array ( 'label'=>__('Unsubscribe text 2','knews'), 'name'=>'unsubscribe_text_2'),
				array ( 'label'=>__('The read more text link','knews'), 'name'=>'read_more_link')
			);
			return array_merge($KnewsDefaultMessages1, $KnewsDefaultMessages2);
		}

		function get_custom_text($name, $lang, $restore=false) {
			$lang = str_replace('-','_',$lang);
			$custom = get_option('knews_custom_' . $name . '_' . $lang,'');

			if ($custom == '' || $restore) {
				
				require_once (KNEWS_DIR . '/includes/mo_reader.php');

				if (!is_file(KNEWS_DIR . '/languages/knews-' . $lang . '.mo')) {
					$custom = mo_reader(KNEWS_DIR . '/languages/knews-en_US.mo', $name);
				} else {
					$custom = mo_reader(KNEWS_DIR . '/languages/knews-' . $lang . '.mo', $name);
				}
				
				$custom = str_replace('\"','"',$custom);
				update_option('knews_custom_' . $name . '_' . $lang, $custom);
			}
			return $custom;
		}
		
		function basic_init($blog_id=0) {
			global $knewsOptions, $wpdb;

			if ($blog_id != 0 && $this->im_networked() ) switch_to_blog($blog_id);

			if (!defined('KNEWS_WP_CONTENT')){
				$content_folder = 'wp-content';
				if (defined('WP_CONTENT_URL')) {
					$home_url = get_option('home');
					$pos = strpos(WP_CONTENT_URL, $home_url);
					if ($pos !== false) {
						$content_folder = substr(WP_CONTENT_URL, $pos + strlen($home_url));
						if (substr($content_folder, 0, 1) == '/') $content_folder = substr($content_folder, 1);
						if (substr($content_folder, -1) == '/') $content_folder = substr($content_folder, 0, strlen($content_folder) -1);
					}
				}
				define ('KNEWS_WP_CONTENT', $content_folder);
			}

			define('KNEWS_USERS', $wpdb->prefix . 'knewsusers');	
			define('KNEWS_USERS_EXTRA', $wpdb->prefix . 'knewsusersextra');	
			define('KNEWS_EXTRA_FIELDS', $wpdb->prefix . 'knewsextrafields');	
			define('KNEWS_LISTS', $wpdb->prefix . 'knewslists');	
			define('KNEWS_USERS_PER_LISTS', $wpdb->prefix . 'knewsuserslists');	
			define('KNEWS_NEWSLETTERS', $wpdb->prefix . 'knewsletters');	
			define('KNEWS_NEWSLETTERS_SUBMITS_DETAILS', $wpdb->prefix . 'knewsubmitsdetails');
			define('KNEWS_STATS', $wpdb->prefix . 'knewstats');
			define('KNEWS_KEYS', $wpdb->prefix . 'knewskeys');
			define('KNEWS_AUTOMATED', $wpdb->prefix . 'knewsautomated');
			define('KNEWS_AUTOMATED_POSTS', $wpdb->prefix . 'knewsautomatedposts');
			define('KNEWS_AUTOMATED_SELECTION', $wpdb->prefix . 'knewsautomatedsel');
			define('KNEWS_USERS_EVENTS', $wpdb->prefix . 'knewsusersevents');

			define('KNEWS_NEWSLETTERS_SUBMITS', $wpdb->base_prefix . 'knewsubmits');
			define('KNEWS_DIR', dirname(__FILE__));
			$url = plugins_url();

			/* fixing protocol when CRON call http and site is https */
			$protocol = 'http://';
			if (substr(home_url(),0,8)=='https://') $protocol = 'https://';
			$url = $protocol . substr($url, strpos($url, '://')+3);

			if ($blog_id != 0 && $this->im_networked()) $url = $this->get_right_blog_path($blog_id) . KNEWS_WP_CONTENT . '/plugins';
			define('KNEWS_URL', $url . '/knews');
			$this->knews_load_plugin_textdomain();
			$knewsOptions = $this->getAdminOptions();
		
			wp_register_script ('knews_subscribe', KNEWS_URL . '/includes/knews_subscribe.js', array('jquery'), KNEWS_VERSION, true);

			$this->basic_initialized=true;
		}
		
		function init($blog_id=0) {
			if ($this->initialized) return;
			$this->initialized = true;
			
			global $knewsOptions, $wpdb;
			if ($blog_id != 0 && $this->im_networked() ) switch_to_blog($blog_id);
			
			if (!$this->basic_initialized) $this->basic_init($blog_id);

			global $KnewsAdminOptions;
			define('KNEWS_MULTILANGUAGE', $this->check_multilanguage_plugin($KnewsAdminOptions['multilanguage_knews']));

			$this->creaSiNoExisteixDB(); 
			
			$this->knewsLangs = $this->getLangs();

			//LOCALIZED URLS (WPML different domains for language option)
			$knews_localized_url = KNEWS_URL;
			$knews_localized_admin = get_admin_url();
			if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml' && function_exists('icl_get_languages')) {
					
				global $sitepress_settings;
				if (isset($sitepress_settings['language_negotiation_type']) && $sitepress_settings['language_negotiation_type']==2) {
				
					//$l = $this->pageLang();
					//$knews_localized_url = $l['url'];
					$knews_localized_url = icl_get_home_url();

					if (substr($knews_localized_url, -1) != '/') $knews_localized_url .= '/';
					$knews_localized_admin = $knews_localized_url . 'wp-admin/';
					$knews_localized_url .= KNEWS_WP_CONTENT . '/plugins/knews';
				}
			}

			if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='pll' && function_exists('pll_home_url')) {
				
				global $polylang;
				if ($polylang->options['force_lang']==3) {
					//$l = $this->pageLang();
					//$knews_localized_url = $l['url'];
					$knews_localized_url = pll_home_url();
					if (substr($knews_localized_url, -1) != '/') $knews_localized_url .= '/';
					$knews_localized_admin = $knews_localized_url . 'wp-admin/';
					$knews_localized_url .= KNEWS_WP_CONTENT . '/plugins/knews';
				}
			}
						
			define('KNEWS_LOCALIZED_URL', $knews_localized_url);
			define('KNEWS_LOCALIZED_ADMIN', $knews_localized_admin);

			if (is_admin()) {
				//Some rude plugins deactivate Knews crons
				if ($knewsOptions['knews_cron'] == 'cronwp' && !wp_next_scheduled('knews_wpcron_function_hook')) wp_schedule_event( time(), 'knewstime', 'knews_wpcron_function_hook');
				if (!wp_next_scheduled('knews_wpcron_automate_hook')) wp_schedule_event( time(), 'hourly', 'knews_wpcron_automate_hook');
			}
		}
		
		function im_networked() {
			if (!is_multisite()) return false;

			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			if (is_plugin_active_for_network('knews/knews.php')) return true;
		}
		
		function get_right_blog_path($blog_id) {
			global $wpdb;

			$blog_found=array();
			
			if( $this->im_networked() ) {
				$query = "SELECT * FROM " . $wpdb->base_prefix . 'blogs' . " WHERE blog_id=" . $blog_id;
				$blog_found = $wpdb->get_results( $query );
			}
			
			if (count($blog_found)==0) return get_bloginfo('wpurl') . '/';
			$protocol = 'http://';
			if (substr(get_bloginfo('wpurl'),0,8)=='https://') $protocol = 'https://';
			return $protocol . $blog_found[0]->domain . $blog_found[0]->path;
		}
		
		function get_main_admin_url() {
			
			$url = get_admin_url();

			if( $this->im_networked() ) {
				$url = $this->get_right_blog_path($this->KNEWS_MAIN_BLOG_ID) . 'wp-admin/';
			}
			
			return $url;
		}
		function get_main_plugin_url() {
			
			$url = plugins_url();

			if( $this->im_networked() ) {
				$url = $this->get_right_blog_path($this->KNEWS_MAIN_BLOG_ID) . KNEWS_WP_CONTENT . '/plugins';
			}
			
			return $url;
		}
		
		function get_localized_home($user_lang, $extra_params='') {
			global $knewsOptions;
			if (! $this->initialized) $this->init();
		
			$url_home = get_bloginfo('url');

			if (KNEWS_MULTILANGUAGE && $user_lang != '') {
				if ($knewsOptions['multilanguage_knews']=='wpml') {
					global $sitepress;
					if (method_exists($sitepress, 'language_url')) {
						//$user_lang = $Knews_plugin->get_user_lang($Knews_plugin->get_safe('e'));
						$url_home = $sitepress->language_url($user_lang);
					}
				}
				
				if ($knewsOptions['multilanguage_knews']=='qt') {

					if (function_exists('qtranxf_convertURL')) {
						$url_home = qtranxf_convertURL(get_bloginfo('url'), $user_lang);
						
					} elseif (function_exists('qtrans_convertURL')) {
						//$user_lang = $Knews_plugin->get_user_lang($Knews_plugin->get_safe('e'));
						$url_home = qtrans_convertURL(get_bloginfo('url'), $user_lang);
					}
				}

				if ($knewsOptions['multilanguage_knews']=='pll') {
					
					global $polylang;
					
					if (isset($polylang->options) && $polylang->options != '') 
						$pll_options = $polylang->options;
					else
						$pll_options = get_option('polylang');

					if ($pll_options['force_lang']==3) {
						
						if (isset($pll_options['domains'][$user_lang])) {
							$url_home = $pll_options['domains'][$user_lang];
							if (substr($url_home, -1) != '/') $url_home .= '/';
						}
					} elseif ($pll_options['hide_default'] == 0 || $user_lang != $pll_options['default_lang']) {
							
						if ($pll_options['force_lang']==2) {
							
							$url_home=str_replace('//', '//' . $user_lang . '.', $url_home);
							
						} elseif ($pll_options['force_lang']==1) {

							if (substr($url_home, -1) != '/') $url_home .= '/';
							$url_home .= ($pll_options['rewrite']==0 ? 'language/' : '') . $user_lang . '/';

						} else {

							$permalinks = get_option('permalink_structure','');
							if (substr($url_home, -1) != '/') $url_home .= '/';
							
							if ( $permalinks != '' )
								$url_home .= $user_lang . '/';
							else
								$url_home .= '?lang='. $user_lang;
							
						}
	
					}
				}

			}
			return $this->add_extra_params($url_home, $extra_params);
		}
		
		function add_extra_params($url, $extra_params) {

			if  ($extra_params=='') return $url;
						
			$hash_url = '';
			if (strpos($url, '#')!==false) {
				$hash_url = substr($url, strpos($url, '#'));
				$url = substr($url, 0 , strpos($url, '#'));
			}
			if (strpos($url, '?')===false) {
				if (substr($url, -1) != '/') $url .= '/';
				$url .= '?' . $extra_params . $hash_url;
			} else {
				$url .= '&' . $extra_params . $hash_url;
			}
			return $url;
		}

		function knews_load_plugin_textdomain() {
			global $initialized_textdomain;

			if ($initialized_textdomain) return;
			load_plugin_textdomain( 'knews', false, 'knews/languages');
			$initialized_textdomain=true;
		}
		
		function check_multilanguage_plugin($plugin='') {
			global $knewsOptions;

			if ($plugin=='') $plugin = $knewsOptions['multilanguage_knews'];

			$multilanguage_plugin = false;
			if ($plugin == 'wpml') $multilanguage_plugin = $this->have_wpml();
			if ($plugin == 'qt') $multilanguage_plugin = $this->have_qtranslate();
			if ($plugin == 'pll') $multilanguage_plugin = $this->have_polylang();

			return $multilanguage_plugin;
		}

		/******************************************************************************************
		/*                                 LOGICA DEL PLUGIN
		******************************************************************************************/
		function KnewsPlugin() {
			//Execucio tant a admin com a web
		}
	
		/******************************************************************************************
		/*                                  COMMON FUNCTIONS 
		******************************************************************************************/
		function get_last_cron_time () {
			
			$last_cron_time=0;
			
			if( $this->im_networked() ) {
				if ( get_current_blog_id() != $this->KNEWS_MAIN_BLOG_ID ) {
					switch_to_blog($this->KNEWS_MAIN_BLOG_ID);
					$last_cron_time = get_option('knews_cron_time',-1);
					restore_current_blog();
				}
			}
			
			if ($last_cron_time == 0) $last_cron_time = get_option('knews_cron_time',0);
			if ($last_cron_time == -1) $last_cron_time = 0;
			
			return $last_cron_time;
		}
		function get_mysql_date($when='now') {
			if ($when=='now') $when=time();
			//if ($when=='now') return current_time('mysql');
			return date("Y-m-d H:i:s", $when);
		}
		
		function sql2time($sqldate) {
			return mktime(substr($sqldate,11,2), substr($sqldate,14,2), substr($sqldate,17,2), substr($sqldate,5,2), substr($sqldate,8,2), substr($sqldate,0,4));
		}

		function real_insert_id() {
			global $wpdb;
			$insert = $wpdb->insert_id; 
			if ($insert == 0) {
				$mysqli = false;
				//from $wbdb class
				if ( function_exists( 'mysqli_connect' ) ) {
					if ( defined( 'WP_USE_EXT_MYSQL' ) ) {
						$mysqli = ! WP_USE_EXT_MYSQL;
					} elseif ( version_compare( phpversion(), '5.5', '>=' ) || ! function_exists( 'mysql_connect' ) ) {
						$mysqli = true;
					} elseif ( false !== strpos( $GLOBALS['wp_version'], '-' ) ) {
						$mysqli = true;
					}
				}
				if (!$mysqli) $insert = mysql_insert_id();
			}
			return $insert;
		}
		
		function humanize_dates ($date, $format) {
			
			if ($date == '0000-00-00 00:00:00') return '-';
			
			if ($format=='mysql') $date = $this->sql2time($date);

			$gmt_offset = intval(get_option('gmt_offset')) * 60 * 60;
			$date = $date + $gmt_offset;
			
			$day = 60*60*24;
			$today_start = mktime (0,0,0,date('n'),date('j'),date('Y')); // + $gmt_offset;

			$diference = $date - $today_start;

			$hour = intval(($date % $day) / (60*60));
			$minute = intval((($date % $day) - $hour * 60 * 60) / 60);
			if ($hour < 10) $hour = '0' . $hour;
			if ($minute < 10) $minute = '0' . $minute;
			$hour = $hour . ':' . $minute;
			
			$date_readable = $this->localize_date($date);

			if ($diference > 0) {
				//Future or today
				if ($diference < $day) return __('Today, at','knews') . ' ' . $hour;
				if ($diference < $day*2) return __('Tomorrow, at','knews') . ' ' . $hour;
				return $date_readable . ' ' . __('at','knews') . ' ' . $hour;
			} else {
				//Past
				$diference=$diference * -1;
				if ($diference < $day) return __('Yesterday, at','knews') . ' ' . $hour;
				return $date_readable . ' ' . __('at','knews') . ' ' . $hour;
			}
		}
		
		function localize_date ($date) {

			$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
			$timezone_format = explode(' ', $timezone_format);
			
			if (!is_array($timezone_format) || !isset($timezone_format[0]) || strlen($timezone_format[0]) <5) {
				$timezone_format = 'Y-m-d';
			} else {
				$timezone_format = $timezone_format[0];
			}
			
			return date($timezone_format,$date);
		}
		
		function get_extra_fields ($extra_sql='') {
			$ef = array( new stdClass, new stdClass );
			$ef[0]->id = 1; $ef[0]->name = 'name'; $ef[0]->show_table =1; $ef[0]->token = '%name%';
			$ef[1]->id = 2; $ef[1]->name = 'surname'; $ef[1]->show_table =1; $ef[1]->token = '%surname%';
			return $ef;
		}
		
		function get_user_field ($user_id, $field_id, $empty='') {
			global $wpdb;
			
			if ($field_id==0) {
				$query = "SELECT email FROM " . KNEWS_USERS . " WHERE id=" . $user_id;
				$field_found = $wpdb->get_col( $query, 0 );

			} else {
				$query = "SELECT * FROM " . KNEWS_USERS_EXTRA . " WHERE user_id=" . $user_id . ' AND field_id=' . $field_id;
				$field_found = $wpdb->get_col( $query, 3 );
			}
			if ($field_found) {
				if ($field_found[0]!='') return $field_found[0];
			}
			return $empty;
		}
		
		function set_user_field ($user_id, $field_id, $cf, $overwrite=true) {
			global $wpdb;

			$query = "SELECT * FROM " . KNEWS_USERS_EXTRA . " WHERE user_id=" . $user_id . ' AND field_id=' . $field_id;
			$field_found = $wpdb->get_results( $query );

			if (count($field_found)==0) {
				
				//Insert field
				if ($cf != '') {
					$query = "INSERT INTO " . KNEWS_USERS_EXTRA . " (value, user_id, field_id) VALUES ('" . $cf . "', " . $user_id . "," . $field_id . ")";
					$result=$wpdb->query( $query );
				} else {
					$result=1;
				}
			} else {
				//Update field
				$result=1;
				if ($overwrite) {
					$query = "UPDATE " . KNEWS_USERS_EXTRA . " SET value='" . $cf . "' WHERE user_id=" . $user_id . ' AND field_id=' . $field_id;
					$result=$wpdb->query( $query );
				}
			}
			return $result;
		}

		function tableExists($table){
			global $wpdb;
			return strcasecmp($wpdb->get_var("show tables like '$table'"), $table) == 0;
		}
		
		function get_safe($field, $un_set='', $mode='paranoid') {
			$value = ((isset($_GET[$field])) ? $_GET[$field] : $un_set);
			if ( get_magic_quotes_gpc()) $value = stripslashes_deep($value);
			if ($mode=='unsafe') return $value;
			if ($mode=='int') return intval($value);
			if ($mode=='paranoid') return esc_sql(htmlspecialchars(strip_tags($value)));
		}

		function post_safe($field, $un_set='', $mode='paranoid') {
			$value = ((isset($_POST[$field])) ? $_POST[$field] : $un_set);
			if (!is_array($un_set)) $value = array($value);
			
			for ($x=0; $x++; $x<count($value)) {
				if ( get_magic_quotes_gpc()) $value[$x] = stripslashes_deep($value[$x]);
				if ($mode=='int') $value[$x] = intval($value[$x]);
				if ($mode=='paranoid') $value[$x] = esc_sql(htmlspecialchars(strip_tags($value[$x])));
			}
			if (!is_array($un_set)) $value = $value[0];
			return $value;
		}

		function escape_js($txt, $comma='"') {
			$txt = str_replace($comma, '\\' . $comma, $txt);
			$txt = str_replace('\\\\' . $comma, '\\' . $comma, $txt);
			return $txt;
		}
	
		function get_user_lang($email_or_id){

			if (! $this->initialized) $this->init();

			global $wpdb;

			$field = 'email';
			
			if (!$this->validEmail($email_or_id)) {
				$email_or_id = intval($email_or_id);
				if ($email_or_id==0) return 'en';
				$field = 'id';
			}
			
			$query = "SELECT * FROM " . KNEWS_USERS . " WHERE " . $field . "='" . $email_or_id . "'";
			$user_found = $wpdb->get_results( $query );
			return $user_found[0]->lang;
		}
				
		function get_unique_id($long=8) {
			return substr(md5(uniqid()), $long * -1);
		}
		
		function add_user_self(){
			header('X-Frame-Options: GOFORIT');

			global $knewsOptions;

			//$name = esc_sql($_POST['name']);
			$lang = $this->post_safe('lang_user');
			$lang_locale = $this->post_safe('lang_locale_user');
			$email = trim($this->post_safe('knewsemail'));
			$id_list_news = $this->post_safe('user_knews_list', 0, 'int');
			
			$custom_fields=array();
			$custom_fields_ok=true;
			$extra_fields = $this->get_extra_fields();
			
			foreach ($extra_fields as $field) {
				if ($this->post_safe($field->name) != '') {
					$custom_fields[$field->id]=$this->post_safe($field->name);
				} else {
					if ($this->post_safe('required_' . $field->name) == '1') $custom_fields_ok=false;
				}
			}
			
			if ($this->post_safe('required_knewsterms') == '1' && $this->post_safe('knewsterms') != '1') $custom_fields_ok=false;

			$stupid_bot = false;
			if (intval($knewsOptions['check_bot'])==1) {
				$key = md5(date('dmY') . wp_create_nonce( 'knews-subscription' ));
				if ($this->post_safe('knewskey') != $key) $stupid_bot = true;
				if (date('G') == 0 && $stupid_bot) {
					$key = md5(date('dmY', strtotime("-1 day")) . wp_create_nonce( 'knews-subscription' ));
					if ($this->post_safe('knewskey') == $key) $stupid_bot = false;
				}
			}
			if ($this->post_safe('knewscomment') != '') $stupid_bot = true;

			//echo '<div class="response"><p>';

			if (!$this->validEmail($email) || $stupid_bot) {
				$code = 5; $response = $this->get_custom_text('ajax_wrong_email', $lang_locale) . ' <a href="#" class="knews_back">' 
						. $this->get_custom_text('dialogs_close_button', $lang_locale) . '</a>';

			} elseif (!$custom_fields_ok) {
				$code = 6; $response = $this->get_custom_text('ajax_wrong_fields', $lang_locale) . ' <a href="#" class="knews_back">' 
						. $this->get_custom_text('dialogs_close_button', $lang_locale) . '</a>';
				
			} else {
				//$code = $this->add_user($email, $id_list_news, $lang, $lang_locale, $custom_fields);
				
				$code = apply_filters('knews_add_user_db', 0, $email, $id_list_news, $lang, $lang_locale, $custom_fields, ($knewsOptions['double_optin']==0) );
				
				if ($code==1 || ($code==3 && $knewsOptions['double_optin']==0)) {
					if ($knewsOptions['notify_signups_email'] != '') {

						$theHtml = '<p>' . sprintf('A new user was subscribed to: %s', get_bloginfo('name')) . '</p>';
						
						foreach ($extra_fields as $field) {
							if ($this->post_safe($field->name) != '') {
								$theHtml.= '<p>' . $field->name . ': ' . $this->post_safe($field->name) . '</p>';
							}
						}

						$theHtml.= '<p>Email: ' . $this->post_safe('knewsemail') . '</p>';

						$this->sendMail($knewsOptions['notify_signups_email'], 'New user subscribed to: ' . get_bloginfo('name'), $theHtml, '', '', false, false, 0, $knewsOptions['smtp_default']);
					}
				}
				if ($code==1) $response = $this->get_custom_text('ajax_subscription', $lang_locale);
				if ($code==2) $response = $this->get_custom_text('ajax_subscription_error', $lang_locale);
				if ($code==3) $response = $this->get_custom_text('ajax_subscription_direct', $lang_locale);
				if ($code==4) $response = $this->get_custom_text('ajax_subscription_error', $lang_locale);
				if ($code==5) $response = $this->get_custom_text('ajax_subscription_oops', $lang_locale);
			}

			do_action('knews_echo_ajax_reply', $code, $response, $lang_locale);
			//echo '</p></div>';
		}
		
		function echo_ajax_reply ($code, $response, $lang_locale) {
			echo '<div class="response"><p>' . $response . '</p></div>';
		}
		
		function echo_dialog ($popup_code, $popup_scripts, $popup_styles, $popup_text, $lang_locale) {
			echo $popup_scripts;
			echo $popup_styles;
			echo $popup_text;
		}
		
		//Deprecated, support for older customised implementations, do better the apply_filters()
		function add_user($email, $id_list_news, $lang='en', $lang_locale='en_US', $custom_fields=array(), $bypass_confirmation=false){
			apply_filters('knews_add_user_db', 0, $email, $id_list_news, $lang, $lang_locale, $custom_fields, $bypass_confirmation);
		}

		function add_user_db($reply, $email, $id_list_news, $lang, $lang_locale, $custom_fields, $bypass_confirmation){
			
			$email=trim($email);
			
			if (! $this->initialized) $this->init();
			
			global $wpdb;
			$date = $this->get_mysql_date();
			$confkey = $this->get_unique_id();

			$query = "SELECT * FROM " . KNEWS_USERS . " WHERE email='" . $email . "'";
			$user_found = $wpdb->get_results( $query );

			$submit_mail=true;

			if (count($user_found)==0) {
				$ip = $_SERVER['REMOTE_ADDR'];
				$query = "INSERT INTO " . KNEWS_USERS . " (email, lang, state, joined, confkey, ip) VALUES ('" . $email . "','" . $lang . "', " . ($bypass_confirmation ? '2' : '1') . ", '" . $date . "','" . $confkey . "','" . $ip . "');";
				$results = $wpdb->query( $query );
				$user_id = $this->real_insert_id();

			} else if ($user_found[0]->state=='2') {
				$user_id = $user_found[0]->id;
				$submit_mail=false;
				$results=true;
				
			} else {
				$user_id = $user_found[0]->id;
				$query = "UPDATE " . KNEWS_USERS . " SET state='1', confkey='" . $confkey . "', lang='" . $lang . "' WHERE id=" . $user_id;
				$results = $wpdb->query( $query );
			}
			
			while ($cf = current($custom_fields)) {
				$this->set_user_field ($user_id, key($custom_fields), esc_sql($cf), false);
				next($custom_fields);
			}
			
			if ($results) {
				
				if (!is_array($id_list_news)) $id_list_news = array($id_list_news);
				
				foreach ($id_list_news as $id_list) {
				
					if (count($user_found)==0) {
	
						$query = "INSERT INTO " . KNEWS_USERS_PER_LISTS . " (id_user, id_list) VALUES (" . $user_id . ", " . $id_list . ");";
	
					} else {
	
						$query = "SELECT * FROM " . KNEWS_USERS_PER_LISTS . " WHERE id_user=" . $user_id . " AND id_list=" . $id_list;
						$subscription_found = $wpdb->get_results( $query );
						
						if (count($subscription_found)==0) {
							$query = "INSERT INTO " . KNEWS_USERS_PER_LISTS . " (id_user, id_list) VALUES (" . $user_id . ", " . $id_list . ");";
						}
					}
					$results = $wpdb->query( $query );
				}
								
				if ($submit_mail) {
					
					if ($bypass_confirmation) return 3;
					
					if (apply_filters('knews_submit_confirmation', $email, $confkey, $lang_locale, $lang, $user_id)) {
					//if ($this->submit_confirmation ($email, $confkey, $lang_locale, $lang)) {
						return 1; //Confirmation sent
					} else {
						return 2; //Submit confirmation error
					}
					
				} else {
					if (count($subscription_found)==0) {
						return 3; //Subscription OK to second mailing list 
					} else {
						return 5; //Yet subscribted to this mailing list
					}
				}
				
			} else {
				return 4; //Error, cant subscribe				
			}
			
		}
				
		function submit_confirmation ($email, $confkey, $lang_locale='en_US', $lang='en', $user_id=0) {

			global $knewsOptions;

			$mailHtml = $this->get_custom_text('email_subscription_body', $lang_locale);
			
			//$url_confirm = KNEWS_LOCALIZED_ADMIN . 'admin-ajax.php?action=knewsConfirmUser&k=' . $confkey . '&e=' . urlencode($email);
			$url_confirm = $this->get_localized_home($lang, 'knews=confirmUser&k=' . $confkey . '&e=' . urlencode($user_id != 0 ? $user_id : $email) );

			$mailHtml = str_replace('#url_confirm#', $url_confirm, $mailHtml);

			$mailText = str_replace('</p>', '</p>\r\n\r\n', $mailHtml);
			$mailText = str_replace('<br>', '<br>\r\n', $mailText);
			$mailText = str_replace('<br />', '<br />\r\n', $mailText);
			$mailText = strip_tags($mailText);

			$result=$this->sendMail( $email, $this->get_custom_text('email_subscription_subject', $lang_locale), $mailHtml, $mailText, '', false, false, 0, $knewsOptions['smtp_default'] );
			return ($result['ok']==1);
		}

		function validEmail($email) {

			if (empty($email) || !is_email($email)) {
				return false;
			} else {
				global $email_blacklist; $this->load_blacklist();

				$domain = explode('@',$email);
				return (!in_array(strtolower($domain[1]), $email_blacklist));
			}
		}
		function load_blacklist($loadInAnycase = false) {
			global $email_blacklist, $knewsOptions;
			if ($loadInAnycase || ($knewsOptions['email_blacklist']==1 && $email_blacklist=='') ) {
				$email_blacklist=file(KNEWS_DIR . '/includes/disposable_email_blacklist.conf', FILE_IGNORE_NEW_LINES);
			} else {
				$email_blacklist=array();
			}
		}
		function localize_lang($langs_array, $lang, $not_found='en_US') {
			$lang_locale=$not_found;
			foreach ($langs_array as $search_lang) {
				if ($search_lang['language_code']==$lang) {
					if (isset($search_lang['localized_code'])) $lang_locale=$search_lang['localized_code'];
					break;
				}
			}
			return $lang_locale;
		}

		function have_wpml() {
			//return (function_exists('icl_get_languages'));
			global $sitepress;
			return isset($sitepress);
		}
		
		function have_qtranslate() {
			return (function_exists( 'qtrans_init') || function_exists( 'qtranxf_init') || defined( 'QTRANSLATE_FILE'));
		}
		
        function have_polylang() {
			global $polylang;
			return isset($polylang);
            //return (get_option('polylang','') != '');
        }

		/******************************************************************************************
		/*                                FUNCIONS FRONT END
		******************************************************************************************/

		function confirm_user_self() {
			
			if (! $this->initialized) $this->init();

			global $wpdb;
			
			$confkey = $this->get_safe('k');
			$date = $this->get_mysql_date();

			$email_or_id = $this->get_safe('e');
			$field = 'email';
			
			if (!$this->validEmail($email_or_id)) {
				$email_or_id = intval($email_or_id);
				if ($email_or_id==0) return false;
				$field = 'id';
			}
			if ($confkey=='') return false;
			
			$query = "SELECT * FROM ".KNEWS_USERS." WHERE " . $field . "='" . $email_or_id . "' AND confkey='" . $confkey . "'";
			$results = $wpdb->get_row( $query );
			if (!isset($results->id)) return false;

			$date = $this->get_mysql_date();
			$query = "UPDATE " . KNEWS_USERS . " SET state='2', joined='" . $date . "' WHERE " . $field . "='" . $email_or_id . "' AND confkey='" . $confkey . "'";
			$results = $wpdb->query( $query );
			
			return true;
		}
		
		function block_user_self() {
			
			if (! $this->initialized) $this->init();

			global $wpdb;
			
			$submit_id = $this->get_safe('id', 0, 'int');
			$id_newsletter = $this->get_safe('n', 0, 'int');
			$confkey = $this->get_safe('k');
			$date = $this->get_mysql_date();

			$field = 'email';
			$email_or_id = $this->get_safe('e');
			if (!$this->validEmail($email_or_id)) {
				$email_or_id = intval($email_or_id);
				if ($email_or_id==0) return false;
				$field = 'id';
			}
			
			if ($confkey=='') return false;
			
			$query = "SELECT id FROM " . KNEWS_USERS . " WHERE confkey='" . $confkey . "' AND " . $field . "='" . $email_or_id . "'";
			$find_user = $wpdb->get_results( $query );
			
			if (count($find_user) != 1) return false;
	
			$query = "INSERT INTO " . KNEWS_STATS . " (what, user_id, submit_id, date, statkey) VALUES (3, " . $find_user[0]->id . ", " . $submit_id . ", '" . $date . "', 0)";
			$result=$wpdb->query( $query );

			$query = "UPDATE ".KNEWS_USERS." SET state='3' WHERE id=" . $find_user[0]->id;
			$results = $wpdb->query( $query );
			
			return $results;
		}
		
		function getLangs($need_localized=false) {
			global $knewsOptions;

			if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') {
				if (function_exists('icl_get_languages')) {
					$languages = icl_get_languages('skip_missing=0');
					if(!empty($languages)) {

						if ($need_localized) {
							foreach ($languages as $lang) {
								$lang['localized_code'] = $this->wpml_locale($lang['language_code']);
								$languages_localized[]=$lang;
							}
							$languages=$languages_localized;
						}						
						return $languages;
					}
				}
			}
			
			if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='qt') {
				global $q_config;
				
				if (is_array($q_config)) {
					if (isset($q_config['enabled_languages'])) {
						
						$active_langs = $q_config['enabled_languages'];
						
						if (isset($q_config['language'])) {
							$q_def_lang = $q_config['language'];
						} else {
							$q_def_lang = substr(get_bloginfo('language'), 0, 2);
						}

						foreach ($active_langs as $lang) {
							
							$q_nat_lang = $lang; if (isset($q_config['language_name'][$lang])) $q_nat_lang = $q_config['language_name'][$lang];
							$q_trans_lang = $lang; if (isset($q_config['windows_locale'][$lang])) $q_trans_lang = $q_config['windows_locale'][$lang];
							$q_localized_lang = $lang; if (isset($q_config['locale'][$lang])) $q_localized_lang = $q_config['locale'][$lang];
	
							$wpml_style_langs[$lang] = array (
									'active' 			=> (($q_def_lang==$lang) ? 1 : 0),
									'native_name'		=> $q_nat_lang,
									'translated_name'	=> $q_trans_lang,
									'language_code'		=> $lang,
									'localized_code'	=> $q_localized_lang
								);
						}
						if (count($wpml_style_langs) > 0) return $wpml_style_langs;
					}
				}
			}

            if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='pll') {
                global $polylang;
                if (isset($polylang)) {
                    $active_langs = $polylang->model->get_languages_list();
					$pll_active = pll_current_language();
					if ($pll_active=='') $pll_active = $polylang->options['default_lang'];

                    foreach ($active_langs as $lang) {
                        $wpml_style_langs[$lang->slug] = array (
                                'active'            => (($pll_active == $lang->slug) ? 1 : 0),
                                'native_name'       => $lang->name,
                                'translated_name'   => $lang->name,
                                'language_code'     => $lang->slug,
                                'localized_code'    => $lang->description
                            );
                    }
                    if (count($wpml_style_langs) > 0) return $wpml_style_langs;
                }
            }
			
			$short_lang = substr(get_bloginfo('language'), 0, 2);
			return array (
				$short_lang => array (
					'active'=>1, 
					'native_name'=>__('Unique language','knews') . ' (' . $short_lang . ')', 
					'translated_name'=>__('Unique language','knews') . ' (' . $short_lang . ')', 
					'language_code'=>$short_lang, 
					'localized_code'=>get_bloginfo('language')
				)
			);
			
		}
		function get_smtp_multiple($n='', $get_old=false) {

			global $knewsOptions;

				$knews_smtp_multiple = array('1' => array(
						'from_mail_knews' => $knewsOptions['from_mail_knews'],
						'from_name_knews' => $knewsOptions['from_name_knews'],
						'knews_cron' => $knewsOptions['knews_cron'],
						'smtp_knews' => $knewsOptions['smtp_knews'],
						'smtp_host_knews' => $knewsOptions['smtp_host_knews'],
						'smtp_port_knews' => $knewsOptions['smtp_port_knews'],
						'smtp_user_knews' => $knewsOptions['smtp_user_knews'],
						'smtp_pass_knews' => $knewsOptions['smtp_pass_knews'],
						'smtp_secure_knews' => $knewsOptions['smtp_secure_knews'],
						'is_sendmail' => $knewsOptions['is_sendmail']
					)
				);
			if ($n=='') return $knews_smtp_multiple;
			if (isset($knews_smtp_multiple[$n])) return $knews_smtp_multiple[$n];

			return false;
		}
		function get_smtp_selector($n='') {
			global $knewsOptions;
			if ($knewsOptions['smtp_knews']!='1') return false;
			
			$knews_smtp_multiple = $this->get_smtp_multiple();
			if ($n=='') $n = $knewsOptions['smtp_default'];
			
			$options=0; $code = '<select name="knews_select_smtp" autocomplete="off">';
			while ($knews_smtp_once = current($knews_smtp_multiple)) {
				if (!isset($knews_smtp_once['deleted'])) {
					$options++;
					$code .= '<option value="' . key($knews_smtp_multiple) . '"' . ((key($knews_smtp_multiple)==$n) ? ' selected="selected"' : '') . '>';
					$code .= $knews_smtp_once['from_name_knews'] . ' (' . $knews_smtp_once['from_mail_knews'] . ')</option>';
				}
				next($knews_smtp_multiple);
			}
			$code .= '</select>';
			if ($options > 1) return $code;
			return false;
		}
		function pageLang() {
			foreach($this->knewsLangs as $l) {
				if($l['active']) break;
			}
			return $l;
		}

		function wpml_locale($lang) {
			global $wpdb;
			$default_locale = $wpdb->get_results("SELECT default_locale FROM " . $wpdb->prefix . "icl_languages WHERE code='" . $lang . "'");
			if ($default_locale) return $default_locale[0]->default_locale;
			return '';
		}
		
		function tellMeLists($filter=true) {
		
			if (! $this->initialized) $this->init();
		
			global $wpdb;
			
			$active_lang=$this->pageLang();
			$lists=array();

			$query = "SELECT * FROM " . KNEWS_LISTS;

			if (is_user_logged_in()) {
				$query .= " WHERE open_registered='1'";
			} else {
				$query .= " WHERE open='1'";
			}
			$query .= " AND auxiliary=0 ORDER BY orderlist";
			$results = $wpdb->get_results( $query );

			foreach ($results as $list) {
				$valid=true;
				//Primer mirem si hem de descartar per idioma
				if ($active_lang['language_code'] != '' && KNEWS_MULTILANGUAGE) {
					if ($list->langs != '') {
						$lang_sniffer = explode(',', $list->langs);
						if (!in_array($active_lang['language_code'], $lang_sniffer) ) $valid=false;
					}
				}
				if ($valid || !$filter) $lists[$list->id]=$list->name;
								
			}
			return $lists;
			
		}
		
		/* WARNIG: This functions will be deprecated in the future, please, use the get functions instead of print functions */
		function printListsSelector($lists, $mandatory_id=0) {
			echo $this->getListsSelector($lists, $mandatory_id);
		}
		function printAddUserUrl() {
			//This function has wrong name, and is keept for compatibility customisations with old knews versions
			return $this->getAddUserUrl();
		}
		function printLangHidden() {
			echo $this->getLangHidden();
		}
		function printAjaxScript($container, $custom=false) {
			//echo 
			$this->getAjaxScript($container, $custom=false);
		}
		/* end print deprecated functions */

		function getListsSelector($lists, $mandatory_id=0, $multiple='select') {
			if ($mandatory_id != 0) {
				if (isset($lists[$mandatory_id])) {
					$lists = array();
					$lists[$mandatory_id] = 'mandatory';
				}
			}
			if (count($lists) > 1) {
				if ($multiple=='checkbox') {
					$response = '<fieldset>';
					while ($list = current($lists)) {
						$response .= '<input type="checkbox" name="user_knews_list[]" value="' . key($lists) . '" /> ' . $list . '<br />';
						next($lists);
					}
					$response .= '</fieldset>';
				} else {
					$response = '<fieldset><select name="user_knews_list">';
					while ($list = current($lists)) {
						$response .= '<option value="' . key($lists) . '">' . $list . '</option>';
						next($lists);
					}
					$response .= '</select></fieldset>';
				}
			} else if (count($lists) == 1) {
				$response = '<input type="hidden" name="user_knews_list" value="' . key($lists) . '" />';
			} else {
				$response = '<input type="hidden" name="user_knews_list" value="-" />';			
			}
			return $response;
		}

		function getAddUserUrl() {
			return KNEWS_LOCALIZED_ADMIN . 'admin-ajax.php';
		}
		
		function getLangHidden($html=true) {
			global $knewsOptions;
			
			if ($this->get_safe('forcelang') != '') {
				$cut=explode('_',$this->get_safe('forcelang'));
				$lang = array('language_code' => $cut[0], 'localized_code' => $this->get_safe('forcelang') );
			} else {
				$lang = $this->pageLang();
				if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') $lang['localized_code'] = $this->wpml_locale($lang['language_code']);
			}
			if (!$html) return $lang;
			$response = '<input type="hidden" name="lang_user" value="' . $lang['language_code'] . '" />';
			$response .= '<input type="hidden" name="lang_locale_user" value="' . $lang['localized_code'] . '" />';
			return $response;
		}
		
		function getAjaxScript($container, $custom=false) {
			wp_enqueue_script ('knews_subscribe');
		}
		
		function getForm($mandatory_id=0, $args='', $instance=array(), $container='knews_add_user') {
			global $knewsOptions;
			$stylize = false; if ((isset($instance['stylize']) && $instance['stylize']==1) || is_array($args)) $stylize = true;
			$labelwhere = 'outside'; if (isset($instance['labelwhere'])) $labelwhere = $instance['labelwhere'];
			
			$extra_fields = $this->get_extra_fields();
			$response='';
			if (! $this->initialized) $this->init();
			$knews_lists = $this->tellMeLists( (($mandatory_id==0) ? true : false) );

			if (count($knews_lists) > 0) {
				$lang = $this->pageLang();

				if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') $lang['localized_code'] = $this->wpml_locale($lang['language_code']);

				if (is_array($args)) $response .= $args['before_widget'] . $args['before_title'] . $this->get_custom_text('widget_title', $lang['localized_code']) . $args['after_title'];
				
				$response .= '<div class="' . $container . ' knewsform_container" id="knewsform_' . $this->knews_form_n . '">
					<style type="text/css">
					div.' . $container . ' textarea.knewscomment {position:absolute; top:-3000px; left:-3000px;}
					div.' . $container . ' fieldset {border:0;}';
				
				if (isset($instance['customCSS'])) $response .= $instance['customCSS'];
					
				$response .= '</style>
					<form action="' . $this->getAddUserUrl() . '" method="post">';


				$subtitle = ''; if (isset($instance['subtitle']) && $instance['subtitle']=='1') 
				$response .= '<p>' . $this->get_custom_text('widget_subtitle', $lang['localized_code']) . '</p>';
				
				$requiredtxt = '1'; if (isset($instance['requiredtext'])) $requiredtxt = $instance['requiredtext'];

				foreach ($extra_fields as $field) {
					$name = $field->name;
					if (!isset($instance[$name])) $name = strtolower($field->name);
					if (isset($instance[$name]) && ($instance[$name]=='ask' || $instance[$name]=='required')) {
						$response .= '<fieldset class="' . $field->name . '">';
						
						$label = $this->get_custom_text('widget_label_' . $field->name, $lang['localized_code']);
						if ($label=='') $label=$field->name;
						if ($instance[$name]=='required' && $requiredtxt=='1') $label .= '*';
						
						if ($labelwhere == 'outside') $response .= '<label for="' . $field->name . '"' . (($stylize) ? ' style="display:block;"' : '') . '>' . $label . '</label>';

						$response .= '<input type="text" name="' . $field->name . '" value="';
						if ($labelwhere == 'inside') $response .= strip_tags($label) . '" title="' . strip_tags($label);
						$response .= '"' . (($stylize) ? ' style="display:block; margin-bottom:10px;"' : '') . ' />';
						
						if ($instance[$name]=='required') $response .= '<input type="hidden" value="1" name="required_' . $field->name . '" />';
						$response .= '</fieldset>';
					}
				}

				$response .= '<fieldset class="knewsemail">';
				
				if ($labelwhere == 'outside') $response .= '<label for="knewsemail"' . (($stylize) ? ' style="display:block;"' : '') . '>' . $this->get_custom_text('widget_label_email', $lang['localized_code']) . (($requiredtxt=='1') ? '*' : '') . '</label>';
				
				$response .= '<input type="text" name="knewsemail" value="';
				
				if ($labelwhere == 'inside') $response .= strip_tags($this->get_custom_text('widget_label_email', $lang['localized_code']) . (($requiredtxt=='1') ? '*' : '')) . '" title="' . strip_tags($this->get_custom_text('widget_label_email', $lang['localized_code']) . (($requiredtxt=='1') ? '*' : ''));
				$response .= '"' . (($stylize) ? ' style="display:block; margin-bottom:10px;"' : '') . ' /></fieldset>';
				
				$response .= $this->getListsSelector($knews_lists, $mandatory_id, isset($instance['multiple']) ? $instance['multiple'] : '');
				
				$response .= $this->getLangHidden();
				
				if (isset($instance['terms']) && $instance['terms']=='1') {
					$response .= '<fieldset class="knewsterms">';
					if ($stylize) $response .='<span style="display:block; margin-bottom:10px;">';
					$response .= '<input type="checkbox" name="knewsterms" value="1" title="' . strip_tags($this->get_custom_text('widget_label_terms', $lang['localized_code'])) . '" />';
					$response .= '<label for="knewsterms"><small>' . $this->get_custom_text('widget_label_terms', $lang['localized_code']) . (($requiredtxt=='1') ? '*' : '') . '</small></label>';
					$response .= '<input type="hidden" value="1" name="required_knewsterms" />';
					if ($stylize) $response .='</span>';
					$response .= '</fieldset>';
				}

				$key = md5(date('dmY') . wp_create_nonce( 'knews-subscription' ));
				$response .= '<input type="hidden" name="knewskey" value="' . $key . '" />
						<textarea name="knewscomment" class="knewscomment" style="width:150px; height:80px" rows="5" cols="20"></textarea>';

				if ($requiredtxt=='1') {
					$response .= '<fieldset class="requiredtext">';
					if ($stylize) $response .='<span style="display:block; margin-bottom:10px;">';
					$response .= '<small>' . $this->get_custom_text('widget_required', $lang['localized_code']) . '</small>';
					if ($stylize) $response .='</span>';
					$response .= '</fieldset>';
				}

				$response .= '<fieldset class="knewsbutton"><input class="knewsbutton" type="submit" value="' . $this->get_custom_text('widget_button', $lang['localized_code']) . '"' . (($stylize) ? ' style="display:block; margin-bottom:10px;"' : '') . ' /></fieldset>
						<input type="hidden" name="action" value="knewsAddUser" />
					</form>
				</div>';

				if (is_array($args)) $response .=  $args['after_widget'];
				$js = 1; if (isset($instance['script'])) $js = $instance['script']; if ($js != '0') $this->getAjaxScript('div.' . $container);
			}
			$this->knews_form_n++;
			return $response;
		}

		function printWidget($args, $instance) {
			echo $this->getForm(0, $args, $instance);
		}
		function get_cpt($cpt) {
			if ($this->im_pro()) {
				$cpt2 = $this->getCustomPostTypes();
				foreach (array_keys($cpt2) as $pt) {
					$cpt[$pt] = $cpt2[$pt];
				}
			}
			return $cpt;
		}
		function posts_preview ($posts, $lang, $type, $cat, $s, $paged, $status, $ppp) {

			global $knewsOptions, $post;
			$args = array('posts_per_page' => $ppp, 'paged' => $paged, 'post_type' => $type, 'post_status' => $status);
	
			//Polylang support
			if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='pll') $args['lang']=$lang;
	
			if ($cat != 0) $args['cat'] = $cat;
			if ($s != '') $args['s'] = $s;
		
			$myposts = query_posts($args);
			
			//print_r($myposts);
			
			foreach($myposts as $post) {
				setup_postdata($post);
				$t=get_the_title();	if ($t=='') $t = '{no title}';
				$posts[]=array (	'ID' => $post->ID,
									'lang' => $lang,
									'title' => $t,
									'excerpt' => get_the_excerpt()
								);
			}
			global $wp_query;
			$posts['found_posts']=$wp_query->found_posts;

			return $posts;
		}
		
		function htmlentities_corrected($str_in) {
			$list = get_html_translation_table(HTML_ENTITIES);
			unset($list['"']);
			unset($list['<']);
			unset($list['>']);
			unset($list['&']);
		
			$search = array_keys($list);
			$values = array_values($list);
		
			$search = array_map('utf8_encode', $search);

			//Add hungarian chars support
			$search[] = chr(0xc5).chr(0x91); $values[] = '&#337;';
			$search[] = chr(0xc5).chr(0xb1); $values[] = '&#369;';
			$search[] = chr(0xc5).chr(0x90); $values[] = '&#336;';
			$search[] = chr(0xc5).chr(0xb0); $values[] = '&#368;';

			$str_in = str_replace($search, $values, $str_in);
			
			return $str_in;
		}


		function sendMail($recipients, $theSubject, $theHtml, $theText='', $test_array='', $fp=false, $mobile=false, $idNewsletter=0, $id_smtp=1, $idSubmit=0) {
			require('includes/knews_send_mail.php');
			return $reply;
		}

		function im_pro() { return false; }
		
		function read_advice() {
			global $advice;
			if ($advice !='') return $advice;
			
			$last_advice_time = get_option('knews_advice_time',0);
			$now_time = time();
			if ($now_time - $last_advice_time > 86400) {

				$response = wp_remote_get( 'http://www.knewsplugin.com/read_advice.php?v=' . KNEWS_VERSION . '&l=en&p=' . ($this->im_pro() ? '1' : '0') );

				if( is_wp_error( $response ) ) 
					$response = wp_remote_get( 'https://knewsplugin.com/read_advice.php?v=' . KNEWS_VERSION . '&l=en&p=' . ($this->im_pro() ? '1' : '0') );

			} else {
				$response = get_option('knews_advice_response', '0');
				return $response;
			}

			if( is_wp_error( $response ) ) {
				$advice='0';

			} else {
				if (isset($response['body'])) {
					$advice=$response['body'];
					if (substr($advice, 0, 7) == 'advice*') {
						if (substr($advice, 7, 1)=='0') {
							$advice='0';
						} else {
							$advice = substr($advice, 7);
						}
					} else {
						$advice = '0';
					}
				} else {
					$advice='0';
				}
			}
			//Save cache
			$advice_time = time();
			update_option('knews_advice_time', $advice_time);
			update_option('knews_advice_response', $advice);
			
			return $advice;
		}

		/******************************************************************************************
		/*                                   PANELS ADMIN
		******************************************************************************************/
		
		function KnewsAdminNews() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_news.php");
		}
		function KnewsAdminLists() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_lists.php");
		}
		function KnewsAdminUsers() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_users.php");
		}
		function KnewsAdminSubmit() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_submits.php");
		}
		function KnewsAdminImport() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_import.php");
		}
		function KnewsAdminExport() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_export.php");
		}
		function KnewsAdminStats() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_stats.php");
		}
		function KnewsAdminAuto() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_auto.php");
		}
		function KnewsAdminConfig() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_config.php");
		}
		function KnewsAdminSupport() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_support.php");
		}
		function KnewsRegisteredTemplates() {
			if (! $this->initialized) $this->init();
			require( KNEWS_DIR . "/admin/knews_admin_registered_templates.php");
		}
		function knews_dashboard_widget(){
			include_once KNEWS_DIR . '/includes/dashboard-widget.php';
		}
		function dashboard_widget_setup(){
			if ($this->read_advice() != '0') {
				if (current_user_can('manage_options')) {
					$dashboard_widgets_order = (array)get_user_option( "meta-box-order_dashboard" );
					$all_widgets = array();
					foreach($dashboard_widgets_order as $k=>$v){
						$all_widgets = array_merge($all_widgets, explode(',', $v));
					}
					if(!in_array('knews_dash_advice', $all_widgets)){
						$install = true;
					} else {
						$install = false;
					}
					wp_add_dashboard_widget('knews_dash_advice', 'Knews Plugin Message', array($this, 'knews_dashboard_widget'), null);	
					if($install){
						$dashboard_widgets_order['side'] = 'knews_dash_advice' . ',' . @strval($dashboard_widgets_order['side']);
						$user = wp_get_current_user();
						update_user_option($user->ID, 'meta-box-order_dashboard', $dashboard_widgets_order, false);
						$dashboard_widgets_order = (array)get_user_option( "meta-box-order_dashboard" );
					}
				}
			}
		}
		function select_font($what, $extra='', $custom=false) {
			$fonts = array('Verdana, Geneva, sans-serif', 'Georgia, Times New Roman, Times, serif', 'Courier New, Courier, monospace', 'Arial, Helvetica, sans-serif' , 'Tahoma, Geneva, sans-serif', 'Trebuchet MS, Arial, Helvetica, sans-serif', 'Arial Black, Gadget, sans-serif', 'Times New Roman, Times, serif','Palatino Linotype, Book Antiqua, Palatino, serif', 'Lucida Sans Unicode, Lucida Grande, sans-serif','MS Serif, New York, serif','Lucida Console, Monaco, monospace','Comic Sans MS, cursive');
			if (is_array($extra)) {
				foreach ($extra as $e) {
					echo '<option value="' . $e->value . '"' . (($e->value == $what)? ' selected="selected"' : '') . '>'. $e->caption . '</option>';
				}
				foreach ($fonts as $f) {
					echo '<option value="' . $f . '"' . (($f == $what)? ' selected="selected"' : '') . '>'. $f . '</option>';
				}
				if ($custom) echo '<option value="custom"' . (('custom' == $what)? ' selected="selected"' : '') . '>'. __('Custom font','knews') . '</option>';
			}
		}

	}
}

//Initialize the plugin
if (!function_exists("Knews_plugin_ap")) {

	if (class_exists("KnewsPlugin")) {
		$Knews_plugin = new KnewsPlugin();
		define('KNEWS_VERSION', '1.8.2');

		add_filter( 'knews_submit_confirmation', array($Knews_plugin, 'submit_confirmation'), 10, 5 );
		add_filter( 'knews_add_user_db', array($Knews_plugin, 'add_user_db'), 10, 7 );
		add_filter( 'knews_get_cpt', array($Knews_plugin, 'get_cpt'), 10, 1 );
		add_filter( 'knews_posts_preview', array($Knews_plugin, 'posts_preview'), 10, 8 );
		add_filter( 'knews_get_post', 'get_post_knews', 10, 5 );
		add_action( 'knews_echo_ajax_reply', array($Knews_plugin, 'echo_ajax_reply'), 10, 3 );
		add_action( 'knews_echo_dialog', array($Knews_plugin, 'echo_dialog'), 10, 5 );

		function Knews_plugin_ap() {
			global $Knews_plugin;
			if (!isset($Knews_plugin)) return;
	
			if (is_admin()) {
				$Knews_plugin->knews_load_plugin_textdomain();
			}
	
			//Can't see the Knews admin menu? Try to define KNEWS_MENU_POS with another random value in your wp-config.php or functions.php theme!!!
			//example: define ('KNEWS_MENU_POS',102);
			$menu_order=103;
			if (defined('KNEWS_MENU_POS')) $menu_order=KNEWS_MENU_POS;

			$pro_menus=false;

			add_menu_page( 'K-news', $Knews_plugin->im_pro() ? 'K-news Pro' : 'K-news', 'edit_posts', 'knews_news', array(&$Knews_plugin, 'KnewsAdminNews'), plugins_url() . '/knews/images/icon16.png', $menu_order);
			add_submenu_page( 'knews_news', __('Newsletters','knews'), __('Newsletters','knews'), ($pro_menus ? 'knews_manage_newsletters' : 'edit_posts'), 'knews_news', array(&$Knews_plugin, 'KnewsAdminNews'), '');
			add_submenu_page( 'knews_news', __('Mailing lists','knews'), __('Mailing lists','knews'), ($pro_menus ? 'knews_manage_users' : 'edit_posts'), 'knews_lists', array(&$Knews_plugin, 'KnewsAdminLists'), '');
			$hook_asm = add_submenu_page( 'knews_news', __('Subscribers','knews'), __('Subscribers','knews'), ($pro_menus ? 'knews_manage_users' : 'edit_posts'), 'knews_users', array(&$Knews_plugin, 'KnewsAdminUsers'), '');
			add_submenu_page( 'knews_news', __('Submits','knews'), __('Submits','knews'), ($pro_menus ? 'knews_send_newsletters' : 'edit_posts'), 'knews_submit', array(&$Knews_plugin, 'KnewsAdminSubmit'), '');
			add_submenu_page( 'knews_news', __('Import CSV','knews'), __('Import CSV','knews'), ($pro_menus ? 'knews_manage_users' : 'edit_posts'), 'knews_import', array(&$Knews_plugin, 'KnewsAdminImport'), '');
			add_submenu_page( 'knews_news', __('Export CSV','knews'), __('Export CSV','knews'), ($pro_menus ? 'knews_manage_users' : 'edit_posts'), 'knews_export', array(&$Knews_plugin, 'KnewsAdminExport'), '');
			add_submenu_page( 'knews_news', __('Auto-create','knews'), __('Auto-create','knews'), ($pro_menus ? 'knews_configure' : 'edit_posts'), 'knews_auto', array(&$Knews_plugin, 'KnewsAdminAuto'), '');
			add_submenu_page( 'knews_news', __('Stats','knews'), __('Stats','knews'), ($pro_menus ? 'knews_see_stats' : 'edit_posts'), 'knews_stats', array(&$Knews_plugin, 'KnewsAdminStats'), '');
			add_submenu_page( 'knews_news', __('Configuration','knews'), __('Configuration','knews'), ($pro_menus ? 'knews_configure' : 'edit_posts'), 'knews_config', array(&$Knews_plugin, 'KnewsAdminConfig'), '');
			add_submenu_page( 'knews_news', __('Priority Support','knews'), __('Priority Support','knews'), 'edit_posts', ($Knews_plugin->im_pro() ? 'knews_support' : 'knews_config&tab=pro'), array(&$Knews_plugin, 'KnewsAdminSupport'), '');

			$registered_templates = apply_filters('knews_registered_templates','');
			if (is_array($registered_templates)) {
				add_submenu_page( 'knews_news', __('Registered templates','knews'), __('Registered templates','knews'), ($pro_menus ? 'knews_configure' : 'edit_posts'), 'knews_registered_templates', array(&$Knews_plugin, 'KnewsRegisteredTemplates'), '');
			}
			
	        add_action('wp_dashboard_setup', array(&$Knews_plugin, 'dashboard_widget_setup'));
			if ($Knews_plugin->im_pro()) add_action( "load-$hook_asm", 'knews_asm_add_option' );
		}

		//WP Cron :: http://blog.slaven.net.au/2007/02/01/timing-is-everything-scheduling-in-wordpress/
		function knews_wpcron_function() {
			require(dirname(__FILE__) . '/direct/knews_cron_do.php');
			if( $this->im_networked() ) {
				$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
				$url = 'http' . $s . '://' . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
				if(!headers_sent()) {
					//If headers not sent yet... then do php redirect
					header('Location: '.$url, true, 302);
					exit;
				} else {
					//If headers are sent... do javascript redirect... if javascript disabled, do html redirect.
					echo '<script type="text/javascript">';
					echo 'window.location.href="'.$url.'";';
					echo '</script>';
					echo '<noscript>';
					echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
					echo '</noscript>';
					exit;
				}
			}
		}
		function knews_wpcron_automate() {
			global $Knews_plugin;
			require(dirname(__FILE__) . '/includes/automated_jobs.php');
			if ($Knews_plugin->im_pro()) require(dirname(__FILE__) . '/includes/knews_bounce.php');
		}
		function knews_more_reccurences($schedules) {
			$schedules['knewstime'] = array('interval' => 600, 'display' => 'Knews 10 minutes wpcron submit');
			return $schedules;
		}
		add_filter('cron_schedules', 'knews_more_reccurences');
		add_action( 'knews_wpcron_function_hook', 'knews_wpcron_function' );
		add_action( 'knews_wpcron_automate_hook', 'knews_wpcron_automate' );

		function knews_deactivate() {
			if (wp_next_scheduled('knews_wpcron_function_hook')) wp_clear_scheduled_hook('knews_wpcron_function_hook');
			if (wp_next_scheduled('knews_wpcron_automate_hook')) wp_clear_scheduled_hook('knews_wpcron_automate_hook');
		}
		register_deactivation_hook(__FILE__, 'knews_deactivate');

		function knews_activate() {
			
			//if (!wp_next_scheduled('knews_wpcron_automate_hook')) wp_schedule_event( time(), 'twicedaily', 'knews_wpcron_automate_hook');
			if (!wp_next_scheduled('knews_wpcron_automate_hook')) wp_schedule_event( time(), 'hourly', 'knews_wpcron_automate_hook');

			$look_options = get_option('KnewsAdminOptions');
			if (isset($look_options['knews_cron'])) {
				if ($look_options['knews_cron']!='cronwp') return;
			}
			if (!wp_next_scheduled('knews_wpcron_function_hook')) wp_schedule_event( time(), 'knewstime', 'knews_wpcron_function_hook' );
		}
		register_activation_hook(__FILE__, 'knews_activate');

	}

	if (isset($Knews_plugin)) {
		add_action(basename(__FILE__), array(&$Knews_plugin, 'init'));
		add_action('admin_menu', 'Knews_plugin_ap');
		add_action("widgets_init", create_function( '', 'register_widget( "knews_widget" );' ) );
		if ($Knews_plugin->im_pro()) add_action("widgets_init", create_function( '', 'register_widget( "knewssn2_widget" );' ) );
	}

	function knews_load_jquery() {
		if (!is_admin()) wp_enqueue_script( 'jquery' );
	}    
	add_action('init', 'knews_load_jquery');
	
	function knews_admin_enqueue() {
		global $Knews_plugin;
		if ($Knews_plugin->get_safe('page')=='knews_news' || $Knews_plugin->get_safe('page')=='knews_submit' || $Knews_plugin->get_safe('page')=='knews_config' ||
			basename($_SERVER['SCRIPT_FILENAME']) == 'widgets.php') {
			add_thickbox();
		}

		if ($Knews_plugin->get_safe('page')=='knews_news' && $Knews_plugin->get_safe('section')=='edit') {
			if (! $Knews_plugin->initialized) $Knews_plugin->init();
			wp_enqueue_style( 'farbtastic' );
			wp_enqueue_script( 'farbtastic' );
			
			wp_enqueue_style( 'knews-parent-editor-css', KNEWS_URL . '/wysiwyg/parent_editor.css', array(), KNEWS_VERSION );
			wp_enqueue_script( 'knews-parent-editor-js', KNEWS_URL . '/wysiwyg/parent_editor.js', array( 'farbtastic', 'jquery' ), KNEWS_VERSION ); 
			//wp_enqueue_style( 'wp-color-picker');
			//wp_enqueue_script( 'wp-color-picker');
		}

		//wp_enqueue_script('media-upload');
		if ( get_bloginfo( 'version' ) >= '3.5' && $Knews_plugin->get_safe('page')=='knews_news' && $Knews_plugin->get_safe('section')=='edit') {
			wp_enqueue_media();
	        //wp_enqueue_style( 'wp-color-picker' );
			//wp_enqueue_script( 'wp-color-picker' );
		}

		if ( get_bloginfo( 'version' ) >= '3.3' ) {
			// Register the pointer styles and scripts
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

		//wp_enqueue_script('thickbox',null,array('jquery'));
		//wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	}
	add_action('admin_enqueue_scripts', 'knews_admin_enqueue');
    
    function knews_admin_footer () {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'widgets.php') require ('includes/widgets_footer.php');
	}
	add_action('in_admin_footer', 'knews_admin_footer');
	
	function knews_popup() {
		global $Knews_plugin;
		if (! $Knews_plugin->initialized) $Knews_plugin->init();
		
		//Deprecated, start:
		if ($Knews_plugin->get_safe('subscription')=='ok' || $Knews_plugin->get_safe('subscription')=='error' || $Knews_plugin->get_safe('unsubscribe')=='ok' || $Knews_plugin->get_safe('unsubscribe')=='error') {
			if (!defined ('KNEWS_POP_DIALOG')) define ('KNEWS_POP_DIALOG', true);
		}
		//Deprecated, end.
		if ($Knews_plugin->get_safe('knews')=='confirmUser') {
			$knews_subscription_result = $Knews_plugin->confirm_user_self();
			if (!defined ('KNEWS_POP_DIALOG')) define ('KNEWS_POP_DIALOG', true);
		}
		if ($Knews_plugin->get_safe('knews')=='unsubscribe') {
			$knews_block_result = $Knews_plugin->block_user_self();
			if (!defined ('KNEWS_POP_DIALOG')) define ('KNEWS_POP_DIALOG', true);
		}

		//if ($Knews_plugin->get_safe('knewspophome')=='1'
		if ( $Knews_plugin->get_safe('knews')=='readEmail') {
			if (!defined ('KNEWS_POP_NEWS')) define('KNEWS_POP_NEWS', true);
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				knews_launch_iframe('<?php echo KNEWS_LOCALIZED_ADMIN; ?>admin-ajax.php?action=knewsReadEmail&id=<?php echo $Knews_plugin->get_safe('id',0,'int'); ?>&e=<?php echo $Knews_plugin->get_safe('e') . '&k=' . $Knews_plugin->get_safe('k');
				if ($Knews_plugin->get_safe('m') != '') echo '&mbl=' . ($Knews_plugin->get_safe('m')=='mbl' ? '1' : '0'); 
				if ($Knews_plugin->get_safe('mbl') != '') echo '&mbl=' . $Knews_plugin->get_safe('mbl'); 
				?>');
			});
			</script>
			<?php
		}
		if (defined('KNEWS_POP_HOME') || defined('KNEWS_POP_DIALOG') || defined('KNEWS_POP_NEWS')) {
			if (! $Knews_plugin->initialized) $Knews_plugin->init();
			require_once( KNEWS_DIR . '/includes/dialogs.php');
		}
	}
	add_action('wp_footer', 'knews_popup', 100);
	
	function knews_admin_notice() {
		require('includes/admin_notices.php');
	}
	add_action( 'admin_notices', 'knews_admin_notice' );
	
	function knews_plugin_form($atts) {
		global $Knews_plugin; if (!isset($Knews_plugin)) return '';
		
		$id_list = ((isset($atts['id'])) ? intval($atts['id']) : 0);
		return $Knews_plugin->getForm($id_list, '', $atts);
	}
	add_shortcode("knews_form", "knews_plugin_form");

	function knews_plugin_form_ext() {
		global $Knews_plugin; if (!isset($Knews_plugin)) die();
		
		$id_list = $Knews_plugin->get_safe('id', 0, 'int');

		header('X-Frame-Options: GOFORIT');
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head profile="http://gmpg.org/xfn/11">
		<meta name="robots" content="NOINDEX,NOFOLLOW">
		<meta charset="utf-8" />
		<title>Knews Subscription</title>';
		wp_head();
		if ($Knews_plugin->get_safe('css') != '') {
			$upload_dir = wp_upload_dir();
			echo '<link rel="stylesheet" href="' . $upload_dir['baseurl'] . '/' . $Knews_plugin->get_safe('css') . '.css" type="text/css" media="all" />';
		}
		echo '</head>';
		echo '<body>';
		echo $Knews_plugin->getForm($id_list, '', $_GET);
		echo '</body>
		</html>';
				
		die();
	}
	add_action('wp_ajax_knewsRemote', 'knews_plugin_form_ext' );
	add_action('wp_ajax_nopriv_knewsRemote', 'knews_plugin_form_ext' );
	
	function knews_post_options_fn() {
		global $Knews_plugin, $post, $knewsOptions, $wpdb;
		if (! $Knews_plugin->initialized) $Knews_plugin->init();
		
		$value = intval(get_post_meta($post->ID, '_knews_automated', true));
		if ($value==='') $value = $knewsOptions['def_autom_post'];
		
		echo '<p>' . __('Include this post in the automated newsletters?','knews') . ' <select name="knews_automated_post" id="knews_automated_post">';
		echo '<option value="1"' . (($value == 1) ? ' selected="selected"' : '') . '>' . __('Yes','knews') . '</option>';
		echo '<option value="0"' . (($value == 0) ? ' selected="selected"' : '') . '>' . __('No','knews') . '</option>';
		echo '</select></p>';
		
		$sql = 'SELECT kn.name, kap.id_news, kn.mobile FROM ' . KNEWS_NEWSLETTERS . ' as kn, ' . KNEWS_AUTOMATED_POSTS . ' as kap WHERE kn.mobile=0 AND kap.id_post=' . $post->ID . ' AND kap.id_news = kn.id';
		$results=$wpdb->get_results($sql);
		if (count($results) > 0) {
			echo '<p><strong>' . __('This post has been included into:','knews') . '</strong></p>';
			foreach ($results as $r) {
				echo '<p style="margin:0; padding:0;">&#8226; <a href="' . get_admin_url() . 'admin-ajax.php?action=knewsReadEmail&id=' . $r->id_news . '&preview=1" target="_blank">' . $r->name . '</a></p>';
			}
		} else {
			echo '<p>' . __('This post still not included into any automated newsletter.','knews') . '</p>';
		}
	}
	function knews_options_box() {
		add_meta_box('knews_post_options', __('Knews Post Options','knews'), 'knews_post_options_fn', 'post', 'side', 'core');
	}
	function knews_options_save($postID){
		global $Knews_plugin, $knewsOptions;
		if (! $Knews_plugin->initialized) $Knews_plugin->init();

		if (isset($_POST['knews_automated_post'])) {
			$value=$Knews_plugin->post_safe('knews_automated_post', $knewsOptions['def_autom_post'], 0, 'int');
			update_post_meta($postID, '_knews_automated', $value);
		}
	}
	add_action('admin_menu', 'knews_options_box');
	add_action('save_post', 'knews_options_save');

	/************************************************** AJAX CALLS ******************************************/

	function knews_ajax_select_post() {
		require( dirname(__FILE__) . "/direct/select_post.php");
	}
	function knews_ajax_pick_color() {
		require( dirname(__FILE__) . "/direct/color_picker.php");
	}
	function knews_ajax_pick_font() {
		require( dirname(__FILE__) . "/wysiwyg/fontpicker/index.php");
	}
	function knews_safe_download() {
		require( dirname(__FILE__) . "/direct/download.php");
	}
	function knews_edit_news() {
		require( dirname(__FILE__) . "/direct/edit_news.php");
	}
	function knews_add_user() {
		require( dirname(__FILE__) . "/direct/knews_adduser.php");
	}
	function knews_confirm_user() {
		require( dirname(__FILE__) . "/direct/knews_confirmuser.php");
	}
	function knews_cron() {
		global $Knews_plugin;
		if ( get_current_blog_id() != $Knews_plugin->KNEWS_MAIN_BLOG_ID && $Knews_plugin->im_networked() ) die("You must call the main blog www.yourdomain.com/wp-admin/admin-ajax.php?action=knewsCron URL");
		$cron_time = time();
		update_option('knews_cron_time', $cron_time);

		knews_cron_do();
	}
	function knews_cron_do() {
		require( dirname(__FILE__) . "/direct/knews_cron_do.php");
	}
	function knews_read_email() {
		require( dirname(__FILE__) . "/direct/knews_read_email.php");
	}
	function knews_unsubscribe() {
		require( dirname(__FILE__) . "/direct/knews_unsubscribe.php");
	}
	function knews_off_warn() {
		require( dirname(__FILE__) . "/direct/off_warn.php");
	}
	function knews_resize_img() {
		global $Knews_plugin;
	
		if (! $Knews_plugin->initialized) $Knews_plugin->init();
	
		$url_img = $Knews_plugin->post_safe('urlimg');
		$width = intval($Knews_plugin->post_safe('width'));
		$height = intval($Knews_plugin->post_safe('height'));
		$Knews_plugin->template_id = $Knews_plugin->post_safe('template_id','unknown');
		/*
		$url_img = $Knews_plugin->get_safe('urlimg');
		$width = intval($Knews_plugin->get_safe('width'));
		$height = intval($Knews_plugin->get_safe('height'));
		$Knews_plugin->template_id = $Knews_plugin->get_safe('template_id','unknown');
		*/
	
		require( dirname(__FILE__) . "/includes/resize_img.php");

		$jsondata = knews_resize_img_fn($url_img, $width, $height);
		echo json_encode($jsondata);

		die();

	}
	function knews_save_news() {
		require( dirname(__FILE__) . "/direct/save_news.php");
	}
	function knews_see_fails() {
		require( dirname(__FILE__) . "/direct/see_fails.php");
	}
	function knews_test_smtp() {
		require( dirname(__FILE__) . "/direct/test_smtp.php");
	}
	function knews_track() {
		require( dirname(__FILE__) . "/direct/track.php");
	}
	function knews_htmleditor() {
		require( dirname(__FILE__) . "/direct/html_edit.php");
	}
	function knews_ajax_deny() {
		die();
	}
	function knews_spamcheck() {
		require( dirname(__FILE__) . "/includes/spamcheck.php");
	}
	function knews_spamcheck2() {
		require( dirname(__FILE__) . "/includes/spamcheck2.php");
	}
	add_action('wp_ajax_knewsSelPost', 'knews_ajax_select_post' );
	add_action('wp_ajax_nopriv_knewsSelPost', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsPickColor', 'knews_ajax_pick_color' );
	add_action('wp_ajax_nopriv_knewsPickColor', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsPickFont', 'knews_ajax_pick_font' );
	add_action('wp_ajax_nopriv_knewsPickFont', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsSafeDownload', 'knews_safe_download' );
	add_action('wp_ajax_nopriv_knewsSafeDownload', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsEditNewsletter', 'knews_edit_news' );
	add_action('wp_ajax_nopriv_knewsEditNewsletter', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsAddUser', 'knews_add_user' );
	add_action('wp_ajax_nopriv_knewsAddUser', 'knews_add_user' );

	add_action('wp_ajax_knewsConfirmUser', 'knews_confirm_user' );
	add_action('wp_ajax_nopriv_knewsConfirmUser', 'knews_confirm_user' );

	add_action('wp_ajax_knewsCron', 'knews_cron' );
	add_action('wp_ajax_nopriv_knewsCron', 'knews_cron' );

	add_action('wp_ajax_knewsCronDo', 'knews_cron_do' );
	add_action('wp_ajax_nopriv_knewsCronDo', 'knews_cron_do' );

	add_action('wp_ajax_knewsReadEmail', 'knews_read_email' );
	add_action('wp_ajax_nopriv_knewsReadEmail', 'knews_read_email' );

	add_action('wp_ajax_knewsUnsubscribe', 'knews_unsubscribe' );
	add_action('wp_ajax_nopriv_knewsUnsubscribe', 'knews_unsubscribe' );

	add_action('wp_ajax_knewsOffWarn', 'knews_off_warn' );
	add_action('wp_ajax_nopriv_knewsOffWarn', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsResizeImg', 'knews_resize_img' );
	add_action('wp_ajax_nopriv_knewsResizeImg', 'knews_ajax_deny' );
	
	add_action('wp_ajax_knewsSaveNews', 'knews_save_news' );
	add_action('wp_ajax_nopriv_knewsSaveNews', 'knews_ajax_deny' );
	
	add_action('wp_ajax_knewsSeeFails', 'knews_see_fails' );
	add_action('wp_ajax_nopriv_knewsSeeFails', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsTestSMTP', 'knews_test_smtp' );
	add_action('wp_ajax_nopriv_knewsTestSMTP', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsTrack', 'knews_track' );
	add_action('wp_ajax_nopriv_knewsTrack', 'knews_track' );

	add_action('wp_ajax_knewsForceAutomated', 'knews_wpcron_automate' );
	add_action('wp_ajax_nopriv_knewsForceAutomated', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsHTMLedit', 'knews_htmleditor' );
	add_action('wp_ajax_nopriv_knewsHTMLedit', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsSpamcheck', 'knews_spamcheck' );
	add_action('wp_ajax_nopriv_knewsSpamcheck', 'knews_ajax_deny' );

	add_action('wp_ajax_knewsSpamcheck2', 'knews_spamcheck2' );
	add_action('wp_ajax_nopriv_knewsSpamcheck2', 'knews_ajax_deny' );
	// Add the pointer javascript
	function knews_add_pointer_scripts() {
		global $Knews_plugin, $knewsOptions;
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		if ( !in_array( 'knews_pro_welcome', $dismissed ) && $Knews_plugin->im_pro()) {
			$content = '<h3>' . __('Welcome to Knews Pro','knews') . '</h3><p>' . sprintf(__('You can configure the new features <br />into %s Knews Pro Options tab','knews'), '<a href="admin.php?page=knews_config&tab=pro">') . '</a></p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'knews_pro_welcome');

		} else if ( !in_array( 'french_bug_knews', $dismissed ) && substr(get_bloginfo('language'), 0, 2) == 'fr') {
			$content = '<h3>' . __('French release: Important bug solved.','knews') . '</h3><p>' . sprintf(__('Previous French releases can\'t test SMTP config or use our Real Anti-Spam test. Please, %s check it now.','knews') . "</a>", '<a href="' . get_admin_url() . 'admin.php?page=knews_config&tab=advanced&subtab=2">') . '</p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'french_bug_knews');

		} else if (strpos($_SERVER['REQUEST_URI'],'knews_config') === false && !in_array( 'config_knews', $dismissed ) ) {
			$content = '<h3>' . __('Welcome to Knews.','knews') . '</h3><p>' . sprintf(__('Please, go to %s configuration page','knews') . "</a>", '<a href="' . get_admin_url() . 'admin.php?page=knews_config">') . '</p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'config_knews');
	
		} else if (strpos($_SERVER['REQUEST_URI'],'knews_news&section=edit') !== false && !in_array( 'knews_videotutorial', $dismissed ) ) {
			$content = '<h3>Videotutorial</h3><p>' . sprintf(__('There is a videotutorial about Knews WYSIWYG Editor, %s view it in Youtube','knews'), '<a href="http://www.youtube.com/watch?v=axDO5ZIW-9s" target="_blank">') . '</a></p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'knews_videotutorial');

		} else if (strpos($_SERVER['REQUEST_URI'],'knews_news&section=send') !== false && !in_array( 'real_spam_test', $dismissed ) ) {
			$content = '<h3>New diagnose tool: Real Spam Test</h3><p>We\\\'re just added a powerful and easy to use new tool: simple one button click and get real <strong>Spam Assassin</strong> live result about your SMTP configuration <a href="admin.php?page=knews_config&tab=advanced&subtab=2">here</a>.</p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'real_spam_test');

		} else if (function_exists('is_plugin_active') && is_plugin_active('gravityforms/gravityforms.php') && !in_array( 'knews_gf', $dismissed )  ) {
			$content = '<h3>Knews loves Gravity Forms</h3><p>Now you can add a subscription to Knews lists on any <strong>Gravity Forms</strong> form, with our new free plugin: <a href="https://wordpress.org/plugins/knews-gravity-forms-glue/" target="_blank">Knews + Gravity Forms Glue</a> hosted on wordpress.org</p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'knews_gf');

		} else if (defined('WPCF7_VERSION') && !in_array( 'knews_cf7', $dismissed )  ) {
			$content = '<h3>Knews loves Contact Form 7</h3><p>Now you can add a subscription to Knews lists on any <strong>Contact Form 7</strong> form, with our new free plugin: <a href="https://wordpress.org/plugins/knews-contact-form-7-glue/" target="_blank">Knews + Contact Form 7 Glue</a> hosted on wordpress.org</p>';
			knews_add_pointer_scripts_js($content, '#toplevel_page_knews_news', 'knews_cf7');

		} else if (strpos($_SERVER['REQUEST_URI'],'widgets.php') !== false && !in_array( 'knews_multiple_lists', $dismissed )  ) {
			$content = '<h3>Knews allow multiple subscription at once</h3><p>There is a new option in Knews subscription widget: <strong>Allow multiple subscription</strong>. It will change the combobox for one checkbox per every open mailing list.</p>';
			knews_add_pointer_scripts_js($content, '#menu-appearance', 'knews_multiple_lists');
		}

	}
	function knews_add_pointer_scripts_js($content, $handler, $pointer, $edge = 'left', $align = 'center' ) {
	?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			$('<?php echo $handler; ?>').pointer({
				content: '<?php echo str_replace("\\'", "\'", str_replace("'","\'",$content)); ?>',
				position: {
					edge: '<?php echo $edge; ?>',
					align: '<?php echo $align; ?>'
				},
				close:  function() {
					$.post( ajaxurl, {
						pointer: '<?php echo $pointer; ?>',
						action: 'dismiss-wp-pointer'
					});
				}
			}).pointer('open');
		});
		//]]>
		</script>
	<?php
	}
	if ( get_bloginfo( 'version' ) >= '3.3' ) {
		// Add pointer javascript
		add_action( 'admin_print_footer_scripts', 'knews_add_pointer_scripts' );
	}

	class knews_widget extends WP_Widget {
	
		public function __construct() {
			parent::__construct(
				'knews_widget', // Base ID
				'Knews Subscription Form Widget', // Name
				array( 'description' => __( 'Add a subscription form into the sidebar', 'knews' ), ) // Args
			);
		}
	
		public function widget( $args, $instance ) {
			global $Knews_plugin;
			$Knews_plugin->printWidget($args, $instance);
		}
	
		public function update( $new_instance, $old_instance ) {
			return $new_instance;
		}
	
		public function form( $instance ) {
			require ('includes/widget_form.php');
		}
	
	} // class Knews_Widget
	
	function knews_aj_posts_where( $where ) {
		global $knews_aj_look_date, $knewsOptions, $Knews_plugin;
   		return $where . " AND " . ((intval($knewsOptions['edited_autom_post'])==1) ? 'post_modified_gmt' : 'post_date_gmt') . " > '" . $knews_aj_look_date . "' "
					  . " AND post_date_gmt <= '" . $Knews_plugin->get_mysql_date() . "' AND post_status='publish'";
	}

	function knews_init( $qvars ) {
		global $wp;
		$qvars=$wp->extra_query_vars;
		if (isset($_GET['knews'])) {
			if (!is_array($qvars)) $qvars=array();
			if (!in_array('knews', $qvars)) $qvars[] = 'knews';
			if (!in_array('k', $qvars)) $qvars[] = 'k';
			if (!in_array('e', $qvars)) $qvars[] = 'e';
			if (!in_array('n', $qvars)) $qvars[] = 'n';
			if (!in_array('id', $qvars)) $qvars[] = 'id';
			if (!in_array('m', $qvars)) $qvars[] = 'm';
			if (!in_array('mbl', $qvars)) $qvars[] = 'mbl';
		}
		$wp->extra_query_vars=$qvars;
	}
	add_filter('init', 'knews_init' );

}

?>
