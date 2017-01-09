<?php
//Security for CSRF attacks
$knews_nonce_action='kn-save-news';
$knews_nonce_name='_savenews';
//End Security for CSRF attacks
?>
<!--[if lte IE 7]>
<script type="text/javascript">
alert('<?php _e("Warning! IE 6/7 can't edit newsletters! The editor uses HTML5 properties, you need upgrade at least to IE8, or use an modern Firefox, Chrome or Safari.",'knews');?>');
</script>
<![endif]-->

<?php
	$query = "SELECT * FROM ".KNEWS_NEWSLETTERS." WHERE id=" . $id_edit;
	$results_news = $wpdb->get_results( $query );
	if (count($results_news) == 0) {
?>

	<div class=wrap>
		<div class="icon32" style="background:url(<?php echo KNEWS_URL; ?>/images/icon32.png) no-repeat 0 0;"><br></div><h2><?php _e('Newsletters','knews'); ?></h2>
		<h3><?php echo __('Error','knews') . ': ' . __("Newsletter doesn't exists",'knews'); ?></h3>
	</div>
<?php
	} else {
		$parentid=0;
		$title=$results_news[0]->name;
		$subject=$results_news[0]->subject;
		$newstype=$results_news[0]->newstype;
		$template_id=$results_news[0]->template;
		$predefined_colours = apply_filters('knews_predefined_colours_' . $template_id, array() );
?>
<script type="text/javascript">
	url_plugin = '<?php echo KNEWS_URL; ?>';
	news_lang='<?php echo $results_news[0]->lang; ?>';
	droppable_code='<?php echo $results_news[0]->html_container; ?>';
	id_news='<?php echo $Knews_plugin->get_safe('idnews',0,'int');?>';
	newstype='<?php echo $newstype; ?>';
	template_id='<?php echo $template_id; ?>';
	
	<?php
	$one_post = get_posts(array('numberposts' => 1) );
	if (count($one_post)!=1) $one_post = get_pages();
	echo 'one_post_id=' . intval($one_post[0]->ID) . ';';
	?>
	submit_news='<?php echo get_admin_url(); ?>admin.php?page=knews_news&section=send&id=<?php echo (($parentid==0) ? $Knews_plugin->get_safe('idnews',0,'int') : $parentid);?>';
	autocreation_news='<?php echo get_admin_url(); ?>admin.php?page=knews_auto&id=<?php echo (($parentid==0) ? $Knews_plugin->get_safe('idnews',0,'int') : $parentid);?>#newauto';
	autoresponder_news='<?php echo get_admin_url(); ?>admin.php?page=knews_auto&tab=autoresponders&id=<?php echo (($parentid==0) ? $Knews_plugin->get_safe('idnews',0,'int') : $parentid);?>#newauto';

	reload_news='<?php echo get_admin_url(); ?>admin.php?page=knews_news&section=edit&idnews=<?php echo $Knews_plugin->get_safe('idnews',0,'int') ;?>';
	
	must_apply_undo = "<?php echo $Knews_plugin->escape_js(__('You are in image edition mode. You must press Apply or Undo image changes (or press ESC key) before doing anything.','knews')); ?>";
	edit_image= "<?php echo $Knews_plugin->escape_js(__('Edit image','knews')); ?>";
	sharp_image= "<?php echo $Knews_plugin->escape_js(__('Apply changes and refresh image','knews')); ?>";
	undo_image= "<?php echo $Knews_plugin->escape_js(__('Undo image changes','knews')); ?>";
	properties_image= "<?php echo $Knews_plugin->escape_js(__('Properties of image','knews')); ?>";
	post_handler= "<?php echo $Knews_plugin->escape_js(__('Insert post/page content','knews')); ?>";
	free_handler= "<?php echo $Knews_plugin->escape_js(__('Free text content','knews')); ?>";
	move_handler= "<?php echo $Knews_plugin->escape_js(__('Move module','knews')); ?>";
	delete_handler= "<?php echo $Knews_plugin->escape_js(__('Delete module','knews')); ?>";
	unsaved_message= "<?php echo $Knews_plugin->escape_js(__('If you leave now this page, the Newsletter changes will be lost. Please, cancel and press the "Save" button (blue coloured).','knews')); ?>";
	url_admin = "<?php echo get_admin_url(); ?>";
	error_resize = "<?php echo $Knews_plugin->escape_js(__('Error','knews') . ': ' . __('Check the directory permissions for','knews')); ?> '/wp-content/uploads'";
	error_save = "<?php  echo $Knews_plugin->escape_js(__('Error saving','knews')); ?>";
	ok_save = "<?php  echo $Knews_plugin->escape_js(__('Newsletter saved','knews')); ?>";
	button_continue_editing = "<?php echo $Knews_plugin->escape_js(__('Continue editing','knews')); ?>";
	button_submit_newsletter = "<?php  echo $Knews_plugin->escape_js(__('Submit newsletter','knews')); ?>";
	button_create_automation = "<?php  echo $Knews_plugin->escape_js(__('Create task for news autocreation','knews')); ?>";
	button_create_autoresponder = "<?php  echo $Knews_plugin->escape_js(__('Create task for autoresponder','knews')); ?>";

	confirm_delete = "<?php echo $Knews_plugin->escape_js(__('Do you really want to delete this module?','knews')); ?>";
	button_yes = "<?php echo $Knews_plugin->escape_js(__('Yes','knews')); ?>";
	button_no = "<?php echo $Knews_plugin->escape_js(__('No','knews')); ?>";
	button_continue = "<?php echo $Knews_plugin->escape_js(__('Continue','knews')); ?>";
	
	opera_no = "<?php echo $Knews_plugin->escape_js(__("Warning! Opera can't edit newsletters. You must use a modern Firefox, Chrome, Safari or at least Internet Explorer 8.",'knews')); ?>";

	function sorrypro() {
		tb_dialog("Knews","<?php echo $Knews_plugin->escape_js(sprintf(__('Sorry, this is a premium feature. Please, %s click here and see all the Knews Pro features.','knews'), '<a href=\"http://www.knewsplugin.com/knews-free-vs-knews-pro\" target=\"_blank\">')); ?></a>", '', '', '');
	}
</script>
	<div class="wrap">

		<div id="poststuff">
			<div id="titlediv">
			<div id="titlewrap">
				<label for="title" id="title-prompt-text" style="" class="hide-if-no-js"><?php _e('Subject','knews'); ?></label>
				<input type="text" autocomplete="off" id="title" value="<?php echo $subject; ?>" tabindex="1" size="30" name="post_title">
			</div>
			<h2 class="nav-tab-wrapper knews-editor-tab-wrapper"><span class="knews_title"><a href="#" title="<?php _e('Editing newsletter','knews'); ?>:" style="text-decoration:none; color:#000;">&gt;</a> <?php echo $title; ?></span>
			<?php
			if ($parentid==0) {
				echo '<a href="#" class="nav-tab nav-tab-active">' . __('Desktop version','knews') . '</a>';
				if ($results_news[0]->id_mobile == 0) {
					echo '<a href="#" class="nav-tab" onclick="' . (($Knews_plugin->im_pro()) ? 'select_mobile_template()' : 'sorrypro()' ) . '">' . __('+ Add mobile version','knews') . '</a>';
				} else {
					echo '<a href="' . get_admin_url() . 'admin.php?page=knews_news&section=edit&idnews=' . $results_news[0]->id_mobile . '" class="nav-tab knews_save_before">' . __('Mobile version','knews') . '</a>';
				}
			} else {
				echo '<a href="' . get_admin_url() . 'admin.php?page=knews_news&section=edit&idnews=' . $parentid . '" class="nav-tab knews_save_before">' . __('Desktop version','knews') . '</a>';
				echo '<a href="#" class="nav-tab nav-tab-active">' . __('Mobile version','knews') . '</a>';				
			}
			//Type: Manual For AutoCreation For AutoResponder
			?>
			<span id="newstype">Type: <strong><?php echo $newstype; ?></strong> <a href="#">Change</a></span>
			</h2>
				<?php
				$lang_attr='';
				if ($Knews_plugin->get_safe('lang') != '') {
					$lang_attr='&lang=' . $Knews_plugin->get_safe('lang');
				}
				?>
				<div class="wysiwyg_toolbar">
					<?php /*
					<a href="#" class="move" title="move"></a>
					<a href="#" class="minimize" title="minimize"></a>
					<span class="clear"></span>*/?>
					<div class="image_properties">
						<span class="img_handler">
							<?php /*<input type="button" value="Edit image" class="change_image button" />
							<a title="Edit image" class="change_image" href="#"></a>
							<a title="Apply changes and refresh image" class="rredraw_image" href="#"></a>
							<a title="Undo image changes" class="uundo_image" href="#"></a>*/?>
						</span>
						<p><label><?php _e('Image URL:','knews'); ?></label><a href="#" class="change_image"></a><input type="text" name="image_url" id="image_url" readonly /></p>
						<p><label><?php _e('Image link:','knews'); ?> <a href="#" title="<?php _e('Put a url if you want to put link around the image, for example: http://www.mysite.com/mypage.html.','knews'); ?>"><img src="<?php echo KNEWS_URL; ?>/images/help2.gif" width="16" height="16" alt="" /></a></label><input type="text" name="image_link" id="image_link" /></p>
						<p><label><?php _e('Image alt:','knews'); ?> <a href="#" title="<?php _e('The alternate text is very important, because most mail clients block initial image load and show the alternate image text.','knews'); ?>"><img src="<?php echo KNEWS_URL; ?>/images/help2.gif" width="16" height="16" alt="" /></a></label><textarea name="image_alt" id="image_alt"></textarea></p>
						<div class="alignable"><p><?php _e('Image align:','knews'); ?> <select name="image_align" id="image_align"><option value="">none</option>
						<option value="left">left</option>
						<option value="right">right</option>
						<option value="top">top</option>
						<option value="texttop">texttop</option>
						<option value="middle">middle</option>
						<option value="absmiddle">absmiddle</option>
						<option value="baseline">baseline</option>
						<option value="bottom">bottom</option>
						<option value="absbottom">absbottom</option>
						</select></p></div>
						<?php /*<p class="line size"><label>Width:</label><input type="text" name="image_w" id="image_w" /> x <label>Height:</label><input type="text" name="image_h" id="image_h" /></p>*/ ?>
						<p class="line extra" dir="ltr"><label>Border:</label><input type="text" name="image_b" id="image_b" /> <label>Hspace:</label><input type="text" name="image_hs" id="image_hs" /> <label>Vspace:</label><input type="text" name="image_vs" id="image_vs" /></p>
						<span class="clear"></span>
						<p class="buttons">
						<input type="button" value="<?php _e('Apply changes','knews'); ?>" class="rredraw_image button-primary" /><input type="button" value="<?php _e('Undo','knews');?>" class="uundo_image button" /></p>
					</div>
					<div class="tools">
						<?php /*<a href="#" class="toggle_handlers toggle_handlers_off" title="<?php _e('Show/hide handlers','knews'); ?>"></a>*/?>
						<span class="clear"></span>
					</div>
					<div class="resultats_test_pro"></div>
					<div class="save_button">
						<p><a <?php echo ($Knews_plugin->im_pro()) ? ' href="#" onClick="spam_check(); return false;" ' : ' href="admin.php?page=knews_config&tab=pro" target="_blank" '; ?> class="button" ><?php _e('Real Spam Test','knews');?></a> <a href="http://knewsplugin.com/real-spam-test-for-smtp-configuration-and-newsletters/" style="background:url(<?php echo KNEWS_URL; ?>/images/help.png) no-repeat 5px 0; padding:3px 0 3px 30px; color:#0646ff; font-size:15px; vertical-align:middle;" target="_blank" rel="noreferrer" title="About Real Spam Test"></a>
						<a href="#" class="button-primary" onClick="save_news(); return false;" style="float:right;"><?php _e('Save','knews');?></a>
					</div>
					<div class="plegable">
					<?php 
					$query = "SELECT * FROM ".KNEWS_NEWSLETTERS." WHERE id=" . $id_edit;
					$results_news = $wpdb->get_results( $query );

					//if (count($results_news) != 0) {
						$code = $results_news[0]->html_modules;
						/*for ($a=1; $a<20; $a++) {
							$code = str_replace('modules/module_' . $a . '.jpg','modules/module_' . $a . '.jpg?r=' . uniqid(),$code);
						}*/
						echo $code;
					//}
					?>
					</div>
					<div class="resize">
						<a href="#" title="<?php _e('Resize toolbox','knews');?>">&nbsp;</a>
					</div>
				</div>

				<div class="editor_iframe">
					<div id="botonera">
						<div class="right_icons">
							<a href="#" title="hidden CSS preview" class="previewCSS" onclick="b_preview('css'); return false;">-</a>
							<a href="#" title="hidden images preview" class="previewIMG" onclick="b_preview('img'); return false;">-</a>
							<select name="zoom" id="zoom" autocomplete="off"><option value="0.5">50%</option><option value="0.75">75%</option><option value="1" selected="selected">100%</option><option value="1.5">150%</option><option value="2">200%</option><option value="4">400%</option></select>
						</div>
						<div class="standard_buttons desactivada">
							<a href="#" title="bold" class="bold" onclick="b_simple('Bold'); return false;">B</a>
							<a href="#" title="italic" class="italic" onclick="b_simple('Italic'); return false;">I</a>
							<a href="#" title="strike-through" class="strike" onclick="b_simple('StrikeThrough'); return false;">St</a>
							<a href="#" title="insert image" class="image" onclick="b_insert_image(); return false;">i</a>
							<a href="#" title="link" class="link" onclick="b_link(); return false;">A</a>
							<a href="#" title="UN-link" class="no_link" onclick="b_del_link(); return false;">(A)</a>
						</div>
						<div class="justify_buttons desactivada">
							<a href="#" title="align: Left" class="just_l" onclick="b_justify('left'); return false;">L</a>
							<a href="#" title="align: Center" class="just_c" onclick="b_justify('center'); return false;">C</a>
							<a href="#" title="align: Right" class="just_r" onclick="b_justify('right'); return false;">R</a>
							<a href="#" title="align: Justify" class="just_j" onclick="b_justify('justify'); return false;">J</a>
						</div>
						<div class="standard_buttons desactivada">
							<a href="#" class="sup" title="superscript" onclick="b_simple('Superscript'); return false;">sp</a>
							<a href="#" class="sub" title="subscript" onclick="b_simple('Subscript'); return false;">sb</a>
							<a href="#" class="color" title="change color" onclick="b_color(); return false;">C</a>
						</div>
						<div class="do_undo_buttons">
							<a href="#" class="undo" title="undo" onclick="b_simple('undo'); return false;">U</a>
							<a href="#" class="redo" title="redo" onclick="b_simple('redo'); return false;">R</a>
						</div>
						<div class="standard_buttons desactivada">
							<a href="#" class="clean" title="clean format" onclick="b_clean(); return false;">C</a>
						</div>
						<div>
							<a href="#" class="htmledit" title="HTML edit" onclick="b_htmledit(); return false;">H</a>
						</div>
						<div class="automated_buttons desactivada">
							<a href="#" class="automated" title="Automated" onclick="return false;">A</a>
						</div>
						<span class="clear"></span>
					</div>
					<div class="automated_menu">
						<ul>
							<?php
							$ef = $Knews_plugin->get_extra_fields();
							echo '<li class="token off"><a href="#">%email%</a></li>';
							foreach ($ef as $e) {
								if ($e->token != '') echo '<li class="token off"><a href="#">' . $e->token . '</a></li>';
							}
							?>
							<li class="shortcode off"><a href="#">#blog_name#</a></li>
							<li class="shortcode off"><a href="#">#url_confirm#</a></li>
							<li class="thetitle off"><a href="#">%the_title_1%</a></li>
						</ul>
					</div>
					<div class="iframe_container"><iframe class="knews_editor" id="knews_editor" name="knews_editor" style="width:100%; height:100px" src="<?php echo get_admin_url() . 'admin-ajax.php?action=knewsEditNewsletter&idnews=' . $id_edit . '&r=' . uniqid() . $lang_attr; ?>"></iframe></div>
					<div id="tagsnav"></div>
				</div>
				<div class="drag_preview"></div>
			</div>
		</div>
	</div>
	<div id="knews_dialog_color" style="display:none;">
		<div class="rightcol">
			<div class="default_colours">
			<?php
			$colours = array ('#ffffff','#CCCCCC','#999999','#666666','#333333','#000000','#FFAAAA','#FF0000','#7F0000','#ffd4aa','#ff7f00','#7f3f00','#ffffaa','#ffff00','#7f7f00','#aaffaa','#00ff00','#007f00','#aaffff','#00ffff','#007f7f','#aad4ff','#007fff','#003f7f','#5656ff','#d4aaff','#aa56ff','#3f007f','#ff56ff','#7f007f','#ffaad4','#ff007f','#7f003f');

			foreach ($predefined_colours as $c) {
				echo '<a href="#" style="background:' . $c . '" onClick="set_colour_default(this); return false;" title="' . $c  . '"></a>';
			}
			
			if (count($predefined_colours) != 0) echo '<span class="divider"></span>';
			
			foreach ($colours as $c) {
				echo '<a href="#" style="background:' . $c . '" onClick="set_colour_default(this); return false;" title="' . $c  . '"></a>';
			} 
			?>
			<span class="divider"></span>
				<input type="button" value="OK" onclick="CallBackColour(jQuery('#colorpickerVal').val()); tb_remove(); return false;" class="bt_ok">
				<input type="button" value="OK" onclick="CallBackColourEditor(jQuery('#colorpickerVal').val()); tb_remove(); return false;" class="bt_ed_ok">
				<input type="button" value="CANCEL" onclick="tb_remove(); return false;">
			</div>
		</div>
		<p>Color: <input type="text" id="colorpickerVal" value="#000000" /></p>
		<div id="colorpicker"></div>
	</div>
<?php
	//Security for CSRF attacks
	wp_nonce_field($knews_nonce_action, $knews_nonce_name); 
	}
?>
