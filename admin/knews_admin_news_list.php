<?php
//Security for CSRF attacks
$knews_nonce_action='kn-news-list';
$knews_nonce_name='_newslist';
if (!empty($_POST)) $w=check_admin_referer($knews_nonce_action, $knews_nonce_name);
//End Security for CSRF attacks

function duplicate_news($results) {

	global $wpdb, $Knews_plugin;
	
	if ($results) {
		
		$sql = "INSERT INTO " . KNEWS_NEWSLETTERS . "(name, subject, created, modified, template, html_mailing, html_head, html_bodytag, html_modules, html_container, lang, automated, mobile, id_mobile, newstype) VALUES ('(copy)" . esc_sql($results[0]->name) . "', '" . esc_sql($results[0]->subject) . "', '" . $Knews_plugin->get_mysql_date() . "', '" . $Knews_plugin->get_mysql_date() . "','" . $results[0]->template . "','" . esc_sql($results[0]->html_mailing) . "','" . esc_sql($results[0]->html_head) . "','" . esc_sql($results[0]->html_bodytag) . "','" . esc_sql($results[0]->html_modules) . "','" . esc_sql($results[0]->html_container) . "', '" . $results[0]->lang . "', 0, " . $results[0]->mobile . ", " . $results[0]->id_mobile . ", '" . $results[0]->newstype . "')";
			
		$results = $wpdb->query($sql);
		
		if ($results) {

			$news_id = $Knews_plugin->real_insert_id();
			return $news_id;

		}
	}
	return false;
}


	//global $Knews_plugin;
	require_once( KNEWS_DIR . '/includes/knews_util.php');

	$languages = $Knews_plugin->getLangs(true);

	$tab=$Knews_plugin->get_safe('tab');

	if ($Knews_plugin->get_safe('da')=='rename') {
		$query = "UPDATE ".KNEWS_NEWSLETTERS." SET name='" . $Knews_plugin->get_safe('nn') . "' WHERE id=" . $Knews_plugin->get_safe('nid', 0, 'int');
		$result=$wpdb->query( $query );
		echo '<div class="updated"><p>' . __('Newsletter name updated','knews') . '</p></div>';
	}

	if ($Knews_plugin->get_safe('da')=='delete') {
		$query="DELETE FROM " . KNEWS_NEWSLETTERS . " WHERE id=" . $Knews_plugin->get_safe('nid', 0, 'int');
		$results = $wpdb->query( $query );
		echo '<div class="updated"><p>' . __('Newsletter deleted','knews') . '</p></div>';
	}

	if ($Knews_plugin->get_safe('da')=='duplicate') {
		
		$query="SELECT * FROM " . KNEWS_NEWSLETTERS . " WHERE id=" . $Knews_plugin->get_safe('did', 0, 'int');
		$results = $wpdb->get_results( $query );

		if ($new=duplicate_news($results)) {
			echo '<div class="updated"><p>' . __('Newsletter duplicated','knews') . '</p></div>';
		}
	}

	if ($Knews_plugin->post_safe('action')=='delete_news') {
		$query = "SELECT * FROM " . KNEWS_NEWSLETTERS;
		$results = $wpdb->get_results( $query );
		foreach ($results as $list) {
			if ($Knews_plugin->post_safe('batch_' . $list->id)=='1') {
				$query="DELETE FROM " . KNEWS_NEWSLETTERS . " WHERE id=" . $list->id;
				$results=$wpdb->query($query);
			}
		}
		echo '<div class="updated"><p>' . __('Newsletter list updated','knews') . '</p></div>';
	}

?>
<script type="text/javascript" src="<?php echo KNEWS_URL; ?>/admin/scripts.js"></script>

	<div class=wrap>
			<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ($tab=='') echo ' nav-tab-active'; ?>" href="admin.php?page=knews_news"><?php _e('Manual Newsletters','knews');?></a>
				<a class="nav-tab<?php if ($tab=='autocreated') echo ' nav-tab-active'; ?>" href="admin.php?page=knews_news&tab=autocreated"><?php _e('Auto-created Newsletters','knews'); ?></a>
				<a class="nav-tab<?php if ($tab=='automation') echo ' nav-tab-active'; ?>" href="admin.php?page=knews_news&tab=automation"><?php _e('Templates for Automation','knews');?></a>
				<a class="add-new-h2" href="#newnews" onclick="enfocar_knews('#new_news')" title="<?php _e('Create new newsletter','knews'); ?>">+</a>
			</h2>
			<br />
			<?php 
					$paged = $Knews_plugin->get_safe('paged', 1, 'int');
									
						$results_per_page=10;

					$query = " FROM " . KNEWS_NEWSLETTERS . " WHERE mobile=0 AND automated=" . (($tab=='autocreated') ? '1' : '0');
					if ($tab=='') $query .= ' AND (newstype="unknown" OR newstype="manual" OR newstype="")';
					if ($tab=='automation') $query .= ' AND (newstype="autocreation" OR newstype="autoresponder")';
					
					$filtered_lists = $wpdb->get_results( 'SELECT COUNT(id) AS n ' . $query );
					$filtered_lists = $filtered_lists[0]->n;

					$query .= " ORDER BY modified DESC LIMIT " . $results_per_page . " OFFSET " . $results_per_page * ($paged - 1);

					$results = $wpdb->get_results( 'SELECT * ' . $query );
					if (count($results) != 0) {
				?>
					<script type="text/javascript">
					var save_link='';
					var save_id='';
					
					function rename(n) {
						if (save_id != '') rename_cancel();
						save_id = n;
						save_link = jQuery('td.name_' + n).html();
						
						jQuery('td.name_' + n).html('<input type="text" value="' + jQuery('td.name_' + n + ' a').html() + '"><input type="button" value="Rename" class="rename_do"><input type="button" value="Cancel" class="rename_cancel">');
						
						jQuery('td.name_' + n + ' input')[0].focus();

						jQuery('input.rename_cancel').click(function() {
							rename_cancel();
							return false;
						});

						jQuery('input.rename_do').click(function() {
							location.href="admin.php?page=knews_news&da=rename&nid=" + save_id + '&nn=' + encodeURIComponent(jQuery('td.name_' + save_id + ' input').val());
						});

						return false;
					}
					
					function rename_cancel() {
						if (save_id != '') {
							jQuery('td.name_' + save_id).html(save_link);
							save_id='';
						}
					}

					</script>
					<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
					<table class="widefat">
						<thead>
							<tr>
								<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
								<th align="left"><?php _e('Newsletter name','knews');?></th>
								<th><?php _e('Type','knews');?></th>
								<th><?php _e('Created','knews');?></th>
								<th><?php _e('Modified','knews');?></th>
								<th><?php _e('Template','knews');?></th>
								<th><?php _e('Language','knews');?></th>
								<th><?php _e('Stats','knews');?></th>
							</tr>
						</thead>
						<tbody>
				<?php
						$alt=false;
						$results_counter=0;
						foreach ($results as $list) {
							$results_counter++;
							//if ($results_per_page * ($paged-1)<$results_counter) {
								echo '<tr' . (($alt) ? ' class="alt"' : '') . '><th class="check-column"><input type="checkbox" name="batch_' . $list->id . '" value="1"></th>';
								echo '<td class="name_' . $list->id  . '"><strong><a href="admin.php?page=knews_news&section=edit&idnews=' . $list->id . '">' . $list->name . '</a></strong>';
	
								echo '<div class="row-actions"><span><a title="' . __('Edit this newsletter', 'knews') . '" href="admin.php?page=knews_news&section=edit&idnews=' . $list->id . '">' . __('Edit', 'knews') . '</a> | </span>';
								
								echo '<span><a href="#" title="' . __('Rename this newsletter', 'knews') . '" onclick="rename(' . $list->id . '); return false;">' . __('Rename', 'knews') . '</a> | </span>';
								echo '<span><a href="' . get_admin_url() . 'admin-ajax.php?action=knewsReadEmail&id=' . $list->id . '&preview=1" target="_blank" title="' . __('Open a preview in a new window', 'knews') . '">' . __('Preview', 'knews') . '</a> | </span>';

								if ($list->newstype != 'autoresponder' && $list->newstype != 'autocreation') 
								echo '<span><a href="admin.php?page=knews_news&section=send&id=' . $list->id . '" title="' . __('Submit this newsletter', 'knews') . '">' . __('Submit', 'knews') . '</a> | </span>';
	
								echo '<span><a href="admin.php?page=knews_news&da=duplicate&did=' . $list->id . '&tab=' . $tab . '" title="' . __('Duplicate this newsletter', 'knews') . '">' . __('Duplicate', 'knews') . '</a> | </span>';
								echo '<span class="trash"><a href="admin.php?page=knews_news&da=delete&nid=' . $list->id . '&tab=' . $tab . '" title="' . __('Delete definitively this newsletter', 'knews') . '" class="submitdelete">' . __('Delete', 'knews') . '</a></span></div></td>';

								echo '<td>' . ($tab=='autocreated' ? 'autocreated' : $list->newstype) . '</td>';	
								echo '<td>' . $Knews_plugin->humanize_dates($list->created, 'mysql') . '</td>';
								echo '<td>' . $Knews_plugin->humanize_dates($list->modified, 'mysql') . '</td>';
								echo '<td>' . apply_filters('knews_nice_name_' . $list->template, $list->template) . '</td>';
								echo '<td>' . (($list->lang == '') ? __('Multilanguage','knews') : $list->lang) . '</td>';
								//echo '<td><a href="admin.php?page=knews_news&section=edit&idnews=' . $list->id . '">' . __('Edit', 'knews') . '</a></td>';
								//echo '<td align="center"><input type="checkbox" value="1" name="' . $list->id . '_delete" id="' . $list->id . '_delete" /></td>';
								echo '<td>';
								echo '&nbsp;</td>';
								echo '</tr>';
	
								$alt=!$alt;
								//if ($results_counter == $results_per_page * $paged) break;
							//}
						}
				?>
						</tbody>
						<tfoot>
							<tr>
								<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
								<th align="left"><?php _e('Newsletter name','knews');?></th>
								<th><?php _e('Type','knews');?></th>
								<th><?php _e('Created','knews');?></th>
								<th><?php _e('Modified','knews');?></th>
								<th><?php _e('Template','knews');?></th>
								<th><?php _e('Language','knews');?></th>
								<th><?php _e('Stats','knews');?></th>
							</tr>
						</tfoot>
					</table>
					<div class="tablenav bottom">
						<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php _e('Batch actions','knews'); ?></option>
							<option value="delete_news"><?php _e('Delete','knews'); ?></option>
						</select>
						<input type="submit" value="<?php _e('Apply','knews'); ?>" class="button-secondary" />
					</div>
						<?php knews_pagination($paged, ceil($filtered_lists/ $results_per_page), $filtered_lists); ?>
					</div>
					<?php 
					//Security for CSRF attacks
					wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
					?>
					</form>
				<?php
	
					} else {
						if ($tab=='') {
							echo '<p>' . __('At the moment there is no newsletter, you can create new ones','knews') . '</p>';
						} else if ($tab=='automation') {
							echo '<p>' . __('There is no autoresponders or newsletters for automation (maybe are under "manual newsletters" as unknown type).','knews') . '</p>';
						} else {
							echo '<p>' . __('There is no auto-created newsletter.','knews') . '</p>';							
						}
					}
					
					//if ($tab=='' || $tab=='automation') {
				?>
					<br />
					<hr />
					<a id="newnews"></a>
					<h2><?php _e('Create new newsletter','knews');?> <a href="<?php _e('http://www.knewsplugin.com/tutorial/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px;" target="_blank"><?php _e('Make your own templates how-to','knews'); ?></a></h2>
					<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" class="new_newsletter">
						<input type="hidden" name="action" id="action" value="add_news" />
						<p><label for="new_news"><?php _e('Name','knews');?>: </label><input type="text" name="new_news" id="new_news" class="regular-text" />
						<?php
						$lang_listed = false;
						/*foreach ($languages as $l) {
							if ($l['language_code'] == $users[0]->lang) $lang_listed = true;
						}
						*/
						if (KNEWS_MULTILANGUAGE) $languages['multi'] = array ('translated_name'=>  __('Multilanguage','knews'), 'language_code'=>'', 'active'=>0);
						
						if (count($languages) > 1) {
							
							echo '&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' . __('Language','knews') . ': <select name="lang" id="lang">';
							foreach($languages as $l){
								echo '<option value="' . $l['language_code'] . '"' . ( ($l['active']==1) ? ' selected="selected"' : '' ) . '>' . $l['translated_name'] . '</option>';
							}
							echo '</select>';
			
						} else if (count($languages) == 1) {
							foreach ($languages as $l) {
								echo '<input type="hidden" name="lang" id="lang" value="' . $l['language_code'] . '" />';
							}
						} else {
							echo  __('Error','knews') . ": " . __('Language not detected!','knews');
						}
						?>
						</p>
						<p><?php _e('Choose a template','knews');?>: </p>
						<?php
						knews_display_templates(); 
						?>
						<div class="skin_opts" style="clear:both"></div>
						<h3>Type of newsletter</h3>
						<p><input type="radio" name="newstype" value="manual" checked="checked" /> Manual newsletter</p>
						<p><input type="radio" name="newstype" value="autocreation" /> Newsletter for auto-creation <a href="<?php _e('http://www.knewsplugin.com/automated-newsletter-creation/','knews'); ?>" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:13px;" target="_blank"><?php _e('Auto-create Newsletters Tutorial','knews'); ?></a></p>
						<p><input type="radio" name="newstype" value="autoresponder" /> Newsletter for autoresponder <a href="http://www.knewsplugin.com/knews-have-autoresponders/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:13px;" target="_blank"><?php _e('Autoresponders Tutorial','knews'); ?></a></p>
						<div style="clear:both;"></div>
						<div class="submit">
							<input type="submit" value="<?php _e('Add newsletter','knews');?>" class="button-primary" />
						</div>
						<?php
						global $knewsOptions;
						if (!isset($knewsOptions['hide_shop']) || $knewsOptions['hide_shop']=='0') {
						?>
							<div id="knewsshop">
								<?php 
								$look = wp_remote_get( 'http://www.knewsplugin.com/shop/look.php' );
								if (!is_wp_error($look)) {
									if (isset($look['body'])) echo $look['body'];
								} else {
								?>
								<script type="text/javascript">
									if ('https:' == document.location.protocol) {
										starturl = 'https://';
									} else {
										starturl = 'http://www' + '.';
									}
										var knewsscript = document.createElement('script'); knewsscript.type = 'text/javascript'; knewsscript.async = true;
									knewsscript.src = starturl + 'knewsplugin.com/shop/look.js?w=<?php echo urlencode(get_bloginfo('version'));?>&v=<?php echo urlencode(KNEWS_VERSION); ?>&l=<?php echo WPLANG; ?>';
										var knewsscript_s = document.getElementsByTagName('script')[0]; knewsscript_s.parentNode.insertBefore(knewsscript, knewsscript_s);
								</script>
								<?php
								}
								?>
							</div>
						<?php
						}
						?>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery('form.new_newsletter div.template').each(function() {
									folder = jQuery('input:radio', this).val();
									version = jQuery('input#ver_' + folder, this).val();
									version = version.replace(".","_"); 
									jQuery ('#knewsshop div.' + folder + '_' + version).hide();
								});
							});
						</script>
						<?php 
						//Security for CSRF attacks
						wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
						?>
					</form>
	</div>
