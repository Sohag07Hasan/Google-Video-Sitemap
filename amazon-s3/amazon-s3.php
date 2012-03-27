<?php

/*
 * Main class to control the s3 functionality
 */

/*
 * including the php sdk for amazon
 */
include VideoSitemapS3 . '/sdk-1.5.3/sdk.class.php';
$s3 = new AmazonS3(array('key'=>'AKIAJAJQPZFJO4ZDQC6Q', 'secret'=>'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'));


class amazon_s3{
	
	/*
	 * contains all the hooks here
	 */
	function init(){
		add_action('admin_menu', array(get_class(), 's3_video_menu'));
		add_action('admin_print_styles', array(get_class(), 's3_video_load_css'));
		add_action('admin_print_scripts', array(get_class(),'s3_video_load_js'));
		add_action('wp_print_scripts', array(get_class(),'s3_video_load_js_player'));
	}
	
	
	/*
	 * Creates a Menu to contol the S3 functionality 
	 */
	static function s3_video_menu(){
		// Main side bar entry
		add_menu_page('S3 Video', 'S3 Video', 'manage_options', 's3-video', array(get_class(), 's3_video'));

		// S3 sidebar child pages
		/*
		add_submenu_page('s3-video', __('Upload Video','upload-video'), __('Upload Video','upload-video'), 'manage_options', 's3_video_upload_video', array(get_class(), 's3_video_upload_video'));		
		add_submenu_page('s3-video', __('Playlist Management','show-playlists'), __('Playlist Management','show_playlists'), 'manage_options', 's3_video_show_playlist', array(get_class(),'s3_video_show_playlists'));
		add_submenu_page('s3-video', __('Create Playlist','create-playlist'), __('Create Playlist','create_playlist'), 'manage_options', 's3_video_create_playlist', array(get_class(),'s3_video_create_playlist'));
		
		 */
		add_submenu_page('s3-video', __('Plugin Settings','plugin-settings'), __('Plugin Settings','plugin-settings'), 'manage_options', 's3_video_plugin_settings', array(get_class(),'s3_video_settings'));  	
		 
	
	 }
		 
	
	/*
	 * Menu Page to show the existing videos
	 */
	static function s3_video(){
		$existingVideos = array();
		global $s3;
		$buckets = $s3->get_bucket_list();
		if(is_array($buckets)) :
			foreach($buckets as $bucket) :
				$objects = $s3->get_object_list($bucket);
				if(is_array($objects)):
					foreach($objects as $object):	
						$metadata = $s3->get_object_metadata($bucket, $object);
						$existingVideos[$bucket][$object] = array(
							'last-modified' => strtotime($metadata['Headers']['last-modified']),
							'size' => (int) $metadata['Size']
						);							
						
					endforeach;
				endif;
			endforeach;		
		endif;
		$pluginSettings = self :: get_s3_settings();
		include VideoSitemapS3 . '/video-management/existing-videos.php';
		
	}
	
	/*
	* Page to configure plugin settings i.e Amazon access keys etc
	*/
	static function s3_video_settings(){
		if ($_POST['amazon-s3-submit'] == 'Y') {
			if ((!empty($_POST['amazon_access_key'])) && (!empty($_POST['amazon_secret_access_key']))) {
				
				update_option( 'amazon_access_key', $_POST['amazon_access_key']);
				update_option( 'amazon_secret_access_key', $_POST['amazon_secret_access_key'] );
				update_option( 'amazon_video_bucket', $_POST['amazon_video_bucket'] );

				if (!empty($_POST['amazon_url'])) {
					update_option( 'amazon_url', $_POST['amazon_url']);
				} else {
					update_option( 'amazon_url', 's3.amazonaws.com');
				}

				if (!empty($_POST['page_result_limit'])) {
					update_option( 's3_video_page_result_limit', $_POST['page_result_limit']);
				} else {
					update_option( 's3_video_page_result_limit', 15);
				}

				$successMsg = 'Plugin settings saved successfully.';
			}
			else{
				include VideoSitemapS3 . '/settings/configuration_required.php';
			}
			
		}
		
		$pluginSettings = self :: get_s3_settings();
		require_once(VideoSitemapS3 . '/settings/s3-settings.php');
	}
	/*
	 *Get the settings field
	 */
	static function get_s3_settings(){
		$pluginSettings['amazon_access_key'] = get_option('amazon_access_key');
		$pluginSettings['amazon_secret_access_key'] = get_option('amazon_secret_access_key');
		$pluginSettings['amazon_url'] = get_option('amazon_url');		
		$pluginSettings['s3_video_page_result_limit'] = get_option('s3_video_page_result_limit');		
		return $pluginSettings;

	}
	
	/*
	* Load the custom style sheets for the admin pages
	*/
	static function s3_video_load_css(){
		wp_register_style('s3_video_default', VideoSitemapURL . '/amazon-s3/css/style.css');
		wp_enqueue_style('s3_video_default');

		wp_register_style('s3_video_colorbox', VideoSitemapURL . '/amazon-s3/css/colorbox.css');
		wp_enqueue_style('s3_video_colorbox');	

		wp_register_style('multiselect_css', VideoSitemapURL . '/amazon-s3/css/chosen.css');
		wp_enqueue_style('multiselect_css');			
	}
	
	
	/*
	* Load javascript required by the backend administration pages
	*/
	static function s3_video_load_js(){
		
		//common files for both admin and front-end/ player
		wp_enqueue_script('jquery');
		wp_enqueue_script('swfobject');
		wp_enqueue_script('flowPlayer', VideoSitemapURL . '/amazon-s3/js/flowplayer-3.2.6.js', array('jquery'), '1.0');
		wp_enqueue_script('flowPlayerPlaylist', VideoSitemapURL . '/amazon-s3/js/jquery.playlist.js', array('jquery'), '1.0');
		
		wp_enqueue_script('validateJSs', VideoSitemapURL . '/amazon-s3/js/jquery.validate.js', array('jquery'), '1.0');
		wp_enqueue_script('placeholdersJS', VideoSitemapURL . '/amazon-s3/js/jquery.placeholders.js', array('jquery'), '1.0');
		wp_enqueue_script('colorBox', VideoSitemapURL . '/amazon-s3/js/jquery.colorbox.js', array('jquery'), '1.0');
		wp_enqueue_script('tableSorter', VideoSitemapURL . '/amazon-s3/js/jquery.tablesorter.js', array('jquery'), '1.0');	
		wp_enqueue_script('tablePaginator', VideoSitemapURL . '/amazon-s3/js/jquery.paginator.js', array('jquery'), '1.0');	
		wp_enqueue_script('multiSelect', VideoSitemapURL . '/amazon-s3/js/jquery.multiselect.js', array('jquery'), '1.0');		
		wp_enqueue_script('dragDropTable', VideoSitemapURL . '/amazon-s3/js/jquery.tablednd.js', array('jquery'), '1.0');		
	}
	
	
	/*
	 * Load the common scripts basically player
	 */
	static function s3_video_load_js_player(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('swfobject');
		wp_enqueue_script('flowPlayer', VideoSitemapURL . '/amazon-s3/js/flowplayer-3.2.6.js', array('jquery'), '1.0');
		wp_enqueue_script('flowPlayerPlaylist', VideoSitemapURL . '/amazon-s3/js/jquery.playlist.js', array('jquery'), '1.0');
	}

	/*
	* Return a file size in a human readable format 
	*/
	static function s3_humanReadableBytes($bytes)	{
	$units = array('B', 'K', 'MB', 'GB', 'TB');
		for ($i = 0, $size =$bytes; $size>1024; $size=$size/1024)
		$i++;
		return number_format($size, 2) . ' '  . $units[min($i, count($units) -1 )];
	}

	
}

