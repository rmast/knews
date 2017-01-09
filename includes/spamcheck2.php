<html>
<head>
<title>Email checking</title>
<style type="text/css">
body, p, th, td {
	font-family:Verdana, Geneva, sans-serif;
	font-size:13px;
}
h1 {
	font-size:22px;
}
a.boto {
	display:inline-block;
	padding:5px 10px;
	border-radius:5px;
	background:#ccc;
	color:#000;
	font-weight:bold;
}
a.boto:hover {
	background:#999;
	color:#fff;
}
</style>
</head>
<body>
<?php
	$site = get_bloginfo('url');
	$unique = isset($_GET['unique']) ? $_GET['unique'] : 0;

	echo '<p>' . __('Shipping done.','knews') . '</p>';
	echo '<p><a href="' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://') . 'knewsplugin.com/spamcheck/check.php?site=' . urlencode($site) . '&unique=' . $unique . '" class="boto">SEE RESULT</a></p>';
?>
</body>
</html>
<?php
die();
?>