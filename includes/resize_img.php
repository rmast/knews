<?php 

function knews_resize_img_fn($url_img, $width, $height, $media_id=0) {

	global $knewsOptions;
	
    if ($url_img == '' || $url_img == 'undefined') {

		$jsondata['result'] = 'error';
		$jsondata['url'] = '';
		$jsondata['message'] = __('Error: there is no image selected','knews');
		return $jsondata;
	}
	
	$refresh = false;

	global $Knews_plugin;
	$crop = apply_filters('knews_image_crop_' . $Knews_plugin->template_id, ($knewsOptions['crop_knews'] == 'yes') );

	$wp_dirs = wp_upload_dir();
		
	//Support for https admin
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
		if (substr($url_img,0,5) == 'http:') $url_img = 'https:' . substr($url_img,5);
		if (substr($blog_url,0,5) == 'http:') $blog_url = 'https:' . substr($blog_url,5);
		if (substr($wp_dirs['baseurl'],0,5) == 'http:') $wp_dirs['baseurl'] = 'https:' . substr($wp_dirs['baseurl'],5);
		//if (substr($wp_dirs['url'],0,5) == 'http:') $wp_dirs['url'] = 'https:' . substr($wp_dirs['url'],5);
	}

	if (strpos($url_img, $wp_dirs['baseurl']) === false) {
		$look = str_replace('//','*doublehash*',$url_img);
		$cut_folders = explode('/', $wp_dirs['baseurl']);
		if (is_array($cut_folders) && isset($cut_folders[0]) ) {
			$cut_folders[0] = str_replace('*doublehash*','//',$cut_folders[0]);
		}
		$x=count($cut_folders)-1;
		$found=false;
		while (!$found && $x>-1) {
			$test_path=$cut_folders[0];
			for ($a=1; $a<$x; $a++) {
				$test_path .= '/' . $cut_folders[$a];
			}
			if (strpos($url_img, $test_path) !== false) {
				$extra_path='';
				for ($a=$x; $a<=count($cut_folders)-1; $a++) {
					$extra_path .= '/' . $cut_folders[$a];
				}
				$wp_dirs['basedir'] = substr($wp_dirs['basedir'], 0, strpos($wp_dirs['basedir'], $extra_path) );
				$wp_dirs['baseurl'] = substr($wp_dirs['baseurl'], 0, strpos($wp_dirs['baseurl'], $extra_path) );
				$found=true;
			}
			$x--;	
		}
	}
		
	$file_extension = pathinfo($url_img, PATHINFO_EXTENSION);

	if (strpos($url_img, "-knewsnocrop-") !== false) {
		$url_img = substr($url_img, 0, strpos($url_img, "-knewsnocrop-")) . '.' . $file_extension;
	}
	if (strpos($url_img, "-knewscrop-") !== false) {
		$url_img = substr($url_img, 0, strpos($url_img, "-knewscrop-")) . '.' . $file_extension;
	}
	
	$wp_sufix = '-' . $width . 'x' . $height . '.' . $file_extension;
	$full_sufix = ($crop ? '-knewscrop' : '-knewsnocrop') . $wp_sufix;
	$full_alt_sufix = (!$crop ? '-knewscrop' : '-knewsnocrop') . $wp_sufix;

	if (strpos($url_img, $full_sufix) !== false) {
		$url_img = str_replace($full_sufix, '.' . $file_extension, $url_img);
		
	} else if (strpos($url_img, $full_alt_sufix) !== false) {
		$url_img = str_replace($full_alt_sufix, '.' . $file_extension, $url_img);

	} else if (strpos($url_img, $wp_sufix) !== false) {
		$url_img = str_replace($wp_sufix, '.' . $file_extension, $url_img);
		
	}

	$pos = strrpos($url_img, "-");
	if ($pos !== false && strrpos($url_img, "x") != false && strrpos($url_img, "x") > $pos + 1) { 
		$pos2 = strrpos($url_img, ".");
		
		if ($pos2 !== false) { 
			$try_original = substr($url_img, 0, $pos) . substr($url_img, $pos2);
			$try_original2 = substr($try_original, strlen($wp_dirs['baseurl']));

			if (is_file($wp_dirs['basedir'] . $try_original2)) $url_img = $try_original;
		}
	}
	
	$filename = pathinfo($url_img, PATHINFO_BASENAME);

	$blog_url = get_bloginfo('url');

	if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='qt') $blog_url = site_url();

	if (substr($blog_url, -1, 1) == '/') $blog_url = substr($blog_url, 0, strlen($blog_url)-1);
	
	$blog_url_base = explode('/',$blog_url); 
	$blog_url_base = $blog_url_base[0] . '//' . $blog_url_base[2];

	$absolute_url = $wp_dirs['baseurl'];
	if (strpos($absolute_url, 'http://')===false && strpos($absolute_url, 'https://')===false) {
		$absolute_url = $blog_url_base . $absolute_url;
	}
	
	$subdir = $url_img;

	if (strpos($url_img, $wp_dirs['baseurl']) !== false) {
		$subdir = substr($url_img, strpos($url_img, $wp_dirs['baseurl']) + strlen($wp_dirs['baseurl']));
	}
	$subdir = str_replace($filename,'',$subdir);
	
	$resized_filename = substr($filename, 0 , -1 * (strlen($file_extension) + 1) ) . $full_sufix;
	
	/* print_r($wp_dirs);
	echo '$blog_url: ' . $blog_url . "<br>";
	echo '$absolute_url: ' . $absolute_url . "<br>";
	echo '$subdir: ' . $subdir . "<br>";
	echo '$resized_filename: ' . $resized_filename . "<br>";
	echo '$url_img: ' . $url_img . "<br>";
	echo '$blog_url_base: ' . $blog_url_base . "<br>";*/

	if (!is_file($wp_dirs['basedir'] . $subdir . $filename)) {
		
		$jsondata['result'] = 'error';
		$jsondata['url'] = '';
		$jsondata['message'] = __('Error: there is no image selected','knews');
		return $jsondata;		
	}

	
	

	$imagesize = getimagesize($wp_dirs['basedir'] . $subdir . $filename);
	if ($imagesize[0] == $width && $imagesize[1]==$height) {
		
		$jsondata['url'] = $absolute_url . $subdir . $filename;
		$jsondata['width'] = $width;
		$jsondata['height'] = $height;
		$jsondata['media_id'] = $media_id;
		return $jsondata;
	}
	
	if (is_file($wp_dirs['basedir'] . $subdir . $resized_filename)) {

		if (filemtime($wp_dirs['basedir'] . $subdir . $resized_filename) > filemtime($wp_dirs['basedir'] . $subdir . $filename) ) {
			$imagesize = getimagesize($wp_dirs['basedir'] . $subdir . $resized_filename);
			$jsondata['result'] = 'ok';
			$jsondata['url'] = $absolute_url . $subdir . $resized_filename;
			$jsondata['width'] = $imagesize[0];
			$jsondata['height'] = $imagesize[1];
			$jsondata['media_id'] = $media_id;
			return $jsondata;
		}
		
		$refresh = true;
	}

	// resize the image
	
	global $wp_version;
	if (version_compare('3.5', $wp_version, '<=')) {

		$image_editor = wp_get_image_editor( $wp_dirs['basedir'] . $subdir . $filename );
		if ( ! is_wp_error( $image_editor ) ) {

			$thumb = $wp_dirs['basedir'] . $subdir . $resized_filename;

			$image_editor->resize( $width, $height, $crop );
			$image_editor->save( $thumb );

		} else {
			$jsondata['result'] = 'error';
			$jsondata['url'] = '';
			$jsondata['message'] = __('Error','knews') . ': ' . $image_editor->get_error_message();;
			return $jsondata;
		}

	} else {
		$thumb = image_resize($wp_dirs['basedir'] . $subdir . $filename, $width, $height, $crop, substr($full_sufix, 1) );
		if ( is_wp_error( $thumb ) ) {
			$jsondata['result'] = 'error';
			$jsondata['url'] = '';
			$jsondata['message'] = __('Error','knews') . ': ' . $thumb->get_error_message();;
			return $jsondata;
		}
	}
	
	if (is_string($thumb)) {

		//$thumb = substr($thumb, strpos($thumb, 'wp-content'));
		$thumb = substr($thumb, strlen($wp_dirs['basedir']));

		if (is_file($wp_dirs['basedir'] . $thumb)) {
			
			$imagesize = getimagesize($wp_dirs['basedir'] . $thumb);
			$jsondata['result'] = 'ok';
			$jsondata['url'] = $absolute_url . $thumb;
			$jsondata['width'] = $imagesize[0];
			$jsondata['height'] = $imagesize[1];
			$jsondata['media_id'] = $media_id;
			return $jsondata;
			
		}

	}

	$jsondata['result'] = 'error';
	$jsondata['url'] = '';
	$jsondata['message'] = __('Error','knews') . ': ' . __('Check the directory permissions for','knews') . ' ' . $wp_dirs['basedir'] . $subdir;
	return $jsondata;
	
	//
	
/*
Normal:
Array
(
    [path] => /home/knewsplugin/www/debug/wp-content/uploads/2014/11
    [url] => http://www.knewsplugin.com/debug/wp-content/uploads/2014/11
    [subdir] => /2014/11
    [basedir] => /home/knewsplugin/www/debug/wp-content/uploads
    [baseurl] => http://www.knewsplugin.com/debug/wp-content/uploads
    [error] => 
)

Amb define('WP_CONTENT_URL','http://knewsplugin.com/debug/wp-content');
Array
(
    [path] => /home/knewsplugin/www/debug/wp-content/uploads/2014/11
    [url] => http://knewsplugin.com/debug/wp-content/uploads/2014/11
    [subdir] => /2014/11
    [basedir] => /home/knewsplugin/www/debug/wp-content/uploads
    [baseurl] => http://knewsplugin.com/debug/wp-content/uploads
    [error] => 
)

Amb define('WP_CONTENT_URL','/debug/wp-content');
Array
(
    [path] => /home/knewsplugin/www/debug/wp-content/uploads/2014/11
    [url] => /debug/wp-content/uploads/2014/11
    [subdir] => /2014/11
    [basedir] => /home/knewsplugin/www/debug/wp-content/uploads
    [baseurl] => /debug/wp-content/uploads
    [error] => 
)
*/
	print_r($wp_dirs);
	
	$blog_url = get_bloginfo('url');
	
	if (KNEWS_MULTILANGUAGE && $knewsOptions['multilanguage_knews']=='qt') $blog_url = site_url();
	
	if (substr($blog_url, -1, 1) == '/') $blog_url = substr($blog_url, 0, strlen($blog_url)-1);
	//$absolute_dir = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'wp-admin'));

	//Support for https admin
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
		if (substr($url_img,0,5) == 'http:') $url_img = 'https:' . substr($url_img,5);
		if (substr($blog_url,0,5) == 'http:') $blog_url = 'https:' . substr($blog_url,5);
		if (substr($wp_dirs['baseurl'],0,5) == 'http:') $wp_dirs['baseurl'] = 'https:' . substr($wp_dirs['baseurl'],5);
		if (substr($wp_dirs['basedir'],0,5) == 'http:') $wp_dirs['basedir'] = 'https:' . substr($wp_dirs['basedir'],5);
	}

	/*
	$without_www=false;
	if (substr($url_img, 0, 4) == 'http' && strpos($url_img, '/www.') === false) $without_www=true;
	if (substr($wp_dirs['baseurl'], 0, 4) == 'http' && strpos($wp_dirs['baseurl'], '/www.') === false) $without_www=true;
	if (substr($blog_url, 0, 4) == 'http' && strpos($blog_url, '/www.') === false) $without_www=true;
	if ($without_www) {
		$url_img = str_replace('/www.', '/', $url_img);
		$wp_dirs['baseurl'] = str_replace('/www.', '/', $wp_dirs['baseurl']);
		$blog_url = str_replace('/www.', '/', $blog_url);
	}
	*/
	
	if (strpos($url_img, $blog_url) === false) $url_img = $blog_url . $url_img;
	if (strpos($wp_dirs['baseurl'], $blog_url) === false) $wp_dirs['baseurl'] = $blog_url . $wp_dirs['baseurl'];


	$wp_dirs['basedir'] = substr($wp_dirs['basedir'], strpos($wp_dirs['basedir'], $blog_url));

	//echo '*' . $wp_dirs['baseurl'] . '*<br>';
	//echo '*' . substr($url_img, 0, strlen($wp_dirs['baseurl'])) . '*<br>';
	if (substr($url_img, 0, strlen($wp_dirs['baseurl'])) != $wp_dirs['baseurl']) {
		//echo 'no comencen igual<br>';
		$wp_dirs['baseurl']=substr($url_img, 0, strpos($url_img, KNEWS_WP_CONTENT));
		$wp_dirs['basedir']=substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'wp-admin'));
	}
	//echo '*' . $wp_dirs['baseurl'] . '*<br>';
	//echo '*' . $wp_dirs['basedir'] . '*<br>';

	//$url_start = substr($url_img, 0, strpos($url_img, $_SERVER['SERVER_NAME']) + strlen($_SERVER['SERVER_NAME']));

	$pos = strrpos($url_img, "-");
	if ($pos !== false) { 
		$pos2 = strrpos($url_img, ".");
		
		if ($pos2 !== false) { 
			$try_original = substr($url_img, 0, $pos) . substr($url_img, $pos2);
			$try_original2 = substr($try_original, strlen($wp_dirs['baseurl']));

			if (is_file($wp_dirs['basedir'] . $try_original2)) $url_img = $try_original;
		}
	}
	
    if ($url_img != '' && $url_img != 'undefined') {

		// cut the url
		//$url_imatge = substr($img_url, strpos($img_url, 'wp-content'));

		$url_imatge = substr($url_img, strlen($wp_dirs['baseurl']));
		$url=$url_imatge;

		$url_imatge = str_replace('.jpg', '-' . $width . 'x' . $height .'.jpg', $url_imatge);
		$url_imatge = str_replace('.jpeg', '-' . $width . 'x' . $height .'.jpeg', $url_imatge);
		$url_imatge = str_replace('.gif', '-' . $width . 'x' . $height .'.gif', $url_imatge);
		$url_imatge = str_replace('.png', '-' . $width . 'x' . $height .'.png', $url_imatge);

		$url_imatge = str_replace('.JPG', '-' . $width . 'x' . $height .'.JPG', $url_imatge);
		$url_imatge = str_replace('.JPEG', '-' . $width . 'x' . $height .'.JPEG', $url_imatge);
		$url_imatge = str_replace('.GIF', '-' . $width . 'x' . $height .'.GIF', $url_imatge);
		$url_imatge = str_replace('.PNG', '-' . $width . 'x' . $height .'.PNG', $url_imatge);

		if (is_file($wp_dirs['basedir'] . $url)) {
			$size = getimagesize($wp_dirs['basedir'] . $url);
			if ($size[0]==$width && $size[1]==$height) {

				$jsondata['result'] = 'ok';
				$jsondata['url'] = $wp_dirs['baseurl'] . $url;
				return $jsondata;
			}
		}
		
		if (is_file($wp_dirs['basedir'] . $url_imatge)) {

			$jsondata['result'] = 'ok';
			$jsondata['url'] = $wp_dirs['baseurl'] . $url_imatge;
			return $jsondata;
	
		} else {
	
			// resize the image
			
			global $wp_version;
			if (version_compare('3.5', $wp_version, '<=')) {

				$image_editor = wp_get_image_editor( $wp_dirs['basedir'] . $url );
				if ( ! is_wp_error( $image_editor ) ) {

					$file_extension = pathinfo($wp_dirs['basedir'] . $url, PATHINFO_EXTENSION);
					$thumb = $wp_dirs['basedir'] . substr($url, 0, (strlen($file_extension) + 1) * -1) . '-' . $width.'x'.$height . '.' . $file_extension;

					$image_editor->resize( $width, $height, $crop );
					$image_editor->save( $thumb );

				} else {
					$jsondata['result'] = 'error';
					$jsondata['url'] = '';
					$jsondata['message'] = __('Error','knews') . ': ' . $image_editor->get_error_message();;
					return $jsondata;
				}

			} else {
				$thumb = image_resize($wp_dirs['basedir'] . $url, $width, $height, $crop, $width.'x'.$height);
				if ( is_wp_error( $thumb ) ) {
					$jsondata['result'] = 'error';
					$jsondata['url'] = '';
					$jsondata['message'] = __('Error','knews') . ': ' . $thumb->get_error_message();;
					return $jsondata;
				}
			}
			

			if (is_string($thumb)) {

				//$thumb = substr($thumb, strpos($thumb, 'wp-content'));
				$thumb = substr($thumb, strlen($wp_dirs['basedir']));

				$jsondata['result'] = 'ok';
				$jsondata['url'] = $wp_dirs['baseurl'] . $thumb;
				return $jsondata;
	
			} else {
				if (is_file($blog_url . $url)) {

					$jsondata['result'] = 'ok';
					$jsondata['url'] = $wp_dirs['baseurl'] . $url;
					return $jsondata;
					
				} else {

					$jsondata['result'] = 'error';
					$jsondata['url'] = '';
					$jsondata['message'] = __('Error','knews') . ': ' . __('Check the directory permissions for','knews') . ' ' . $wp_dirs['basedir'] . dirname($url);
					return $jsondata;
				}
			}
		}

	} else {

		$jsondata['result'] = 'error';
		$jsondata['url'] = '';
		$jsondata['message'] = __('Error: there is no image selected','knews');
		return $jsondata;
	}
}
?>