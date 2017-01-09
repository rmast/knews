<?php
global $Knews_plugin;

if ($Knews_plugin) {


	if (! $Knews_plugin->initialized) $Knews_plugin->init();

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

	<link rel="stylesheet" type="text/css" href="<?php echo KNEWS_URL; ?>/admin/styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo KNEWS_URL; ?>/includes/codemirror/codemirror.css" />
	<script src="<?php echo KNEWS_URL; ?>/includes/codemirror/codemirror.js" type="text/javascript"></script>
	<?php /*
	<script src="<?php echo KNEWS_URL; ?>/includes/codemirror/xml.js" type="text/javascript"></script>
	<script src="<?php echo KNEWS_URL; ?>/includes/codemirror/htmlmixed.js" type="text/javascript"></script>
	*/ ?>

    <style type="text/css">
      .CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}
    </style>

	</head>
	<body>
    <form>
		<textarea id="code" name="code"></textarea>
		<p style="padding:10px 0 0 0; text-align:center; margin:0;"><input type="button" class="save knews-button-primary" value="Close and Save Changes" /> &nbsp; <input type="button" class="undo knews-button" value="Close and Discard Changes" /></p>
	</form>
<?php if (isset($_GET['editor'])  && $_GET['editor']=='2') {?>
	<script type="text/javascript">
	parent.jQuery(document).ready(function() {

		var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
		  //mode: "text/html",
		  lineNumbers: true,
		  lineWrapping: true,
		  tabMode: "indent"
		});
		code = parent.partial_html_populate();
		console.log(code);
		editor.setValue(code);
		editor.setSize(parseInt(parent.jQuery(window).width(), 10)-15, parseInt(parent.jQuery(window).height(), 10)-60);

		parent.jQuery('input.save', document).click(function() {
			code=editor.getValue();			
			parent.partial_html_replace(code);
			parent.tb_remove();
		});
		
		parent.jQuery('input.undo', document).click(function() {
			parent.tb_remove();
		});
	});
	</script>
<?php } else { ?>
	<script type="text/javascript">
	parent.jQuery(document).ready(function() {

		save_code=parent.jQuery('iframe#knews_editor').contents().find('div.wysiwyg_editor').html();
		parent.clean_before_save();
		code=parent.jQuery('iframe#knews_editor').contents().find('div.wysiwyg_editor').html();
		parent.jQuery('iframe#knews_editor').contents().find('div.wysiwyg_editor').html(save_code);
		
		//parent.jQuery('#code',document).html(code);

		var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
		  //mode: "text/html",
		  lineNumbers: true,
		  lineWrapping: true,
		  tabMode: "indent"
		});
		editor.setValue(code);
		editor.setSize(parseInt(parent.jQuery(window).width(), 10)-15, parseInt(parent.jQuery(window).height(), 10)-60);
		
		parent.jQuery('input.save', document).click(function() {
			code=editor.getValue();			
			parent.jQuery('iframe#knews_editor').contents().find('div.wysiwyg_editor').html(code);
			parent.save_news();
			parent.tb_remove();
		});
		
		parent.jQuery('input.undo', document).click(function() {
			parent.tb_remove();
		});
	});
	</script>
<?php } ?>
	</body>
	</html>
<?php
}
die();
?>