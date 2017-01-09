<?php
//This method is maintained for old Knews submitted newsletters (1.0.x and 1.1.x Knews versions)
if (!function_exists('add_action')) {
	$path='./';
	for ($x=1; $x<6; $x++) {
		$path .= '../';
		if (@file_exists($path . 'wp-config.php')) {
		    require_once($path . "wp-config.php");
			break;
		}
	}
}

global $Knews_plugin, $wpdb;

if ($Knews_plugin) {

	if (! $Knews_plugin->initialized) {
		$Knews_plugin->init($Knews_plugin->get_safe('site_id', 0, 'int'));
	} else {
		if ($Knews_plugin->get_safe('site_id', 0, 'int') != 0) die();
	}

	$mysqldate = $Knews_plugin->get_mysql_date();
	
	if (isset($_GET['img'])) {
		if ($_GET['img']=='testcat') {
			wp_redirect( KNEWS_URL . '/images/testcat.jpg' ); exit;
		}
		if ($_GET['img']=='testled') {
			wp_redirect( KNEWS_URL . '/images/green_led.gif' ); exit;
		}
		$key_user = substr($Knews_plugin->get_safe('img'), 0, strpos($Knews_plugin->get_safe('img'),'_'));
		$key_submit = substr($Knews_plugin->get_safe('img'), -16);
		$what=7;
	} else {
		$key_user = substr($Knews_plugin->get_safe('t'), 0, strpos($Knews_plugin->get_safe('t'),'_'));
		$key_submit = substr($Knews_plugin->get_safe('t'), -16);
		$what=1;
	}
	//echo $key_user . ' ** ' . $key_submit;

	$query = "SELECT * FROM " . KNEWS_KEYS . " WHERE keyy='" . $key_submit . "'";
	$key_track = $wpdb->get_results( $query );

	if (count($key_track) == 1) {

		$user_id = 0;
		$query = "SELECT * FROM " . KNEWS_USERS . " WHERE confkey='" . $key_user . "'";
		$user = $wpdb->get_results( $query );
		
		if (count($user) ==1) $user_id=$user[0]->id;
		
		/**************** REVISAR ******************/
		if ($key_track[0]->type==1 && $what==1 && isset($key_track[0]->id) ) {
			$query = "INSERT INTO " . KNEWS_STATS . " (what, user_id, submit_id, date, statkey) VALUES (1, " . $user_id . ", " . $key_track[0]->submit_id . ", '" . $mysqldate . "', " . $key_track[0]->id . ")";
			$result=$wpdb->query( $query );
		}

		$query = "SELECT * FROM " . KNEWS_STATS . " WHERE what=7 AND user_id='" . $user_id . "' AND submit_id='" . $key_track[0]->submit_id . "'";
		$stat = $wpdb->get_row( $query );
		/**************** REVISAR ******************/
		if (!isset($stat->id) && isset($key_track[0]->id) ) {
			$query = "INSERT INTO " . KNEWS_STATS . " (what, user_id, submit_id, date, statkey) VALUES (7, " . $user_id . ", " . $key_track[0]->submit_id . ", '" . $mysqldate . "', " . $key_track[0]->id . ")";
			$result=$wpdb->query( $query );
		}
		if (isset($key_track[0]->param_href) && $key_track[0]->param_href != '') {
			wp_redirect( $key_track[0]->param_href ); exit;
		} else {
			wp_redirect( $key_track[0]->href ); exit;
		}
		
	} else {
		if ($what==1) echo '<p>Not found</p>';
	}
} else {
	echo '<p>Knews is not active</p>';
}
die();
?>