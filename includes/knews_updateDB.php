<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

global $wpdb, $knewsOptions, $Knews_plugin;

if (version_compare(get_option('knews_version','0.0.0'), '1.8.2') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.4.1') < 0)) {
	if (!knews_add_column(KNEWS_AUTOMATED, 'submit_time', "varchar(5) NOT NULL DEFAULT '00:00'")) return;
}
if (version_compare(get_option('knews_version','0.0.0'), '1.7.0') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.3.3') < 0)) {
	//Some oldie installs keep twicedaily schedule instead hourly
	if (wp_next_scheduled('knews_wpcron_automate_hook')) wp_clear_scheduled_hook('knews_wpcron_automate_hook');
	wp_schedule_event( time(), 'hourly', 'knews_wpcron_automate_hook');
}

if (version_compare(get_option('knews_version','0.0.0'), '1.7.0') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.3.1') < 0)) {
	if (!knews_add_column(KNEWS_NEWSLETTERS, 'html_bodytag', "mediumtext NOT NULL DEFAULT ''")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.6.3') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.2.5') < 0)) {
	if (!knews_add_column(KNEWS_AUTOMATED, 'include_cats', "varchar(255) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'exclude_cats', "varchar(255) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'include_tags', "varchar(255) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'exclude_tags', "varchar(255) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'include_postypes', "varchar(255) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'use_post_embed_pref', "bigint(1) UNSIGNED NOT NULL DEFAULT '1'")) return;
}
if ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.0.9') < 0) {
	//Let's add new cappabilitie 
	global $wp_roles;
	$all_roles = $wp_roles->roles;
	foreach ($all_roles as $key=>$role) {
		if (isset($role['capabilities']['knews_manage_newsletters']) && !isset($role['capabilities']['knews_send_newsletters'])) {
			$role = get_role($key);
			$role->add_cap('knews_send_newsletters', true);
		}
	}
}
// in the 2.0.9 this fields are introduced, but some users haven't it (update issue in 2.1.0 & 2.1.1 versions):
if (version_compare(get_option('knews_version','0.0.0'), '1.4.9') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.1.2') < 0)) {
	if (!knews_add_column(KNEWS_NEWSLETTERS_SUBMITS, 'id_smtp', "bigint(20) NOT NULL DEFAULT 1")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'id_smtp', "bigint(20) NOT NULL DEFAULT 1")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.1.0') < 0) {
	//The 1.1.0 added fields & tables

	if (!knews_add_column(KNEWS_NEWSLETTERS, 'lang', "varchar(3) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_NEWSLETTERS, 'automated', "varchar(1) NOT NULL DEFAULT 0")) return;

	if (!$this->tableExists(KNEWS_NEWSLETTERS_SUBMITS)) {
	
		$sql =	"CREATE TABLE " .KNEWS_NEWSLETTERS_SUBMITS . " (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) UNSIGNED NOT NULL DEFAULT " . $this->KNEWS_MAIN_BLOG_ID . ",
				newsletter int(11) NOT NULL,
				finished tinyint(1) NOT NULL,
				paused tinyint(1) NOT NULL,
				start_time timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				end_time timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				users_total int(11) NOT NULL,
				users_ok int(11) NOT NULL,
				users_error int(11) NOT NULL,
				priority tinyint(4) NOT NULL,
				strict_control varchar(100) NOT NULL,
				emails_at_once int(11) NOT NULL,
				special varchar(32) NOT NULL,
	
				id_smtp bigint(20) NOT NULL DEFAULT 1,
	
				UNIQUE KEY id (id)
			   )$charset_collate;";
		
		dbDelta($sql);

	} else {

		$sql = "SHOW COLUMNS FROM " . KNEWS_NEWSLETTERS_SUBMITS . " LIKE 'blog_id'";
		$exists = $wpdb->get_results($sql);
		if (count($exists)==0) {
	
			if (!knews_add_column(KNEWS_NEWSLETTERS_SUBMITS, 'blog_id', "bigint(20) UNSIGNED NOT NULL DEFAULT " . $this->KNEWS_MAIN_BLOG_ID)) return;
	
		}
	}
	
	if ($wpdb->prefix != $wpdb->base_prefix) {
		if ($this->tableExists($wpdb->prefix . 'knewsubmits')) {
	
			$query = "SELECT * FROM " . $wpdb->prefix . "knewsubmits";
			$submit_pend = $wpdb->get_results( $query );
			
			foreach ($submit_pend as $sp) {
				
				$query = 'INSERT INTO ' . KNEWS_NEWSLETTERS_SUBMITS . ' (blog_id, newsletter, finished, paused, start_time, users_total, users_ok, users_error, priority, strict_control, emails_at_once, special, end_time, id_smtp) VALUES (' . get_current_blog_id() . ', ' . $sp->newsletter . ', ' . $sp->finished . ', ' . $sp->paused . ', \'' . $sp->start_time . '\', ' . $sp->users_total . ', ' . $sp->users_ok . ', ' . $sp->users_error . ', ' . $sp->priority . ', \'' . $sp->strict_control . '\', ' . $sp->emails_at_once . ', \'' . $sp->special . '\', \'' . $sp->end_time . '\', ' . $knewsOptions['smtp_default'] . ')';
				$results = $wpdb->query( $query );
				$submit_confirmation_id = $Knews_plugin->real_insert_id();

				if ($submit_confirmation_id != 0) {
					$query  = "UPDATE " . KNEWS_NEWSLETTERS_SUBMITS_DETAILS . " SET submit=" . $submit_confirmation_id . " WHERE submit=" . $sp->id;
					$results = $wpdb->query( $query );
				}
			}
		}
	}	
}

if (version_compare(get_option('knews_version','0.0.0'), '1.2.2') < 0) {
	//Missing field in the 1.2.0 and 1.2.1 installations bug
	if ($this->tableExists(KNEWS_AUTOMATED)) {
		
		if (strcasecmp($wpdb->get_var("show columns from " . KNEWS_AUTOMATED . " like 'last_run'"), 'last_run') != 0) {
			if (!knews_add_column(KNEWS_AUTOMATED, 'last_run', "datetime NOT NULL")) return;
		}
	}
}

if (version_compare(get_option('knews_version','0.0.0'), '1.2.0') < 0) {
	//The 1.2.0 added fields & tables

	$sql =	"CREATE TABLE " .KNEWS_AUTOMATED . " (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			selection_method varchar(1) NOT NULL,
			target_id bigint(20) UNSIGNED NOT NULL,
			newsletter_id bigint(20) UNSIGNED NOT NULL,
			lang varchar(3) NOT NULL,
			paused varchar(1) NOT NULL,
			auto varchar(1) NOT NULL,
			every_mode int(11) NOT NULL,
			every_time int(11) NOT NULL,
			what_dayweek int(11) NOT NULL,
			every_posts int(11) NOT NULL,
			last_run datetime NOT NULL,
			UNIQUE KEY id (id)
		   )$charset_collate;";
		   
	dbDelta($sql);

	$sql =	"CREATE TABLE " .KNEWS_AUTOMATED_POSTS . " (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			id_automated bigint(20) UNSIGNED NOT NULL,
			id_post bigint(20) UNSIGNED NOT NULL,
			id_news bigint(20) UNSIGNED NOT NULL,
			when_scheduled datetime NOT NULL,
			UNIQUE KEY id (id)
		   )$charset_collate;";
		   
	dbDelta($sql);

}

if (version_compare(get_option('knews_version','0.0.0'), '1.2.3') < 0) {
	//The 1.2.3 added fields & tables
	if (!knews_add_column(KNEWS_LISTS, 'orderlist', "int(11) NOT NULL DEFAULT 0")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.2.6') < 0) {
	if (!knews_add_column(KNEWS_NEWSLETTERS, 'mobile', "varchar(1) NOT NULL DEFAULT 0")) return;
	if (!knews_add_column(KNEWS_NEWSLETTERS, 'id_mobile', "bigint(20) UNSIGNED NOT NULL DEFAULT 0")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'emails_at_once', "int(11) NOT NULL DEFAULT 25")) return;
	if (!knews_update_sql("ALTER TABLE " . KNEWS_USERS . " CHANGE lang lang varchar(12) NOT NULL;")) return;
	if (!knews_update_sql("ALTER TABLE " . KNEWS_NEWSLETTERS . " CHANGE lang lang varchar(12) NOT NULL;")) return;
	if (!knews_update_sql("ALTER TABLE " . KNEWS_AUTOMATED . " CHANGE lang lang varchar(12) NOT NULL;")) return;


	$sql =	"CREATE TABLE " .KNEWS_AUTOMATED_SELECTION . " (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			id_automated bigint(20) UNSIGNED NOT NULL,
			type varchar(100) NOT NULL,
			value varchar(100) NOT NULL,
			UNIQUE KEY id (id)
		   )$charset_collate;";
		   
	dbDelta($sql);

}

if (version_compare(get_option('knews_version','0.0.0'), '1.4.2') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.0.3') < 0)) {
	//Database updates for 1.4.2 Free & 2.0.3 Pro version
	if (!knews_add_column(KNEWS_AUTOMATED, 'run_yet', "int(1) NOT NULL DEFAULT 1")) return;
	//if (!knews_add_column(KNEWS_USERS, 'ip', "varchar(32) NOT NULL")) return;
	//if (!knews_add_column(KNEWS_USERS, 'alive', "datetime NOT NULL")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.4.4') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.0.6') < 0)) {
	if (!knews_add_column(KNEWS_STATS, 'statkey', "bigint(20) UNSIGNED NOT NULL DEFAULT 0")) return;
	if (!knews_add_column(KNEWS_USERS, 'ip', "varchar(45) NOT NULL")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.5.3') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.1.1') < 0)) {
	if (!knews_add_column(KNEWS_KEYS, 'param_href', "mediumtext NOT NULL")) return;

	$query  = "UPDATE " . KNEWS_STATS . " SET what=7 WHERE what=6";
	$results = $wpdb->query( $query );

	if (!knews_add_column(KNEWS_KEYS, 'param_href', "mediumtext NOT NULL")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.5.3') < 0 || ( version_compare(get_option('knews_version','0.0.0'), '1.9.9') > 0  && version_compare(get_option('knews_version','0.0.0'), '2.1.3') < 0)) {
	//Earlier versions saves wrong submit ID in stats for cant read, block and mobile click users
	$query = "SELECT DISTINCT (submit_id) FROM " . KNEWS_STATS . " WHERE what=2 OR what=3 OR what=4";
	$bad_ids = $wpdb->get_results($query);
	foreach ($bad_ids as $b_id) {
		$query = "SELECT * FROM " . KNEWS_NEWSLETTERS_SUBMITS . " WHERE newsletter=" . $b_id->submit_id . ' AND blog_id=' . get_current_blog_id();
		$submits = $wpdb->get_results($query);
		if (count($submits) == 1) {
			$query = "UPDATE " . KNEWS_STATS . " SET submit_id=" . $submits[0]->id . " WHERE (what=2 OR what=3 OR what=4) AND submit_id=" . $b_id->submit_id;
			$result = $wpdb->query($query);
		} else if (count($submits) > 1) {
			$query = "UPDATE " . KNEWS_STATS . " SET submit_id=0 WHERE (what=2 OR what=3 OR what=4) AND submit_id=" . $b_id->submit_id;
			$result = $wpdb->query($query);
		}
	}
}

if (version_compare(get_option('knews_version','0.0.0'), '1.6.0') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.2.0') < 0)) {

	if (!knews_add_column(KNEWS_AUTOMATED, 'what_is', "varchar(20) NOT NULL DEFAULT 'autocreate'")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'event', "varchar(20) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'delay', "int(11) NOT NULL DEFAULT 0")) return;
	if (!knews_add_column(KNEWS_AUTOMATED, 'delay_unit', "varchar(20) NOT NULL DEFAULT ''")) return;
	if (!knews_add_column(KNEWS_NEWSLETTERS, 'newstype', "varchar(20) NOT NULL DEFAULT 'unknown'")) return;
	
	$sql =	"CREATE TABLE " .KNEWS_USERS_EVENTS . " (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			event varchar(100) NOT NULL,
			triggered datetime NOT NULL,
			UNIQUE KEY id (id)
		   )$charset_collate;";
		   
	dbDelta($sql);
}

if (version_compare(get_option('knews_version','0.0.0'), '1.6.4') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.2.6') < 0)) {
	if (!knews_add_column(KNEWS_LISTS, 'auxiliary', "int(1) UNSIGNED NOT NULL DEFAULT '0'")) return;
}

if (version_compare(get_option('knews_version','0.0.0'), '1.7.7') < 0 || ( $this->im_pro() && version_compare(get_option('knews_version','0.0.0'), '2.3.8') < 0)) {
	
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

	if ($knewsOptions['newsletter'] != 'no')	$dismissed[] = 'knews_newsletter';
	if ($knewsOptions['config_knews'] != 'no')	$dismissed[] = 'knews_config_knews';
	if ($knewsOptions['videotutorial'] != 'no')	$dismissed[] = 'knews_videotutorial';

	update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', implode(',',$dismissed) );
}
update_option('knews_version', KNEWS_VERSION);
update_option('knews_advice_time', 0);
$this->knews_admin_messages = sprintf("Knews updated the database successfully. Welcome to %s version.", KNEWS_VERSION);


function knews_update_sql($sql) {
	global $wpdb;
	if (!$wpdb->query($sql)) {
		echo '<div style="background-color:#FFEBE8; border:#CC0000 1px solid; color:#555555; border-radius:3px; padding:5px 10px; margin:20px 15px 10px 0; text-align:left">';
		_e('Knews cant update. Please, check user database permisions. (You must allow ALTER TABLE).','knews');
		echo ' (ver. ' . get_option('knews_version','0.0.0') . ')' . ' ' . $wpdb->last_error . ' sql: ' . $sql;
		echo '</div>';
		return false;
	}
	return true;
}

function knews_add_column($table, $field, $typefield) {
	global $wpdb;
	
	if (strcasecmp($wpdb->get_var("show columns from $table like '$field'"), $field) == 0) return true;

	$sql = "ALTER TABLE " . $table . " ADD COLUMN " . $field . " " . $typefield;
	
	if (!$wpdb->query($sql)) {
		echo '<div style="background-color:#FFEBE8; border:#CC0000 1px solid; color:#555555; border-radius:3px; padding:5px 10px; margin:20px 15px 10px 0; text-align:left">';
		_e('Knews cant update. Please, check user database permisions. (You must allow ALTER TABLE).','knews');
		echo ' (ver. ' . get_option('knews_version','0.0.0') . ')' . ' ' . $wpdb->last_error . ' sql: ' . $sql;
		echo '</div>';
		return false;
	}
	return true;
}
?>