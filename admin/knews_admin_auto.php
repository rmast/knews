<?php
//Security for CSRF attacks
$knews_nonce_action='kn-adm-auto';
$knews_nonce_name='_autokn';
if (!empty($_POST)) $w=check_admin_referer($knews_nonce_action, $knews_nonce_name);
//End Security for CSRF attacks

	global $Knews_plugin, $wpdb, $knewsOptions;
	require_once( KNEWS_DIR . '/includes/knews_util.php');

	$pending=false;

	$languages = $Knews_plugin->getLangs(true);
	
	if ($Knews_plugin->get_safe('da')=='delete') {
		$query="DELETE FROM " . KNEWS_AUTOMATED . " WHERE id=" . $Knews_plugin->get_safe('idauto', 0, 'int');
		$results = $wpdb->query( $query );
		echo '<div class="updated"><p>' . __('Automated process deleted','knews') . '</p></div>';
	}

	if ($Knews_plugin->get_safe('activated')==1 || $Knews_plugin->get_safe('activated',2)==0) {
		$query = "UPDATE ".KNEWS_AUTOMATED." SET paused=" . $Knews_plugin->get_safe('activated') . " WHERE id=" . $Knews_plugin->get_safe('idauto', 0, 'int');
		$result=$wpdb->query( $query );
		echo '<div class="updated"><p>' . (($Knews_plugin->get_safe('activated')!=1) ? __('Automated process activated','knews') : __('Automated process deactivated','knews')) . '</p></div>';
	}

	if ($Knews_plugin->get_safe('auto')==1 || $Knews_plugin->get_safe('auto',2)==0) {
		$query = "UPDATE ".KNEWS_AUTOMATED." SET auto=" . $Knews_plugin->get_safe('auto', 0, 'int') . " WHERE id=" . $Knews_plugin->get_safe('idauto', 0, 'int');
		$result=$wpdb->query( $query );
		echo '<div class="updated"><p>' . (($Knews_plugin->get_safe('auto')==1) ? __('Automated submit activated','knews') : __('Manual submit activated','knews')) . '</p></div>';
	}

	if ($Knews_plugin->post_safe('action')=='add_autoresponder' || $Knews_plugin->post_safe('action')=='add_auto' || $Knews_plugin->post_safe('action')=='edit_auto' || $Knews_plugin->post_safe('action')=='edit_autoresponder') {

		$name = $Knews_plugin->post_safe('auto_name');
		$lang = $Knews_plugin->post_safe('auto_lang');
		$news = $Knews_plugin->post_safe('auto_newsletter');
		$target = $Knews_plugin->post_safe('auto_target');
		$paused = $Knews_plugin->post_safe('auto_paused', 0, 'int');
		$auto = $Knews_plugin->post_safe('auto_auto', 0, 'int');
		$mode = $Knews_plugin->post_safe('auto_mode', 0, 'int');
		$posts = $Knews_plugin->post_safe('auto_posts', 0, 'int');
		$time = $Knews_plugin->post_safe('auto_time', 0, 'int');
		$day = $Knews_plugin->post_safe('auto_dayweek', 0, 'int');
		$at_once = $Knews_plugin->post_safe('emails_at_once', 50, 'int');
		$id_smtp = $Knews_plugin->post_safe('knews_select_smtp', 1, 'int');
		
		$event = $Knews_plugin->post_safe('auto_event');
		$delay = $Knews_plugin->post_safe('auto_delay', 0, 'int');
		$delay_unit = $Knews_plugin->post_safe('auto_delay_unit');	

		$type = 'autocreate'; if ($Knews_plugin->post_safe('action')=='add_autoresponder' || $Knews_plugin->post_safe('action')=='edit_autoresponder') $type = 'autoresponder';
		
		$knews_cpt='';
		$knews_categories='';
		$knews_exclude_categories='';
		$knews_tags='';
		$knews_exclude_tags='';
		$knews_ignore_post_opt = 0;
		$use_post_embed_pref = 1;

		$submit_time = $Knews_plugin->post_safe('auto_submit_time', '00:00');	

		if ($name =='' || $news=='' || $target=='') {
			
			echo '<div class="error"><p><strong>';
			if ($name=='') {
				_e('Error: the name cant be empty','knews');
			} else {
				_e('Error: Please, fill all the form','knews');
			}
			echo '</strong></p></div>';

		} else {
			$query = "SELECT * FROM " . KNEWS_AUTOMATED . " WHERE name='" . $name . "'";
			if ($Knews_plugin->post_safe('action')=='edit_auto' || $Knews_plugin->post_safe('action')=='edit_autoresponder') $query .= ' AND id <> ' . $Knews_plugin->post_safe('edit_auto_id', 0, 'int');

			$results = $wpdb->get_results( $query );
			
			if (count($results)==0) {
				if ($Knews_plugin->post_safe('action')=='edit_auto' || $Knews_plugin->post_safe('action')=='edit_autoresponder') { 
 
					$sql = "UPDATE " . KNEWS_AUTOMATED . " SET name = '" . $name . "', selection_method = 1, target_id = " . $target . ", newsletter_id = " . $news . ", lang = '" . $lang . "', paused = " . $paused . ", auto = " . $auto . ", every_mode = " . $mode . ", every_time = " . $time . ", what_dayweek =  " . $day . ", every_posts = " . $posts . ", emails_at_once = " . $at_once . ", id_smtp = " . $id_smtp . ", what_is = '" . $type . "', event = '" . $event . "', delay = " . $delay . ", delay_unit = '" . $delay_unit . "', include_cats = '" . $knews_categories . "', exclude_cats = '" . $knews_exclude_categories . "', include_tags = '" . $knews_tags . "', exclude_tags = '" . $knews_exclude_tags . "', include_postypes = '" . $knews_cpt . "', use_post_embed_pref = " . $use_post_embed_pref . ", submit_time = '" . $submit_time . "' WHERE id = " . $Knews_plugin->post_safe('edit_auto_id', 0, 'int');

					if ($wpdb->query($sql)) {
						echo '<div class="updated"><p>' . __('Automated submit modified successfully','knews') . '</p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __('Error','knews') . ':</strong> ' . __("Cant modify the automated submit",'knews') . ' : ' . $wpdb->last_error . '</p></div>';
					}
	
				} else {
					$sql = "INSERT INTO " . KNEWS_AUTOMATED . " (name, selection_method, target_id, newsletter_id, lang, paused, auto, every_mode, every_time, what_dayweek, every_posts, last_run, emails_at_once, run_yet, id_smtp, what_is, event, delay, delay_unit, include_cats, exclude_cats, include_tags, exclude_tags, include_postypes, use_post_embed_pref, submit_time) VALUES (";
					$sql .= "'" . $name . "', 1, " . $target . ", " . $news . ", '" . $lang . "', " . $paused . ", " . $auto . ", " . $mode . ", " . $time . ", " . $day . ", " . $posts . ", '" . $Knews_plugin->get_mysql_date() . "', " . $at_once . ", 0, " . $id_smtp . ", '" . $type . "', '" . $event . "', " . $delay . ", '" . $delay_unit . "', '" . $knews_categories . "', '" . $knews_exclude_categories . "', '" . $knews_tags . "', '" . $knews_exclude_tags . "', '" . $knews_cpt . "', " . $use_post_embed_pref . ", '" . $submit_time . "')";
					
					if ($wpdb->query($sql)) {
						echo '<div class="updated"><p>' . __('Automated submit created','knews') . '</p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __('Error','knews') . ':</strong> ' . __("Cant create the automated submit",'knews') . ' : ' . $wpdb->last_error . '</p></div>';
					}
				}
			} else {
				echo '<div class="error"><p><strong>';
				_e('Error: there is already an automated submit with this name','knews');
				echo '</strong></p></div>';
			}
		}
	}

	$results_per_page=10;
	$paged = $Knews_plugin->get_safe('paged', 1, 'int');

	$query = "SELECT * FROM " . KNEWS_NEWSLETTERS . " WHERE automated=0 AND mobile=0 ORDER BY modified DESC";
	$news = $wpdb->get_results( $query );

	$frequency = array ('daily','weekly','every 15 days','monthly','every 2 months','every 3 months');
	$dayname = array ('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
	
	$query = "SELECT id, name FROM " . KNEWS_LISTS . " WHERE auxiliary=0 ORDER BY orderlist";
	$lists_name = $wpdb->get_results( $query );

	if (!$Knews_plugin->im_pro()) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('input[name="knews_get_cpt"], input[name="knews_get_categories"], input[name="knews_get_tags"], input[name="knews_ignore_post_opt"]')
			.click(function(event) {
				location.href='admin.php?page=knews_config&tab=pro';
				return false;
			})
			.change(function() {
				return false;
			});
		});
	
	</script>
<?php
	}
?>
	<link href="<?php echo KNEWS_URL; ?>/admin/styles.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo KNEWS_URL; ?>/admin/scripts.js"></script>
	<script type="text/javascript">
		var knews_cats_tags = new Array();
		knews_cats_tags['cats']=new Array();
		knews_cats_tags['tags']=new Array();
<?php
	$cats=array(); $tags=array();
function knews_select($name_id, $values, $edit_task, $key) {
	echo '<select name="' . $name_id . '" id="' . $name_id . '" autocomplete="off">';
	$n=0;
	foreach ($values as $val => $text) {
		$n++;
		$selected=false; if ($n==1 && !isset($edit_task->$key)) $selected=true;
		if (isset($edit_task->$key) && $edit_task->$key == $val) $selected=true;
		echo '<option value="' . $val . '" ' . ($selected ? 'selected="selected"' : '') . '>' . $text . '</option>';
	}
	echo '</select>';
}
?>
	</script>
	<div class=wrap>
			<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div>
		<h2 class="nav-tab-wrapper">
<?php
if ($Knews_plugin->get_safe('tab')!='autoresponders') {
?>
		<a href="admin.php?page=knews_auto" class="nav-tab nav-tab-active"><?php _e('Autocreators','knews'); ?></a>
		<a href="admin.php?page=knews_auto&tab=autoresponders" class="nav-tab"><?php _e('Autoresponders','knews'); ?></a></h2><br />
			<?php 
			$query = "FROM " . KNEWS_AUTOMATED . " WHERE what_is='autocreate'";

			$filtered_automated = $wpdb->get_results( 'SELECT COUNT(id) AS n ' . $query );
			$filtered_automated = $filtered_automated[0]->n;

			$query .= " ORDER BY paused LIMIT " . $results_per_page . " OFFSET " . $results_per_page * ($paged - 1);

			$results = $wpdb->get_results( 'SELECT * ' . $query );

			if (count($results) != 0) {
			?>
				<form method="post" action="admin.php?page=knews_auto">
				<table class="widefat">
					<thead>
						<tr>
							<th align="left"><?php _e('Automated process name','knews');?></th>
							<th><?php _e('Target','knews');?></th>
							<th><?php _e('Newsletter','knews');?></th>
							<th><?php _e('Language','knews');?></th>
							<th><?php _e('Activated','knews');?></th>
							<th><?php _e('Automatic submit','knews');?></th>
							<th><?php _e('Method','knews');?></th>
							<th><?php _e('Last run','knews');?></th>
							<th><?php _e('Details','knews');?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$alt=true;
					$results_counter=0;
					foreach ($results as $automated) {
						$results_counter++;
						
							echo '<tr' . (($alt) ? ' class="alt"' : '') . '>';
							echo '<td class="name_' . $automated->id  . '"><strong>' . $automated->name . '</strong>';
							
						echo '<div class="row-actions"><span><a title="' . __('Edit the automated task details', 'knews') . '" href="admin.php?page=knews_auto&edit=1&idauto=' . $automated->id . '#editauto">' . __('Edit task', 'knews') . '</a> | </span>';

						echo '<span><a title="' . __('Activate/deactivate the automated task', 'knews') . '" href="admin.php?page=knews_auto&activated=' . (($automated->paused==1) ? '0' : '1') . '&idauto=' . $automated->id . '">' . (($automated->paused==1) ? __('Activate', 'knews') : __('Deactivate', 'knews')) . '</a> | </span>';

						echo '<span><a title="' . __('Automatic/Manual submits of auto-created newsletters', 'knews') . '" href="admin.php?page=knews_auto&auto=' . (($automated->auto==1) ? '0' : '1') . '&idauto=' . $automated->id . '">' . (($automated->auto==1) ? __('Manual submit', 'knews') : __('Submit automatic', 'knews')) . '</a> | </span>';

						echo '<span class="trash"><a href="admin.php?page=knews_auto&da=delete&idauto=' . $automated->id . '" title="' . __('Delete definitively this automated task', 'knews') . '" class="submitdelete">' . __('Delete', 'knews') . '</a></span></div>';

						echo '</td><td>';
						foreach ($lists_name as $ln) {
							if ($ln->id==$automated->target_id) {
								echo $ln->name;
								break;
							}
						}
						echo '</td>';
						echo '<td>';
						foreach ($news as $n) {
							if ($n->id==$automated->newsletter_id) {
								echo $n->name;
								break;
							}
						}
						echo '</td>';
						echo '<td>' . $automated->lang . '</td>';
						echo '<td>' . (($automated->paused==1) ? '<span style="color:#f00">' . __('Off', 'knews') . '</span>' : __('On', 'knews')) . '</td>';
						if ($automated->paused!=1) $pending=true;
						echo '<td>' . (($automated->auto==1) ? __('Automated submit', 'knews') : __('Manual submit', 'knews')) . '</td>';
						echo '<td>';
						if ($automated->every_mode ==1) {
							printf( 'every %s posts', $automated->every_posts);
						} else {
							echo $frequency[$automated->every_time - 1];
							if ($automated->every_time > 1) echo ' on ' . $dayname[$automated->what_dayweek-1];
						}
						echo '</td>';
						echo '<td>' . (($automated->run_yet==0) ? __('NEVER','knews') : $automated->last_run) . '</td>';
						echo '<td><a href="#" class="knews_details knews_alert_click" title="';
						if ($automated->include_postypes != '') echo 'Only this post types: ' . $automated->include_postypes . "\r\n";
						if ($automated->include_cats != '') echo 'Only from this categories: ' . knews_list_items($automated->include_cats, $all_cats) . "\r\n";
						if ($automated->exclude_cats != '') echo 'Exclude this categories: ' . knews_list_items($automated->exclude_cats, $all_cats) . "\r\n";
						if ($automated->include_cats == '' && $automated->exclude_cats == '') echo 'All categories will be included' . "\r\n";
						if ($automated->include_tags != '') echo 'Only from this tags: ' . knews_list_items($automated->include_tags, $all_tags) . "\r\n";
						if ($automated->exclude_tags != '') echo 'Exclude this tags: ' . knews_list_items($automated->exclude_tags, $all_tags) . "\r\n";
						if ($automated->include_tags == '' && $automated->exclude_tags == '') echo 'All tags will be included' . "\r\n";
						echo 'Ignore the Knews Automation post setting: ' . (($automated->use_post_embed_pref == 0) ? 'Yes' : 'No') . "\r\n";
						echo $automated->emails_at_once * 6 . ' emails per hour sent' . "\r\n";
						echo 'Submit method: ' . (($automated->auto==1) ? 'Automated' : 'Manual') . "\r\n";
						echo 'The submission will start at: ' . $automated->submit_time . "\r\n";
						if (isset($all_smtp[$automated->id_smtp])) echo 'It will be sent from: ' . $all_smtp[$automated->id_smtp]['from_mail_knews'];
						echo '">+</a></td></tr>';
						
						$alt=!$alt;
					}
					?>
					</tbody>
					<tfoot>
						<tr>
							<th align="left"><?php _e('Automated process name','knews');?></th>
							<th><?php _e('Target','knews');?></th>
							<th><?php _e('Newsletter','knews');?></th>
							<th><?php _e('Language','knews');?></th>
							<th><?php _e('Activated','knews');?></th>
							<th><?php _e('Automatic submit','knews');?></th>
							<th><?php _e('Method','knews');?></th>
							<th><?php _e('Last run','knews');?></th>
							<th><?php _e('Details','knews');?></th>
						</tr>
					</tfoot>
				</table>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
				</form>
				<?php
				//Pagination
				echo '<div class="tablenav bottom">';
				knews_pagination($paged, ceil($filtered_automated/ $results_per_page), $filtered_automated);
				echo '</div>';

				if ($pending) {
				?>
				<div class="updated">
					<p><?php _e('Knews runs every hour the automated newsletter creation jobs.','knews'); ?></p>
					<p><?php echo sprintf(__('You can manually trigger this task now (only recommended for testing purposes) %s Run Automated Creation Now','knews'), '<a href="' . get_admin_url() . 'admin-ajax.php?action=knewsForceAutomated&manual=1" class="button" target="_blank">'); ?></a></p>
				</div>
				<?php
				}
			} else {
			?>
				<p><?php _e('At the moment there is no automated task, you can create new ones','knews'); ?></p>
			<?php
			}
			?><p>&nbsp;</p>
			<hr />
			<?php
			$edit_task = new stdClass();
			if ($Knews_plugin->get_safe('edit')=='1' && $Knews_plugin->get_safe('idauto') != '') {
				foreach ($results as $result) {
					if ($result->id == $Knews_plugin->get_safe('idauto')) {
						$edit_task = $result;
						break;
					}
				}
				if (isset($edit_task)) {
				?>
					<a id="editauto"></a>
					<h2><?php _e('Editing Auto-creation Process:','knews'); echo ' ' . $edit_task->name; ?></h2>
				<?php
				} else {
				?>
					<a id="editauto"></a>
					<h2><?php _e('New Auto-creation Process','knews');?> <a href="<?php _e('http://www.knewsplugin.com/automated-newsletter-creation/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Auto-create Newsletters Tutorial','knews'); ?></a></h2>
					<div class="notice"><?php _e('Selected task not found.','knews'); ?></div>
				<?php
				}
			} else {
			?>
				<a id="newauto"></a>
				<h2><?php _e('New Auto-creation Process','knews');?> <a href="<?php _e('http://www.knewsplugin.com/automated-newsletter-creation/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Auto-create Newsletters Tutorial','knews'); ?></a></h2>
			<?php
			}
			?>
			<form method="post" action="admin.php?page=knews_auto" id="create_auto">
				<?php 
				if (isset($edit_task->id)) {
				?>
				<input type="hidden" name="action" id="action" value="edit_auto" />
				<input type="hidden" name="edit_auto_id" id="action" value="<?php echo $edit_task->id; ?>" />
				<?php } else { ?>
				<input type="hidden" name="action" id="action" value="add_auto" />
				<?php } ?>
				<h3><?php _e('General options','knews'); ?></h3>
				<p><label for="auto_name"><?php _e('Automated process name:','knews');?> </label><input type="text" name="auto_name" id="auto_name" class="regular-text" value="<?php if (isset($edit_task->name)) echo $edit_task->name; ?>" /></p> 
				<p><label for="auto_paused"><?php _e('Active process:','knews');?></label> <?php knews_select('auto_paused', array('0' => __('On','knews'), '1' => __('Off','knews')), $edit_task, 'paused'); ?></p>
				<h3><?php _e('Newsletter creation options','knews');?></h3>
				<?php
				$lang_listed = false;
				
				if (count($languages) > 1) {
					
					echo '<p><label for="auto_lang">' . __('Get posts from language:','knews') . '</label> ';
					$langs=array();
					foreach($languages as $l){
						if (!isset($edit_task->id) && $l['active']) $edit_task->lang = $l['language_code'];
						$langs[$l['language_code']] = $l['translated_name'];
					}
					knews_select('auto_lang', $langs, $edit_task, 'lang'); 
					echo '</p>';
		
				} else if (count($languages) == 1) {
					foreach ($languages as $l) {
						echo '<input type="hidden" name="auto_lang" id="auto_lang" value="' . $l['language_code'] . '" />';
					}
				} else {
					echo  '<p>' . __('Error','knews') . ": " . __('Language not detected!','knews') . '</p>';
				}
				?>
				<p><label for="auto_newsletter"><?php _e('Use as template:','knews');?></label> 
				<?php
				$disponible_news=array();
				foreach ($news as $n) {
					if (strrpos($n->html_mailing, '%the_title') !== false || strrpos($n->html_mailing, '%the_excerpt') !== false || strrpos($n->html_mailing, '%the_content') !== false && ($n->newstype == 'unknown' || $n->newstype == 'autocreation')) {
						$disponible_news[]=$n;
					}
				}
				if (count($disponible_news) != 0) {
					$disp_news=array();
					foreach ($disponible_news as $n) {
						$disp_news[$n->id] = $n->name . ' (' . $n->lang . ')';
					}
					if (!isset($edit_task->id) && $Knews_plugin->get_safe('id') != '') $edit_task->newsletter_id = $Knews_plugin->get_safe('id');
					knews_select('auto_newsletter', $disp_news, $edit_task, 'newsletter_id');
					echo '</p>';
				} else {
					echo '<span style="color:#f00">';
					echo __('You must first create a newsletter with insertable info (leave the %the_content%, %the_title% etc.)','knews') . '</p>';
					echo '</span>';
				}
				
				$f_auto_mode = 1; if (isset($edit_task->every_mode)) $f_auto_mode = $edit_task->every_mode;
				?>
				<p><input type="radio" name="auto_mode" autocomplete="off" value="1" <?php if ($f_auto_mode=='1') echo 'checked="checked"'; ?> /><?php printf(__('Create a newsletter every %s posts','knews'), '<input type="text" name="auto_posts" id="auto_posts" value="' . (isset($edit_task->every_posts) ? $edit_task->every_posts : '5') . '" style="text-align:right; width:30px;" />');?></p>
				<p><input type="radio" name="auto_mode" autocomplete="off" value="2" <?php if ($f_auto_mode=='2') echo 'checked="checked"'; ?> /> <?php _e('Create a newsletter every x amount of time','knews');?> 
				<?php /*<span id="auto_mode_1">Every <input type="text" name="auto_posts" id="auto_posts" value="5" style="width:30px;" /> posts</span>*/?>
				<span id="auto_mode_2" style="display:none;"><label for="auto_time"><?php _e('Submit','knews'); ?></label> 
				<?php
				$f=0; $freq = array();
				foreach ($frequency as $fre) {
					$f++;
					$freq[$f] = $fre;
				}
				knews_select('auto_time', $freq, $edit_task, 'every_time');
				?>
				<span id="dayweek">, <label for="auto_dayweek">on</label> 
				<?php
				$d=0; $days = array();
				foreach ($dayname as $day_name) {
					$d++;
					$days[$d] = $day_name;
				}
				knews_select('auto_dayweek', $days, $edit_task, 'what_dayweek');
				?>
				</span></p>
				<h3><?php _e('Post selection advanced criteria','knews'); ?></h3>
				<?php 
				$howmany=0;
				$post_types = array('name' => 'post', 'label' => 'post');
				if ($Knews_plugin->im_pro()) $post_types = $Knews_plugin->getCustomPostTypes();
				foreach ($post_types as $pt) {
					$howmany++;

					if ($howmany == 1) {
						$cpt_checked = false;
						if (isset($edit_task->id) && isset($edit_task->include_postypes) && $edit_task->include_postypes != '') $cpt_checked=true;
						?>
						<p><input type="checkbox" name="knews_get_cpt" value="1" <?php if ($cpt_checked) echo 'checked="checked"'; ?> autocomplete="off" /> <?php _e('Get only from this Custom Post Types:','knews'); ?></p>
						<?php
						echo '<div class="knews_hidden_cpt"' . ($cpt_checked ? ' style="display:block" ' : '') . '>';
						echo '<select size="6" multiple="multiple" name="knews_cpt[]">';

					}

					$selected=false; 
					if (isset($edit_task->id) && isset($edit_task->include_postypes) && in_array($pt['name'], explode(',', $edit_task->include_postypes)) ) $selected=true;

					echo '<option value="' . $pt['name'] . '" ' . ($selected ? 'selected="selected"' : '') . '> ' . $pt['label'] . '</option>';
				}
				if ($howmany != 0) echo '</select></div>';

				//$cats = get_categories(array('hide_empty' => 0));
				if (count($cats) > 0 || !$Knews_plugin->im_pro()) {
				?>
				<div class="knews_radios_cat" <?php if ($cpt_checked) echo ' style="display:none"'; ?>>
					<?php
					$f_cats = 'all'; $cats_to_select = array(); 
					if (isset($edit_task->id) && isset($edit_task->include_cats) && $edit_task->include_cats != '') {
						$f_cats = 'include';
						$cats_to_select = explode(',', $edit_task->include_cats);
					} elseif (isset($edit_task->id) && isset($edit_task->exclude_cats) && $edit_task->exclude_cats != '') {
						$f_cats = 'exclude';
						$cats_to_select = explode(',', $edit_task->exclude_cats);						
					}
					?>
					<p>
					<input type="radio" name="knews_get_categories" value="all" checked="checked" autocomplete="off" /> Get from all categories<br />
					<input type="radio" name="knews_get_categories" value="include" autocomplete="off" /> Get from this categories:<br />
					<input type="radio" name="knews_get_categories" value="exclude" autocomplete="off" /> Exclude this categories:
					</p>
					<div class="knews_hidden_cat" <?php if ($f_cats != 'all') echo ' style="display:block"'; ?>>
						<select size="6" multiple="multiple" name="knews_categories[]">
						<?php
						foreach ($cats as $c) {

							$selected=false; 
							if (in_array($c->term_id, $cats_to_select) ) $selected=true;
							echo '<option value="' . $c->term_id . '" ' . ($selected ? 'selected="selected"' : '') . '>' . $c->name . '</option>';
						}
						?>
						</select>
					</div>
				</div>
				<?php
				}
				//$tags = get_tags(array('hide_empty' => 0));
				if (count($tags) > 0 || !$Knews_plugin->im_pro()) {
				?>
				<div class="knews_radios_tags" <?php if ($cpt_checked) echo ' style="display:none"'; ?>>
					<?php
					$f_tags = 'all'; $tags_to_select = array(); 
					if (isset($edit_task->id) && isset($edit_task->include_tags) && $edit_task->include_tags != '') {
						$f_tags = 'include';
						$tags_to_select = explode(',', $edit_task->include_tags);
					} elseif (isset($edit_task->id) && isset($edit_task->exclude_tags) && $edit_task->exclude_tags != '') {
						$f_tags = 'exclude';
						$tags_to_select = explode(',', $edit_task->exclude_tags);						
					}
					?>
					<p>
					<input type="radio" name="knews_get_tags" value="all" <?php if ($f_cats=='all') echo 'checked="checked"'; ?> autocomplete="off" /> <?php _e('Get from all tags', 'knews'); ?><br />
					<input type="radio" name="knews_get_tags" value="include" <?php if ($f_cats=='include') echo 'checked="checked"'; ?> autocomplete="off" /> <?php _e('Get from this tags:', 'knews'); ?><br />
					<input type="radio" name="knews_get_tags" value="exclude" <?php if ($f_cats=='exclude') echo 'checked="checked"'; ?> autocomplete="off" /> <?php _e('Exclude this tags:', 'knews');?>
					</p>
					<div class="knews_hidden_tags" <?php if ($f_tags != 'all') echo ' style="display:block"'; ?>>
						<select size="6" multiple="multiple" name="knews_tags[]">
						<?php
						foreach ($tags as $t) {
							$selected=false; 
							if (in_array($t->term_id, $tags_to_select) ) $selected=true;
							echo '<option value="' . $t->term_id . '" ' . ($selected ? 'selected="selected"' : '') . '>' . $t->name . '</option>';
						}
						?>
						</select>
					</div>
				</div>
				<?php
				}

				$checked = false;
				if (isset($edit_task->id) && isset($edit_task->use_post_embed_pref) && $edit_task->use_post_embed_pref == '0') $checked=true;
				?>
				<p><input type="checkbox" name="knews_ignore_post_opt" value="1" <?php if ($checked) echo 'checked="checked"'; ?> autocomplete="off" /> <?php _e('Ignore the Knews Automation post setting (posts will be embeded if the above conditions are met)', 'knews'); ?></p>
				<h3><?php _e('Newsletter submit options','knews'); ?></h3>
				<?php
				if (count($lists_name) != 0) {
					?>
					<p><label for="auto_target"><?php _e('Target for newsletter:','knews');?></label> 
					<?php
					$t = array();
					foreach ($lists_name as $ln) {
						$t[$ln->id] = $ln->name;
					}
					knews_select('auto_target', $t, $edit_task, 'target_id');
					?>
					</p>
					<?php
				} else {
					echo '<p style="color:#f00">' . __('Error: there are no mailing lists','knews'). '</p>';
				}
				?>
				<p><label for="auto_submit_time"><?php _e('Hour to start the submission:','knews');?></label> <input type="text" name="auto_submit_time" id="auto_submit_time" value="<?php echo (isset($edit_task->submit_time) ? $edit_task->submit_time : '00:00'); ?>" style="width:60px" /> (<?php printf(__('insert the time into your local time, now is: %s', 'knews'), date('H:i', current_time('timestamp'))); ?>)</p>
				<p><label for="auto_auto"><?php _e('Submit method:','knews');?></label> 
				<?php
				knews_select('auto_auto', array('0' => __('Manual submit','knews'), '1' => __('Automated submit','knews')), $edit_task, 'auto');
				?>				
				</p>
<p><labels for="emails_at_once"><?php _e('E-mails sent at once:','knews');?></label>
				<?php
				$speed = array(
					'2' => '2 ' . __('test mode','knews'),
					'10' => '10',
					'25' => '25',
					'50' => '50 ' . __('(normal)','knews'),
					'100' => '100',
					'250' => '250 ' . __('(high performance SMTP)','knews'),
					'500' => '500 ' . __('(high performance SMTP)','knews'),
				);
				if (defined('KNEWS_CUSTOM_SPEED')) $speed = array_merge(array(KNEWS_CUSTOM_SPEED => KNEWS_CUSTOM_SPEED), $speed);
				if (!isset($edit_task->id) && defined('KNEWS_CUSTOM_SPEED')) $edit_task->emails_at_once = KNEWS_CUSTOM_SPEED;
				if (!isset($edit_task->id) && !defined('KNEWS_CUSTOM_SPEED')) $edit_task->emails_at_once = 50;

				knews_select('emails_at_once', $speed, $edit_task, 'emails_at_once');
				?> <span class="at_once_preview"><?php echo $edit_task->emails_at_once * 6; ?></span> per hour.</p>
<?php if ($Knews_plugin->im_pro()) {
$smtp_selected = ''; if (isset($edit_task->id) && isset($edit_task->id_smtp)) $smtp_selected=$edit_task->id_smtp;

if ($selector = $Knews_plugin->get_smtp_selector($smtp_selected)) {
	echo '<p><label for="knews_select_smtp">' . __('Use the SMTP: ', 'knews') . '</label> ' . $selector . '</p>';
}
?>
<?php } ?>
				<p><input type="submit" value="<?php 
				if (!isset($edit_task->id)) {
					_e('New Auto-create Newsletters Process','knews');
				} else {
					_e('Update Auto-creation process','knews');
				}
				?>" class="button-primary" /></p>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
			</form>
<?php
} else {
?>
		<a href="admin.php?page=knews_auto" class="nav-tab"><?php _e('Autocreators','knews'); ?></a>
		<a href="admin.php?page=knews_auto&tab=autoresponders" class="nav-tab nav-tab-active"><?php _e('Autoresponders','knews'); ?></a></h2><br />
<?php 
			$query = "FROM " . KNEWS_AUTOMATED . " WHERE what_is='autoresponder'";

			$filtered_automated = $wpdb->get_results( 'SELECT COUNT(id) AS n ' . $query );
			$filtered_automated = $filtered_automated[0]->n;

			$query .= " LIMIT " . $results_per_page . " OFFSET " . $results_per_page * ($paged - 1);

			$results = $wpdb->get_results( 'SELECT * ' . $query );
			$pending=false;
			if (count($results) != 0) {
				$pending=true;
			?>
				<form method="post" action="admin.php?page=knews_auto&tab=autoresponders">
				<table class="widefat">
					<thead>
						<tr>
							<th align="left"><?php _e('autoresponder process name','knews');?></th>
							<th><?php _e('Event','knews');?></th>
							<th><?php _e('Suscribed to','knews');?></th>
							<th><?php _e('User language','knews');?></th>
							<th><?php _e('Newsletter','knews');?></th>
							<th><?php _e('Activated','knews');?></th>
							<th><?php _e('Delay','knews');?></th>
							<th><?php _e('Details','knews');?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$alt=true;
					$results_counter=0;
					foreach ($results as $automated) {
						$results_counter++;

						echo '<tr' . (($alt) ? ' class="alt"' : '') . '>';

						echo '<td class="name_' . $automated->id  . '"><strong>' . $automated->name . '</strong>';
						
						echo '<div class="row-actions"><span><a title="' . __('Edit the automated task details', 'knews') . '" href="admin.php?page=knews_auto&tab=autoresponders&edit=1&idauto=' . $automated->id . '#editauto">' . __('Edit task', 'knews') . '</a> | </span>';

						echo '<span><a title="' . __('Activate/deactivate the automated task', 'knews') . '" href="admin.php?page=knews_auto&activated=' . (($automated->paused==1) ? '0' : '1') . '&tab=autoresponders&idauto=' . $automated->id . '">' . (($automated->paused==1) ? __('Activate', 'knews') : __('Deactivate', 'knews')) . '</a> | </span>';

						echo '<span class="trash"><a href="admin.php?page=knews_auto&tab=autoresponders&da=delete&idauto=' . $automated->id . '" title="' . __('Delete definitively this automated task', 'knews') . '" class="submitdelete">' . __('Delete', 'knews') . '</a></span></div>';

						echo '</td><td>';
						echo $automated->event;
						echo '</td><td>';

						if ($automated->target_id==0) {
							echo __('All','knews');
						} else {
							foreach ($lists_name as $ln) {
								if ($ln->id==$automated->target_id) {
									echo $ln->name;
									break;
								}
							}
						}
						echo '</td>';
						echo '<td>' . (($automated->lang=='') ? 'All' : $automated->lang) . '</td><td>';
						foreach ($news as $n) {
							if ($n->id==$automated->newsletter_id) {
								echo $n->name;
								break;
							}
						}
						echo '</td>';
						echo '<td>' . (($automated->paused==1) ? '<span style="color:#f00">' . __('Off', 'knews') . '</span>' : __('On', 'knews')) . '</td>';
						echo '<td>' . $automated->delay . ' ' . $automated->delay_unit . '</td>';

						echo '<td><a href="#" class="knews_details knews_alert_click" title="';
						echo $automated->emails_at_once * 6 . ' emails per hour sent' . "\r\n";
						if (isset($all_smtp[$automated->id_smtp])) echo 'It will be sent from: ' . $all_smtp[$automated->id_smtp]['from_mail_knews'];
						echo '">+</a></td></tr>';

						$alt=!$alt;
					}
					?>
					</tbody>
					<tfoot>
						<tr>
							<th align="left"><?php _e('autoresponder process name','knews');?></th>
							<th><?php _e('Event','knews');?></th>
							<th><?php _e('Suscribed to','knews');?></th>
							<th><?php _e('User language','knews');?></th>
							<th><?php _e('Newsletter','knews');?></th>
							<th><?php _e('Activated','knews');?></th>
							<th><?php _e('Delay','knews');?></th>
							<th><?php _e('Details','knews');?></th>
						</tr>
					</tfoot>
				</table>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
				</form>
				<?php
				//Pagination
				echo '<div class="tablenav bottom">';
				knews_pagination($paged, ceil($filtered_automated/ $results_per_page), $filtered_automated);
				echo '</div>';

				if ($pending) {
				?>
				<div class="updated">
					<p><?php _e('Knews runs every hour the automated newsletter creation jobs.','knews'); ?></p>
					<p><?php echo sprintf(__('You can manually trigger this task now (only recommended for testing purposes) %s Run Automated Creation Now','knews'), '<a href="' . get_admin_url() . 'admin-ajax.php?action=knewsForceAutomated&manual=1" class="button" target="_blank">'); ?></a></p>
				</div>
				<?php
				}
			} else {
			?>
				<p><?php _e('At the moment there is no autoresponder tasks, you can create new ones','knews'); ?></p>
			<?php
			}
			?><p>&nbsp;</p>
			<hr />
			<?php
			$edit_task = new stdClass();
			if ($Knews_plugin->get_safe('edit')=='1' && $Knews_plugin->get_safe('idauto') != '') {
				foreach ($results as $result) {
					if ($result->id == $Knews_plugin->get_safe('idauto')) {
						$edit_task = $result;
						break;
					}
				}
				if (isset($edit_task)) {
				?>
					<a id="editauto"></a>
					<h2><?php _e('Editing autoresponder process:','knews'); echo ' ' . $edit_task->name; ?> <a href="http://www.knewsplugin.com/knews-have-autoresponders/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Autoresponders Tutorial','knews'); ?></a></h2>
				<?php
				} else {
				?>
					<a id="editauto"></a>
					<h2><?php _e('New autoresponder','knews');?> <a href="http://www.knewsplugin.com/knews-have-autoresponders/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Autoresponders Tutorial','knews'); ?></a></h2>
					<div class="notice"><?php _e('Selected task not found.','knews'); ?></div>
				<?php
				}
			} else {
			?>
				<a id="newauto"></a>
				<h2><?php _e('New autoresponder','knews');?> <a href="http://www.knewsplugin.com/knews-have-autoresponders/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Autoresponders Tutorial','knews'); ?></a></h2>
			<?php
			}
			?>
			<form method="post" action="admin.php?page=knews_auto&tab=autoresponders" id="create_autoresponder">
				<?php 
				if (isset($edit_task->id)) {
				?>
				<input type="hidden" name="action" id="action" value="edit_autoresponder" />
				<input type="hidden" name="edit_auto_id" id="action" value="<?php echo $edit_task->id; ?>" />
				<?php } else { ?>
				<input type="hidden" name="action" id="action" value="add_autoresponder" />
				<?php } ?>
				<p><label for="auto_name"><?php _e('Autoresponder name:','knews');?> </label><input type="text" name="auto_name" id="auto_name" value="<?php if (isset($edit_task->name)) echo $edit_task->name; ?>" class="regular-text" /></p> 

				<p><label for="auto_event"><?php _e('Event:','knews');?></label> 
				<?php knews_select('auto_event', array('not_confirmed' => __('Not confirmed','knews'), 'after_confirmation' => __('After confirmation','knews')), $edit_task, 'event'); ?></p>
				
				<p><label for="auto_delay"><?php _e('Delay:','knews');?> *</label> <input type="text" name="auto_delay" id="auto_delay" value="<?php echo (isset($edit_task->delay)) ? $edit_task->delay : '1'; ?>" style="text-align:right; width:30px;" />

				<?php knews_select('auto_delay_unit', array('minutes' => __('minutes','knews'), 'hours' => __('hours','knews'), 'days' => __('days','knews'), 'weeks' => __('weeks','knews')), $edit_task, 'delay'); ?><br />
				<?php _e ('* Not confirmed autoresponder should have some delay, otherwise the subscribers will recieve the autoresponder without time to confirm','knews'); ?></p>
				
				<p><label for="auto_newsletter"><?php _e('Use as template:','knews');?></label> 
				<?php
				$disponible_news=array();
				foreach ($news as $n) {
					if (strrpos($n->html_mailing, '%the_title') == false && strrpos($n->html_mailing, '%the_excerpt') == false && strrpos($n->html_mailing, '%the_content') == false && ($n->newstype == 'unknown' || $n->newstype == 'autoresponder')) {
						$disponible_news[]=$n;
					}
				}
				if (count($disponible_news) != 0) {
					$disp_news=array();
					foreach ($disponible_news as $n) {
						$disp_news[$n->id] = $n->name . ' (' . $n->lang . ')';
					}
					if (!isset($edit_task->id) && $Knews_plugin->get_safe('id') != '') $edit_task->newsletter_id = $Knews_plugin->get_safe('id');
					knews_select('auto_newsletter', $disp_news, $edit_task, 'newsletter_id');

					echo '</p>';
				} else {
					echo '<span style="color:#f00">' . __('You must first create a newsletter as must shown (remove all %the_content%, %the_title% etc.)','knews') . '</span></p>';
				}
				if (count($lists_name) != 0) {
					?>
					<p><label for="auto_target"><?php _e('Only for users suscribed to:','knews');?></label> 
					<?php
					$lists=array('0' => __('All','knews'));
					foreach ($lists_name as $ln) {
						$lists[$ln->id] = $ln->name;
					}
					knews_select('auto_target', $lists, $edit_task, 'target_id');
					?>
					</p>
					<?php
				} else {
					echo '<p style="color:#f00">' . __('Error: there are no mailing lists','knews'). '</p>';
				}
				?>
				<?php
				if (count($languages) > 1) {
					
					echo '<p><label for="auto_lang">' . __('Only for users who speak the language:','knews') . '</label> ';
					
					$langs=array('' => __('All','knews'));
					foreach ($languages as $lg) {
						$langs[$lg['language_code']] = $lg['translated_name'];
					}
					knews_select('auto_lang', $langs, $edit_task, 'lang');
					
					echo '</p>';
		
				} else if (count($languages) == 1) {
					foreach ($languages as $l) {
						echo '<input type="hidden" name="auto_lang" id="auto_lang" value="' . $l['language_code'] . '" />';
					}
				} else {
					echo  '<p style="color:#f00">' . __('Error','knews') . ": " . __('Language not detected!','knews') . '</p>';
				}
				?>
				</p>
<div id="at_once"><p>
				<?php 
				echo '<label for="emails_at_once">' . __('E-mails sent at once:','knews') . '</label> ';
				$at_once = array(
					'2' => '2 ' . __('test mode','knews'),
					'10' => '10',
					'25' => '25',
					'50' => '50 ' . __('(normal)','knews'),
					'100' => '100',
					'250' => '250 ' . __('(high performance SMTP)','knews'),
					'500' => '500 ' . __('(high performance SMTP)','knews')
				);
				if (defined ('KNEWS_CUSTOM_SPEED')) $at_once[KNEWS_CUSTOM_SPEED] = KNEWS_CUSTOM_SPEED . ' ' . __('(custom speed)','knews');

				if (!isset($edit_task->id)) {
					$edit_task->emails_at_once = defined ('KNEWS_CUSTOM_SPEED') ? KNEWS_CUSTOM_SPEED : 50;
				}

				knews_select('emails_at_once', $at_once, $edit_task, 'emails_at_once');
				?>
<span class="at_once_preview"><?php if (defined('KNEWS_CUSTOM_SPEED')) echo KNEWS_CUSTOM_SPEED * 6; else echo '300'; ?></span> per hour.</p>
</div>
<?php if ($Knews_plugin->im_pro()) {

$smtp_selected = ''; if (isset($edit_task->id) && isset($edit_task->id_smtp)) $smtp_selected=$edit_task->id_smtp;

if ($selector = $Knews_plugin->get_smtp_selector($smtp_selected)) {
	echo '<p><label for="knews_select_smtp">' . __('Use the SMTP: ', 'knews') . '</label> ' . $selector . '</p>';
}
?>
<?php } ?>
				<p><input type="submit" value="<?php 
				if (!isset($edit_task->id)) {
					_e('New autoresponder','knews');
				} else {
					_e('Update autoresponder','knews');
				}
				?>" class="button-primary" /></p>
				<?php 
				//Security for CSRF attacks
				wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
				?>
			</form>
<?php
}
?>
	</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#create_auto input, #create_auto select').change(function() {
			val=jQuery('input[name=auto_mode]:checked', '#create_auto').val();
			//jQuery('span#auto_mode_1, span#auto_mode_2').hide();
			//jQuery('span#auto_mode_' + val).show();
			if (val==2) {
				jQuery('span#auto_mode_2').show();
				if (jQuery('#auto_time').val() != 1) {
					jQuery('#dayweek').show();
				} else {
					jQuery('#dayweek').hide();					
				}
			} else {
				jQuery('span#auto_mode_2').hide();
			}
		});
		jQuery('#auto_auto').change(function() {
			val=jQuery(this).val();
			if (val==0) {
				jQuery('div#at_once').hide();
			} else {
				jQuery('div#at_once').show();
			}
		});
	});
</script>