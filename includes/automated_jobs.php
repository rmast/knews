<?php
global $knewsOptions, $Knews_plugin, $wpdb, $knews_aj_look_date, $knews_timer, $break_to_avoid_timeout;

$break_to_avoid_timeout=false;
$knews_timer = time();
knews_prevent_timeout();

if ($Knews_plugin) {

	add_filter('posts_where', 'knews_aj_posts_where' );
	
	if (! $Knews_plugin->initialized) $Knews_plugin->init();

	function knews_custom_excerpt_length_fn ($length) {
		
		global $Knews_plugin, $knewsOptions;
		
		$length = $knewsOptions['excerpt_length'];
		$length = apply_filters( 'knews_excerpt_length', $length);

		$template_id = $Knews_plugin->get_safe('tempid', 'unknown');
		return apply_filters( 'knews_excerpt_length_' . $template_id, $length );
	}
	add_filter( 'excerpt_length', 'knews_custom_excerpt_length_fn', 999, 1 );

	if ($knewsOptions['write_logs']=='yes') {
		$hour = date('d-m-Y_H-i', current_time('timestamp'));
		@$fp = fopen(KNEWS_DIR . '/tmp/automated_' . $hour . '.log', 'a');
		$hour = date('d/m/Y H:i', current_time('timestamp'));
		knews_debug('Knews automated jobs script, started at: ' . $hour . "\r\n");
	} else {
		$fp=false;
	}
	
	//Evitem doble execucio
	@$filelock = fopen(KNEWS_DIR . '/tmp/lockfile2.txt', 'x');
	if (!$filelock) {
		//Existeix?
		if (is_file(KNEWS_DIR . '/tmp/lockfile2.txt')) {
			@$filelock = fopen(KNEWS_DIR . '/tmp/lockfile2.txt', 'r');
			$timelock = intval(fread($filelock, filesize(KNEWS_DIR . '/tmp/lockfile2.txt')));
			fclose($filelock);
			$timemax = 3500;
			if( !@set_time_limit(25) ) $timemax = ini_get('max_execution_time') + 120;
			if (intval(time()) - $timelock > $timemax) {
				//Posem la nova data
				knews_debug ('* Previous submit process terminated suddenly, continuing...' . "\r\n");
				@$filelock = fopen(KNEWS_DIR . '/tmp/lockfile2.txt', 'w');
				if (!$filelock) {
					knews_debug( "\r\n" . '* Cant write filelock' . "\r\n");
					if ($fp) fclose($fp);
					die();
				}
				fwrite($filelock, time() );
				fclose($filelock);

			} else {
				knews_debug( "\r\n" . '* Submit process overlapped, terminating this one...' . "\r\n");
				if ($fp) fclose($fp);
				die();
			}
		} else {
			knews_debug("\r\n" . '* Please, make writable the folder wp-content/plugins/knews/tmp. Otherwise overlapped process can occur.' . "\r\n");				
		}
	} else {
		//Escribim fitxer
		fwrite($filelock, time() );
		fclose($filelock);
	}
	
	$mysqldate = $Knews_plugin->get_mysql_date();
	
	$query = "SELECT * FROM " . KNEWS_AUTOMATED . " WHERE paused=0 ORDER BY what_is";
	$automated_jobs = $wpdb->get_results( $query );

	if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') {
		global $sitepress;
		$save_lang = $sitepress->get_current_language();
	}
	
	foreach ($automated_jobs as $aj) {
		knews_debug("\r\n" . 'Automated job (' . $aj->name . ')' . "\r\n");
		$doit=false;
		
		if ($aj->what_is=='autoresponder') {
			
			$delay = array('minutes' => 60, 'hours' => 60*60, 'days' => 60*60*24, 'weeks' => 60*60*24*7);
			$max_date = time() - $delay[$aj->delay_unit] * $aj->delay;
			
			$select = 'SELECT u.id AS real_user_id, u.email, u.joined, u.state';
			$from = ' FROM ' . KNEWS_USERS . ' u';
			$left_join = ' LEFT JOIN (SELECT user_id FROM ' . KNEWS_USERS_EVENTS . ' WHERE event = \'' . $aj->event . '\' ) ue ON u.id = ue.user_id';
			$where = " WHERE ue.user_id IS NULL AND u.joined < '" . $Knews_plugin->get_mysql_date($max_date) . "' AND u.joined >= '" . $aj->last_run . "'";
			
			if ($aj->lang != '') $where .= " AND u.lang='" . $aj->lang . "'";
			
			if ($aj->target_id != 0) {
				$select .= ', upl.*';
				$from .=  ' JOIN ' . KNEWS_USERS_PER_LISTS . ' upl';
				$where .= ' AND upl.id_list = ' . $aj->target_id . ' AND upl.id_user = u.id';
			}
			
			if ($aj->event == 'not_confirmed') $where .= ' AND u.state = 1';
			if ($aj->event == 'after_confirmation') $where .= ' AND u.state = 2';
			
			$query = $select . $from . $left_join . $where . ' LIMIT ' . $aj->emails_at_once;			
			
			$selected_users = $wpdb->get_results( $query );
			
			knews_debug("\r\n" . '- There are ' . count($selected_users) . ' users to send this autoresponder' . "\r\n");
			
			if (count($selected_users) > 0) {
				
				$mysqldate = $Knews_plugin->get_mysql_date();
				
				$query = 'INSERT INTO ' . KNEWS_NEWSLETTERS_SUBMITS . ' (blog_id, newsletter, finished, paused, start_time, users_total, users_ok, users_error, priority, strict_control, emails_at_once, special, end_time, id_smtp) VALUES (' . get_current_blog_id() . ', ' . $aj->newsletter_id . ', 0, 0, \'' . $mysqldate . '\', ' . count($selected_users) . ', 0, 0, 3, \'\', ' . $aj->emails_at_once . ', \'\', \'0000-00-00 00:00:00\', ' . $aj->id_smtp . ')';

				$results = $wpdb->query( $query );
				
				$submit_id = $Knews_plugin->real_insert_id();
	
				foreach ($selected_users as $user) {
					knews_debug("\r\n" . $user->email . "\r\n");
					//$target->id;
					$query = 'INSERT INTO ' . KNEWS_NEWSLETTERS_SUBMITS_DETAILS . ' (submit, user, status) VALUES (' . $submit_id . ', ' . $user->real_user_id . ', 0)';
					$results = $wpdb->query( $query );

					$query = 'INSERT INTO ' . KNEWS_USERS_EVENTS . ' (user_id, event, triggered) VALUES (' . $user->real_user_id . ', \'' . $aj->event . '\', \'' . $mysqldate . '\')';
					$results = $wpdb->query( $query );
				}
				
				//$query = "UPDATE " . KNEWS_AUTOMATED . " SET last_run='" . $Knews_plugin->get_mysql_date() . "', run_yet=1 WHERE id=" . $aj->id . " ";
				$query = "UPDATE " . KNEWS_AUTOMATED . " SET run_yet=1 WHERE id=" . $aj->id . " ";
				$results = $wpdb->query($query);				
			}
			
			
		} elseif ($aj->what_is=='autocreate') {
		
			if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') $sitepress->switch_lang($aj->lang);
			while (!$break_to_avoid_timeout) :
				$doit = false;
				if ($aj->every_mode ==1) {
					$knews_aj_look_date = $aj->last_run;
					$pend_posts = knews_search_posts($aj, $aj->every_posts);
					if (count($pend_posts) == $aj->every_posts) $doit = true;
					knews_debug('- posts to send: ' .count($pend_posts) . "\r\n");
				} else {
					$time_lapsus = time();
					if ($aj->every_time == 1) $time_lapsus = $time_lapsus - 24 * 60 * 60; //Daily
					if ($aj->every_time == 2) $time_lapsus = $time_lapsus - 6 * 24 * 60 * 60; //Weekly
					if ($aj->every_time == 3) $time_lapsus = $time_lapsus - 13 * 24 * 60 * 60; //2 weeks
					if ($aj->every_time == 4) $time_lapsus = $time_lapsus - 30 * 24 * 60 * 60; //Monthly
					if ($aj->every_time == 5) $time_lapsus = $time_lapsus - 60 * 24 * 60 * 60; //2 Monthly
					if ($aj->every_time == 6) $time_lapsus = $time_lapsus - 90 * 24 * 60 * 60; //3 Monthly
					
					if ($Knews_plugin->sql2time($aj->last_run) < $time_lapsus || $aj->run_yet==0 ) {
						if ($aj->every_time == 1) {
							$doit=true;
						} else {
							$daynumber=date('w');
							if ($daynumber==0) $daynumber=7;
							if ($daynumber == $aj->what_dayweek) $doit=true;
							knews_debug('- must submit dayweek number: ' . $aj->what_dayweek . ' and today is: ' . $daynumber . ' dayweek number. ' . "\r\n");
						}
					}
					
					if ($doit) {
						$knews_aj_look_date = $aj->last_run;
						$pend_posts = knews_search_posts($aj, -1);
						knews_debug('- posts to send: ' .count($pend_posts) . "\r\n");
					}
				}
				if ($doit && count($pend_posts) != 0) {
					
					$query="SELECT * FROM " . KNEWS_NEWSLETTERS . " WHERE id=" . $aj->newsletter_id;
					$news = $wpdb->get_results( $query );
					
					$rightnews = false;
					if (count($news) != 0) $rightnews=true;
			
					$Knews_plugin->template_id = $news[0]->template;

					if ($rightnews) {
						knews_debug('- there is a newsletter to build: ' . $news[0]->name . "\r\n");
						require_once (KNEWS_DIR . '/includes/knews_util.php');
	

						if (!$Knews_plugin->im_pro()) $news_id = knews_create_news($aj, $pend_posts, $news, $fp, false, 0);
					} else {
						knews_debug('broken automation, the newsletter was deleted' . "\r\n");
					}
				} else {
					break;
				}
				if ($aj->every_mode !=1 || !$rightnews) break;
				knews_debug('let\'s iterate, maybe more posts wait for news build' . "\r\n");
				
				knews_prevent_timeout();
			endwhile;
		}
		
		knews_prevent_timeout();
		if ($break_to_avoid_timeout) break;
	}
	remove_filter('posts_where', 'knews_aj_posts_where' );
	remove_filter('excerpt_length', 'knews_custom_excerpt_length_fn' );

	if (is_file(KNEWS_DIR . '/tmp/lockfile2.txt')) unlink(KNEWS_DIR . '/tmp/lockfile2.txt');
	if ($fp) fclose($fp);

	if ((KNEWS_MULTILANGUAGE) && $knewsOptions['multilanguage_knews']=='wpml') $sitepress->switch_lang($save_lang);
}

function knews_prevent_timeout() {
	global $break_to_avoid_timeout, $knews_timer;

	if( !@set_time_limit(25) ) {
		if ($knews_timer + ini_get('max_execution_time') - 4 <= time()) {
			$break_to_avoid_timeout=true;
			knews_debug('* Your webserver are run under safe mode, terminating the script to avoid the PHP timeout error...' . "\r\n");
		}
	}
	echo ' ';
}
function knews_debug($message) {
	
	global $fp, $Knews_plugin;
	
	if ($fp) fwrite($fp, $message . "\r\n");
	if ($Knews_plugin->get_safe('manual','0')!=0) echo $message . '<br>' . "\r\n";
	
}

function knews_search_posts($aj, $max) {
	global $wpdb, $Knews_plugin, $knewsOptions, $post;
	
	//$look_posts = get_posts( array('numberposts'=> -1, 'suppress_filters'=>0, 'order'=>'ASC'));
	

	$cpt=array('post');

	if ($Knews_plugin->im_pro()) {
		if ($aj->include_postypes != '') {
			 $cpt = explode(',', $aj->include_postypes);
		} else {
			$post_types = $Knews_plugin->getCustomPostTypes();
			foreach ($post_types as $pt) {
				if ($pt['automate']==1) $cpt[]=$pt['name'];
			}
		}
	}

	$args = array(
		'post_type' => $cpt,
		'posts_per_page' => -1,
		'order' => 'ASC',
		'post_status' => 'publish'
	);
	if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='pll') $args['lang']=$aj->lang;

	$look_posts = new WP_Query($args);
	
	$pend_posts = array();
	while ($look_posts->have_posts()) {
		$look_posts->the_post();
		$query = 'SELECT * FROM ' . KNEWS_AUTOMATED_POSTS . ' WHERE id_automated=' . $aj->id . ' AND id_post=' . $post->ID;
		$result = $wpdb->get_results($query);
		if (count($result) == 0) {
			
			if ($aj->use_post_embed_pref==0) {
				$include_option='1';
			} else {
				$include_option=get_post_meta($post->ID, '_knews_automated', true);
			}
			if ($include_option=='1' || ($include_option=='' && $knewsOptions['def_autom_post']==1)) {

				if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='qt') $post->post_title = get_the_title();
				if ($post->post_title != '') $pend_posts[] = $post;
			}
		}
		if (count($pend_posts) == $max) break;
	}
	return array_reverse($pend_posts);
}

function knews_create_news($aj, $pend_posts, $news, $fp, $mobile, $mobile_news_id) {
	
	global $Knews_plugin, $knewsOptions, $wpdb;
	
	$news_mod = $news[0]->html_mailing;
	
	//Cut the newsletter into modules
	$news_mod = explode('<!--[start module]-->', $news_mod);
	
	$news_mod2=array();
	$first=true;
	foreach ($news_mod as $nm) {
		if ($first) {
			$first=false;
			$news_mod2[]=$nm;
		} else {
			$cut_nm = explode('<!--[end module]-->', $nm);
			$news_mod2[]=$cut_nm[0];
			$news_mod2[]=$cut_nm[1];
		}
	}
	$news_mod_map=array();
	$news_mod_map_duplicated=array();
	$total_posts=0;
	
	foreach ($news_mod2 as $nm) {
		$found=true;
		$n=1;
		while ($found) {
			$found=false;
			if (strpos($nm, '%the_title_' . $n . '%') !== false || strpos($nm, '%the_excerpt_' . $n . '%') !== false || strpos($nm, '%the_permalink_' . $n . '%') !== false || strpos($nm, '%the_content_' . $n . '%') !== false) {
				$found=true; $n++;
			}
		}
		$news_mod_map[]=$n-1;
		$news_mod_map_duplicated[]=false;
		$total_posts=$total_posts+$n-1;
	}
	
	$s=0;
	while ($total_posts < count($pend_posts) && $total_posts !=0) {

		if ($news_mod_map[$s] != 0 && !$news_mod_map_duplicated[$s]) {
			knews_debug('- enlarging the newsletter, have ' . $total_posts . ' and must have ' . count($pend_posts) . "\r\n");
			array_splice( $news_mod2, $s+1, 0, array('</td></tr><tr class="droppable"><td>' . $news_mod2[$s]));
			array_splice( $news_mod_map, $s+1, 0, array($news_mod_map[$s]));
			array_splice( $news_mod_map_duplicated, $s+1, 0, array(true));
			$total_posts=$total_posts+$news_mod_map[$s];
			knews_debug('- module duplicated, news now can contain ' . $total_posts . " posts inside\r\n");
			$s++;
		}
		$s++;
		if ($s >= count($news_mod_map)) $s=0;
	}					
	
	if ($total_posts >= count($pend_posts)) {

		$subject = $news[0]->subject;
		$most_recent=0;
		foreach ($pend_posts as $pp) {

			global $post;
			$post = get_post($pp->ID);

			$permalink = get_permalink();

			//qTranslate support
			if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='qt') {
	
				if (function_exists('qtranxf_convertURL')) {
					$post = qtranxf_use($aj->lang, $post, false);
					$permalink = qtranxf_convertURL($permalink, $aj->lang, true);
	
				} elseif (function_exists('qtrans_convertURL')) {
					$post = qtrans_use($aj->lang, $post, false);
					$permalink = qtrans_convertURL($permalink, $aj->lang, true);
				}
			}
			//End qT
			setup_postdata($post);
	
			$excerpt_length = apply_filters('excerpt_length', 55);
			$excerpt = (string) get_the_excerpt();
			$content = (string) get_the_content();
			$title = get_the_title();

			knews_debug('- including post: ' . $title . "\r\n");
	
			if ($knewsOptions['apply_filters_on']=='1') $content = apply_filters('the_content', $content);
	
			$content = strip_shortcodes($content);
			$content = knews_iterative_extract_code('<script', '</script>', $content, true);
			$content = knews_iterative_extract_code('<fb:like', '</fb:like>', $content, true);
			$content = str_replace(']]>', ']]>', $content);
			$content = strip_tags($content, $knewsOptions['allowed_content_tags']);
	
			if ($excerpt=='') $excerpt = strip_tags($content);
	
			$words = explode(' ', $content, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				//array_push($words, '[...]');
				$excerpt = implode(' ', $words) . '...';
			}
			//$content = nl2br($content);
	
			/*
			$words = explode(' ', $excerpt, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				//array_push($words, '[...]');
				$excerpt = implode(' ', $words) . '...';
			}
			*/

			$data2replace = apply_filters( 'knews_get_post_' . $Knews_plugin->template_id, array('the_title' => $title, 'the_excerpt' => $excerpt, 'the_content' => $content, 'the_permalink' => $permalink), $post->ID );
			
			$s=0;
			while ($news_mod_map[$s]==0 && $s < count($news_mod_map)) { $s++; }
			
			$n=1;
			$found=false;
			while (!$found && $n<20) {
				if (strpos($news_mod2[$s], '%the_title_' . $n . '%') !== false || strpos($news_mod2[$s], '%the_excerpt_' . $n . '%') !== false || strpos($news_mod2[$s], '%the_permalink_' . $n . '%') !== false || strpos($news_mod2[$s], '%the_content_' . $n . '%') !== false) {
					$found=true;
				} else {
					$n++;
				}
			}
			
			if ($found) {
				$news_mod_map[$s]--;
				
				while ($singledata = current($data2replace)) {
					$news_mod2[$s] = str_replace('%' . key($data2replace) . '_' . $n . '%', $singledata, $news_mod2[$s]);
					next($data2replace);
				}
				reset($data2replace);

				/*
				$news_mod2[$s] = str_replace('%the_permalink_' . $n . '%', $permalink, $news_mod2[$s]);
				$news_mod2[$s] = str_replace('%the_title_' . $n . '%', $title, $news_mod2[$s]);
				$news_mod2[$s] = str_replace('%the_excerpt_' . $n . '%', $excerpt, $news_mod2[$s]);
				$news_mod2[$s] = str_replace('%the_content_' . $n . '%', $content, $news_mod2[$s]);
				*/
				if (isset($data2replace['the_title'])) $subject = str_replace('%the_title_1%', $data2replace['the_title'], $subject);
				
				knews_debug('- included: ' . $pp->post_title . "\r\n");
				
				if ($most_recent==0) $most_recent = $Knews_plugin->sql2time($pp->post_modified_gmt);
				if ($most_recent < $Knews_plugin->sql2time($pp->post_modified_gmt)) $most_recent = $Knews_plugin->sql2time($pp->post_modified_gmt);
				$query = "INSERT INTO " . KNEWS_AUTOMATED_POSTS . " (id_automated, id_post, when_scheduled, id_news) VALUES (" . $aj->id . ", ". $pp->ID . ", '" . $Knews_plugin->get_mysql_date() . "', " . "0)";
				$result=$wpdb->query($query);
			}
		}
		
		if ($most_recent != 0) {
			$news_mod='';
			foreach ($news_mod2 as $nm) {
				$news_mod.=$nm;
			}
			
			$news_mod=str_replace('<span class="chooser"></span>', '', $news_mod);
			
			//Netejem exces
			for ($n=1; $n<10; $n++) {
				//$news_mod = str_replace('%the_permalink_' . $n . '%', '#', $news_mod);
				$news_mod = str_replace('%the_title_' . $n . '%', '', $news_mod);
				$news_mod = str_replace('%the_excerpt_' . $n . '%', '', $news_mod);
				$news_mod = str_replace('%the_content_' . $n . '%', '', $news_mod);
				
				$news_mod = knews_iterative_deleteTag('a', '%the_permalink_' . $n . '%', $news_mod);
				$news_mod = knews_iterative_deleteTag('img', 'the_feat_img_' . $n . '%', $news_mod);
			}
			
			knews_debug('- saving the created newsletter' . "\r\n");
			$sql = "INSERT INTO " . KNEWS_NEWSLETTERS . "(name, subject, created, modified, template, html_mailing, html_head, html_bodytag, html_modules, html_container, lang, automated, mobile, id_mobile, newstype) VALUES ('" . esc_sql($news[0]->name) . " (" . $Knews_plugin->localize_date(current_time('timestamp') ) . ")', '" . esc_sql($subject) . "', '" . $Knews_plugin->get_mysql_date() . "', '" . $Knews_plugin->get_mysql_date() . "','" . $news[0]->template . "','" . esc_sql($news_mod) . "','" . esc_sql($news[0]->html_head) . "','" . esc_sql($news[0]->html_bodytag) . "','" . esc_sql($news[0]->html_modules) . "','" . esc_sql($news[0]->html_container) . "', '" . $news[0]->lang . "', 1, " . (($mobile) ? '1' : '0') . ", " . $mobile_news_id . ", 'automated')";
			$results = $wpdb->query($sql);				
			$id_newsletter = $Knews_plugin->real_insert_id();

			$query = "UPDATE " . KNEWS_AUTOMATED_POSTS . " SET id_news=" . $id_newsletter . " WHERE id_news=0";
			$results = $wpdb->query($query);				

			if ($aj->every_mode == 1) {
				$last_run = "'" . $Knews_plugin->get_mysql_date($most_recent) . "'";
			} else {
				$last_run = "'" . $Knews_plugin->get_mysql_date() . "'";
			}
	
			$query = "UPDATE " . KNEWS_AUTOMATED . " SET last_run=" . $last_run . ", run_yet=1 WHERE id=" . $aj->id . " ";
			$results = $wpdb->query($query);				
			
			if ($mobile) return $id_newsletter;
			
			knews_debug('- scheduling the submit' . "\r\n");
			
			$query = "SELECT DISTINCT(" . KNEWS_USERS . ".id) FROM " . KNEWS_USERS . ", " . KNEWS_USERS_PER_LISTS . " WHERE " . KNEWS_USERS . ".id=" . KNEWS_USERS_PER_LISTS . ".id_user AND " . KNEWS_USERS . ".state='2' AND " . KNEWS_USERS_PER_LISTS . ".id_list=" . $aj->target_id;

			$local_time = current_time('timestamp');
			$local_hour = date( 'H', $local_time);
			$local_minute = date( 'i', $local_time);
			$submit_hour = intval(substr($aj->submit_time, 0, 2));
			$submit_minute = intval(substr($aj->submit_time, 3, 2));
			
			if ($submit_hour < $local_hour || ($submit_hour == $local_hour && $submit_minute < $local_minute )) {
				//Today is too late, in localised time, let's wait to tomorrow to send.
				$local_time += 60*60*24;
			}

			$batch_opts = array (
				'minute' => $submit_minute,
				'hour' => $submit_hour,
				'day' => date("d", $local_time),
				'month' => date("m", $local_time),
				'year' => date("Y", $local_time),
				'paused' => (($aj->auto==1) ? 0 : 1),
				'priority' => 4,
				'strict_control' => '',
				'emails_at_once' => $aj->emails_at_once,
				'id_smtp' => $aj->id_smtp,
				'timezone' => 'local'
			);
								
			require( KNEWS_DIR . "/includes/submit_batch.php");

			knews_debug('- all done' . "\r\n");
		}
	} else {
		knews_debug('- the newsletter kept more posts of wich there to submit, wait for more' . "\r\n");
	}				
}
?>