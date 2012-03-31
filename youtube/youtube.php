<?php
/*
 * Main class to control youtube functionalilty
 */

// setting the include path
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
	}
	
	/*
	 * Creating a menu to show the youtube
	 */
	static function youtube_video_menu(){
		// Main side bar entry
		add_menu_page('Youtube Video', 'Youtube Video', 'manage_options', 'youtube-video', array(get_class(), 'youtube_video'));
	
		//child pages
		add_submenu_page('youtube-video', __('Upload Video','upload-video'), __('Upload Video','upload-video'), 'manage_options', 'youtube_video_upload_video', array(get_class(), 'youtube_video_upload_video'));		
		
	}
	
	/*
	 * existing youtube menu
	 */
	static function youtube_video(){
		$youTubeService = new Zend_Gdata_YouTube();
		$feed = $youTubeService->getUserUploads('sohaghyde');
		
		include VideoSitemapYT . '/video-management/existing-videos.php';
		
		
	}
	
	/*
	 * video uploading page
	 */
	static function youtube_video_upload_video(){
				
		self :: activateAuthentication();			
		include VideoSitemapYT . '/video-management/upload-video.php';
		
		var_dump($_SESSION);
	
		
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
				
		if (isset($_GET['token']) && !empty($_GET['token'])){
								
			self :: updateAuthSubToken($_GET['token']);
		}
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
		$_SESSION['developerKey'] = 'AI39si5V2LjrZo5LQwiM_MysYSHPnR0Xdazts5FNvTWroyzHjZusK4dd0dTJmJcxu_ew36qQ_R9ys-xLMQFm7DfNhudIdM-b9w';		
		$_SESSION['operationsUrl'] = get_option('siteurl') . '/wp-admin/admin.php?page=' . $page;
		$_SESSION['homeUrl'] = get_option('siteurl') . '/wp-admin/admin.php?page=' . $page;			
		
	}	
	
}
