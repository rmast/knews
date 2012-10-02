<?php

if ($Knews_plugin) {
	if (! $Knews_plugin->initialized) $Knews_plugin->init();

	require_once( KNEWS_DIR . '/includes/knews_util.php');

	global $wpdb;
		
	$query = "SELECT * FROM ".KNEWS_NEWSLETTERS." WHERE id=" . $id_newsletter;
	$results = $wpdb->get_results( $query );

	$theSubject = $results[0]->subject;
	$theHtml = $results[0]->html_head . '<body>' . $results[0]->html_mailing . '</body></html>';

	//Remove some shit from WYSIWYG editor
	$theHtml = str_replace( $results[0]->html_container, '', $theHtml);
	$theHtml = str_replace( '<span class="handler"></span>', '', $theHtml);
	$theHtml = str_replace( "\r\n\r\n", "\r\n", $theHtml);
	$theHtml = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n\n", $theHtml);
	
	$used_tokens = array();
	
	$all_tokens = $Knews_plugin->get_extra_fields();
	
	foreach ($all_tokens as $token) {
		if ($token->token != '') {
			
			preg_match("#\{" . $token->token . "\[([^\]]*)\]\}#", $theHtml, $tokenfound);
			
			if( count($tokenfound) != 0) {
				$used_tokens[] = array('token'=>$token->token, 'id'=>$token->id, 'defaultval'=>$tokenfound[1]);
				$theHtml = str_replace($tokenfound[0], $token->token, $theHtml);
			}
		}
	}
}
?>