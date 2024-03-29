<?php
/*
 * Main class to control youtube functionalilty
 */

// setting the include path
session_start();
$_SESSION['developerKey'] =  trim(get_option('site-map-youtube-devkey'));

$oldinpath = get_include_path();
set_include_path(VideoSitemapYT);

include VideoSitemapYT . '/Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_YouTube');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_App_Exception');

//resetting the include path
//set_include_path($oldinpath);

class youtube{
	
	/*
	 * init function to set all the hooks
	 */
	static function init(){
		add_action('admin_menu', array(get_class(), 'youtube_video_menu'));
		add_action('admin_print_scripts', array(get_class(), 'youtube_video_load_js'));
		
		//ajax functions are calling
		add_action('wp_ajax_get_upload_details', array(get_class(), 'checkUpload'));
		
		//shotcode api
		add_shortcode( 'Youtube_embed_video', array(get_class(), 'youtube_video_embed_video'));
	}
	
	/*
	 * shotcode manipulation
	 */
	static function youtube_video_embed_video($atts){
		if (!isset($atts['width'])) {$atts['width']="100%";}
		if (!isset($atts['height'])) {$atts['height']="400px";}
		if (!isset($atts['class'])) {$atts['class']="";}
		if (!isset($atts['id'])) {$atts['id']="";}
		$thesrc = $atts['src'];
		$width=$atts['width'];
		$height=$atts['height'];
		$class=$atts['class'];
		$id=$atts['id'];
		// Model output to look like the following:
		// <iframe width="425" height="349" class="myclass" id="myid" 
		// src="http://www.youtube.com/embed/olB56IEXpvE" 
		// frameborder="0" allowfullscreen></iframe>
		$embed_string =
			"<iframe width=\"". $width . "\" " . 
			"height=\"" . $height . "\" " .
			"class=\"" . $class . "\" " .
			"id=\"" . $id . "\" " .
			"src=\"" . $thesrc . "\" " . 
			"frameborder=\"0\" allowfullscreen>" . 
			"</iframe>" .
			"";

		if ( isset($atts['debug']) ) {
			$embed_string = $embed_string . "<br>Here are the parameters:<br> width=".$width." height=".$height." class=".$class." id=".$id." src=".$thesrc." debug=".$atts['debug']."<br>";
		} 

		return $embed_string;  
	}
	
	
	
	
	/*
	 * js file
	 */
	function youtube_video_load_js(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('youtubeVideoHandler', VideoSitemapURL . '/youtube/js/youtube-video.js', array('jquery'));
		wp_localize_script( 'youtubeVideoHandler', 'YVHandler', array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' )
					));
	}
	
	
	
	/*
	 * Creating a menu to show the youtube
	 */
	static function youtube_video_menu(){
		// Main side bar entry
		add_menu_page('Youtube Video', 'Youtube Video', 'manage_options', 'youtube-video', array(get_class(), 'youtube_video'));
	
		//child pages
		add_submenu_page('youtube-video', __('Upload Video','upload-video'), __('Upload Video','upload-video'), 'manage_options', 'youtube_video_upload_video', array(get_class(), 'youtube_video_upload_video'));
		add_submenu_page('youtube-video', __('Youtube Settings','youtube-setting'), __('Youtube Settings','youtube-setting'), 'manage_options', 'youtube_video_settings', array(get_class(), 'youtube_settings'));
		
	}
	
	/*
	 * you tube settings to update developers key
	 */
	static function youtube_settings(){
		include VideoSitemapYT . '/settings/developers-key-form.php';
	}




	/*
	 * existing youtube menu
	 */
	static function youtube_video(){
		//call some helper function		
		self :: activateAuthentication();	
		if(self::authenticated()) :
			
			//http client object
			$httpClient = self::getAuthSubHttpClient();
			$youTubeService = new Zend_Gdata_YouTube($httpClient);
			
			//setting protocol version
			$youTubeService->setMajorProtocolVersion(2);
			
			//delete controling
			if($_GET['action'] == 'd' && !empty($_GET['vid'])){
				$del_msg = self :: deleteVideo($_GET['vid'], $httpClient, $youTubeService);
			}
			
			
			//setup query parameters
		/*	$query = $youTubeService->newVideoQuery();
			$query->setOrderBy('title');
			$query->setStartIndex(1);
			$query->setMaxResults(1);
			*/
			try {
				$feed = $youTubeService->getUserUploads('default');
				
			}
			catch (Zend_Gdata_App_HttpException $httpException) {
				 echo '<div class="error"><p>ERROR ' . $httpException->getMessage() . '</p></div>';
								
				return;
			}
			catch (Zend_Gdata_App_Exception $e) {
				print 'ERROR - Could not retrieve users video feed: '
					. $e->getMessage() . '<br />';
				return;
			}
						
			
			include VideoSitemapYT . '/video-management/existing-videos.php';
		else :
			self :: generateAuthSubRequestLink();
		endif;
		
	}
	
	/*
	 * video uploading page
	 */
	static function youtube_video_upload_video(){
				
		self :: activateAuthentication();			
		include VideoSitemapYT . '/video-management/upload-video.php';
			
		
	}
	
	
	/**
	 * Convenience method to obtain an authenticted Zend_Http_Client object.
	 *
	 * @return Zend_Http_Client An authenticated client.
	 */
	function getAuthSubHttpClient(){
		try {
			$httpClient = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
		} catch (Zend_Gdata_App_Exception $e) {
			print 'ERROR - Could not obtain authenticated Http client object. '
				. $e->getMessage();
			return;
		}
		$httpClient->setHeaders('X-GData-Key', 'key='. $_SESSION['developerKey']);    
		return $httpClient;
	}
	
	/*
	 * Activates the youtube authentication functions
	 */
	private static function activateAuthentication(){
		//some default settings
		
		self :: generateUrlInformation($_GET['page']);
		if(isset($_GET['token'])) :
			self :: updateAuthSubToken($_GET['token']);
		endif;
		
	}
	
	
	/**
	* Upgrade the single-use token to a session token.
	*
	* @param string $singleUseToken A valid single use token that is upgradable to a session token.
	* @return void
	*/
	static function updateAuthSubToken($singleUseToken){		

		try {
			$sessionToken = Zend_Gdata_AuthSub::getAuthSubSessionToken($singleUseToken);
		} catch (Zend_Gdata_App_Exception $e) {
			print 'ERROR - Token upgrade for ' . $singleUseToken
				. ' failed : ' . $e->getMessage();
			return;
		}

		$_SESSION['sessionToken'] = $sessionToken;		

	}


	/*
	 * print the entire video feed with pagination
	 */
	static function printEntireFeed($videoFeed, $counter) {
		foreach($videoFeed as $videoEntry) {
			echo $counter . " - " . $videoEntry->getVideoTitle() . "<br/>";
			$counter++;
		}

		// See whether we have another set of results
		try {
			$videoFeed = $videoFeed->getNextFeed();
		} 
		catch (Zend_Gdata_App_Exception $e) {
			echo $e->getMessage() . "<br/>";
			return;
		}

		if ($videoFeed) {
			echo "-- next set of results --<br/>";
			self :: printEntireFeed($videoFeed, $counter);
		}
	}
	
	
	/*
	 * parsing feed
	 */
	function echoVideoList($feed, $authenticated = false){
		$table = '<table id="videoResultList" class="videoList"><tbody>';
		$results = 0;

		foreach ($feed as $entry) {
			$videoId = $entry->getVideoId();
			$thumbnailUrl = 'notfound.jpg';
			if (count($entry->mediaGroup->thumbnail) > 0) {
				$thumbnailUrl = htmlspecialchars(
					$entry->mediaGroup->thumbnail[0]->url);
			}

			$videoTitle = htmlspecialchars($entry->getVideoTitle());
			$videoDescription = htmlspecialchars($entry->getVideoDescription());
			$videoCategory = htmlspecialchars($entry->getVideoCategory());
			$videoTags = $entry->getVideoTags();

			$table .= '<tr id="video_' . $videoId . '">'
					. '<td width="130"><img onclick="ytVideoApp.presentVideo(\''
					. $videoId. '\')" src="' . $thumbnailUrl. '" /></td>'
					. '<td><a href="#" onclick="ytVideoApp.presentVideo(\''
					. $videoId . '\')">'. stripslashes($videoTitle) . '</a>'
					. '<p class="videoDescription">'
					. stripslashes($videoDescription) . '</p>'
					. '<p class="videoCategory">category: ' . $videoCategory
					. '</p><p class="videoTags">tagged: '
					. htmlspecialchars(implode(', ', $videoTags)) . '</p>';

			if ($authenticated) {
				$table .= '<p class="edit">'
						. '<a onclick="ytVideoApp.presentMetaDataEditForm(\''
						. addslashes($videoTitle) . '\', \''
						. addslashes($videoDescription) . '\', \''
						. $videoCategory . '\', \''
						. addslashes(implode(', ', $videoTags)) . '\', \''
						. $videoId . '\');" href="#">edit video data</a> | '
						. '<a href="#" onclick="ytVideoApp.confirmDeletion(\''
						. $videoId
						. '\');">delete this video</a></p><br clear="all">';
			}

		$table .= '</td></tr>';
		$results++;
		}

		if ($results < 1) {
			echo '<br />No results found<br /><br />';
		} else {
			echo $table .'</tbody></table><br />';
		}
	}
	
	/*
	 * Authentication for youtube
	 */
	static function authenticated(){
		if (isset($_SESSION['sessionToken'])) return true;
		return false;	
	}
	
	
	/**
	* Generate an AuthSub request Link and print it to the page.
	*
	* @param string $nextUrl URL to redirect to after performing the authentication.
	* @return void
	*/
	static function generateAuthSubRequestLink($nextUrl = null){
		$scope = 'http://gdata.youtube.com';
		$secure = false;
		$session = true;

		if (!$nextUrl) {
			self :: generateUrlInformation($_GET['page']);
			$nextUrl = $_SESSION['operationsUrl'];
		}

		$url = Zend_Gdata_AuthSub::getAuthSubTokenUri($nextUrl, $scope, $secure, $session);
		echo '<a href="' . $url
			. '"><strong>Click here to authenticate with YouTube</strong></a>';
	}
	
	/**
	* Store location of the demo application into session variables.
	*
	* @return void
	*/
	private static function generateUrlInformation($page){				
		$_SESSION['operationsUrl'] = get_option('siteurl') . '/wp-admin/admin.php?page=' . $page;
		$_SESSION['homeUrl'] = get_option('siteurl') . '/wp-admin/admin.php?page=' . $page;			
		
	}
	
	/**
	* Create upload form by sending the incoming video meta-data to youtube and
	* retrieving a new entry. Prints form HTML to page.
	*
	* @param string $VideoTitle The title for the video entry.
	* @param string $VideoDescription The description for the video entry.
	* @param string $VideoCategory The category for the video entry.
	* @param string $VideoTags The set of tags for the video entry (whitespace separated).
	* @param string $nextUrl (optional) The URL to redirect back to after form upload has completed.
	* @return void
	*/
	static function createUploadForm($videoTitle, $videoDescription, $videoCategory, $videoTags, $nextUrl = null){
		$httpClient = self::getAuthSubHttpClient();
		$youTubeService = new Zend_Gdata_YouTube($httpClient);
		$newVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

		$newVideoEntry->setVideoTitle($videoTitle);
		$newVideoEntry->setVideoDescription($videoDescription);

		//make sure first character in category is capitalized
		$videoCategory = strtoupper(substr($videoCategory, 0, 1))
			. substr($videoCategory, 1);
		$newVideoEntry->setVideoCategory($videoCategory);

		// convert videoTags from whitespace separated into comma separated
		$videoTagsArray = explode(' ', trim($videoTags));
		$newVideoEntry->setVideoTags(implode(', ', $videoTagsArray));

		$tokenHandlerUrl = 'http://gdata.youtube.com/action/GetUploadToken';
		try {
			$tokenArray = $youTubeService->getFormUploadToken($newVideoEntry, $tokenHandlerUrl);
			
		} catch (Zend_Gdata_App_HttpException $httpException) {
			print 'ERROR ' . $httpException->getMessage()
				. ' HTTP details<br /><textarea cols="100" rows="20">'
				. $httpException->getRawResponseBody()
				. '</textarea><br />'
				. '<a href="session_details.php">'
				. 'click here to view details of last request</a><br />';
			return;
		} catch (Zend_Gdata_App_Exception $e) {
			print 'ERROR - Could not retrieve token for syndicated upload. '
				. $e->getMessage()
				. '<br /><a href="session_details.php">'
				. 'click here to view details of last request</a><br />';
			return;
		}

		$tokenValue = $tokenArray['token'];
		$postUrl = $tokenArray['url'];

		// place to redirect user after upload
		if (!$nextUrl) {
			$nextUrl = $_SESSION['homeUrl'];
		}
		
		//include the upload form
		$action_url = $postUrl . '?nexturl=' . $nextUrl;
		include dirname(__FILE__) . '/video-management/upload-form.php';
	}
	
	/**
	* Convert HTTP status into normal text.
	*
	* @param number $status HTTP status received after posting syndicated upload
	* @param string $code Alphanumeric description of error
	* @param string $videoId (optional) Video id received back to which the status
	*        code refers to
	*/
	static function  uploadStatus(){
		(isset($_GET['code']) ? $code = $_GET['code'] : $code = null);
		(isset($_GET['id']) ? $videoId = $_GET['id'] : $videoId = null);
		
		switch ($_GET['status']){
			 case $_GET['status'] < 400:
				echo  '<div class="updated">Success ! Entry created (id: '. $videoId . ') <a href="#" class="ytVideoAppcheckUploadDetails" id="' .  $videoId . '">(check details)</a></p></div><div id="show_message"></div>';
				break;
			 default :
				echo '<div class="error"><p>There seems to have been an error: '. $code . '<a href="#" onclick="ytVideoApp.checkUploadDetails(\''. $videoId . '\'); ">(check details)</a></p></div><div id="show_message"></div>';
		}
	}
	
	/**
	* Check the upload status of a video
	*
	* @param string $videoId The video to check.
	* @return string A message about the video's status.
	*/
	static function checkUpload($videoId = null){
		$videoId = $_REQUEST['vid'];
		$httpClient = self::getAuthSubHttpClient();
		$youTubeService = new Zend_Gdata_YouTube($httpClient);

		$feed = $youTubeService->getuserUploads('default');
		$message = '<div class="error"><p>No further status information available yet.</p></div>';

		foreach($feed as $videoEntry) {
			if ($videoEntry->getVideoId() == $videoId) {
				// check if video is in draft status
				try {
					$control = $videoEntry->getControl();
				} catch (Zend_Gdata_App_Exception $e) {
					print 'ERROR - not able to retrieve control element '
						. $e->getMessage();
					exit;
				}

				if ($control instanceof Zend_Gdata_App_Extension_Control) {
					if (($control->getDraft() != null) &&
						($control->getDraft()->getText() == 'yes')) {
						$state = $videoEntry->getVideoState();
						if ($state instanceof Zend_Gdata_YouTube_Extension_State) {
							$message = '<div class="updated"><p>Upload status: ' . $state->getName() . ' '
								. $state->getText() . '</p></div>';
						} else {
							echo $message;
						}
					}
				}
			}
		}
		print $message;
		exit;
	}
	
	/**
	* Finds the URL for the flash representation of the specified video.
	*
	* @param Zend_Gdata_YouTube_VideoEntry $entry The video entry
	* @return (string|null) The URL or null, if the URL is not found
	*/
	static function findFlashUrl($entry){
		foreach ($entry->mediaGroup->content as $content) {
			if ($content->type === 'application/x-shockwave-flash') {
				return $content->url;
			}
		}
		return null;
	}
	
	/**
	* Deletes a Video.
	*activateAuthor is already called
	* @param string $videoId Id of the video to be deleted.
	* @return void
	*/
	static function deleteVideo($videoId, $httpClient, $youTubeService){
		
		// check if videoEntryToUpdate was found
		$videoEntryToDelete = $youTubeService->getFullVideoEntry($id);
		
		if (!$videoEntryToDelete instanceof Zend_Gdata_YouTube_VideoEntry) {
			return '<div class="error"><p>ERROR - Could not find a video entry with id ' . $videoId . '<p></div><br />';
			
		}
				
		
		try{
			$httpResponse = $youTubeService->delete($videoEntryToDelete);
		}
		catch (Zend_Gdata_App_Exception $e){
			return '<div class="error"><p>ERROR - Could not delete video: '. $e->getMessage() . '</p></div>';
			  
		}
		
		return '<div class="updated"><p>Entry deleted succesfully.<br />' . $httpResponse->getBody() . '</p></div>';
	}
}
