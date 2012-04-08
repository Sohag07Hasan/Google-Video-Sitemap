<?php
/*
 * creates xml sitemap 
 */
 
class xml_site_map_creator{
	
	/*
	 * init function
	 * */
	 static function init(){
		add_action('admin_menu', array(get_class(), 'video_sitemap_generate_page'));
	 }
	 
	 /*
	  * Video sitemap generator generator page
	  * */
	  static function video_sitemap_generate_page(){
		  add_submenu_page ('tools.php', __('Video Sitemap'), __('Video Sitemap'),            'manage_options', 'video-sitemap-generate-page', array(get_class(), 'video_sitemap_generate'));
	  }
	  
	  /*
	   * Populates the sitemap page
	   * */
	   static function video_sitemap_generate(){
		   if($_POST['Generate_sitemap_creation'] == "Y") :
				$st = self::video_sitemap_loop();
		   endif;
			?>
			
			<div class="wrap">
				<h2>XML Sitemap for Videos</h2>
				<p>Sitemaps are a way to tell Google and other search engines about web pages, images and video content on your site that they may otherwise not discover. </p>
				<h3>Create Video Sitemap</h3>
				
				<form id="options_form" action="" method="post">
					<input type="hidden" name="Generate_sitemap_creation" value="Y" />
					<div class="submit">
						<input type="submit" name="submit" id="sb_submit" value="Generate Video Sitemap" />
					</div>
				</form>
				<p>
					You can click the button above to generate a Video Sitemap for your website. Once you have created your Sitemap, you should submit it to Google using Webmaster Tools. 
				</p>
				
			</div>
			
			<?php
	   }
	   
	   
	   /*
	    *creates the xml file 
	    * */
	    static function video_sitemap_loop(){
			global $wpdb;
			$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";       
			$xml .= '<!-- Created by (http://wordpress.org/extend/plugins/xml-sitemaps-for-videos/) -->' . "\n";
			$xml .= '<!-- Generated-on="' . date("F j, Y, g:i a") .'" -->' . "\n";             
			$xml .= '<?xml-stylesheet type="text/xsl" href="' . dirname(__FILE__) . '/video-sitemap.xsl' . '"?>' . "\n" ;        
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
			
			$youtube_posts = $wpdb->get_results ("SELECT id, post_title, post_content, post_date_gmt, post_excerpt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') AND post_content LIKE '%youtube.com%' ORDER BY post_date DESC");
			
			$s3_posts = $wpdb->get_results ("SELECT id, post_title, post_content, post_date_gmt, post_excerpt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') AND post_content LIKE '%s3.amazonaws.com%' ORDER BY post_date DESC");
			
			if(empty($youtube_posts) && empty($s3_posts)){
				return false;
			}
			
			if(!empty($youtube_posts)) :
				$videos = array();
				foreach($youtube_posts as $post) :
					 $c = 0;
					if (preg_match_all ("/youtube.com\/(v\/|watch\?v=|embed\/)([a-zA-Z0-9\-_]*)/", $post->post_content, $matches, PREG_SET_ORDER)) {					
						$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ; 
						$permalink = get_permalink($post->id);
						
						foreach($matches as $match){
							$id = $match [2];
							$fix =  $c++==0?'':' [Video '. $c .'] ';
							
							if (in_array($id, $videos)) continue;                            
							array_push($videos, $id);
							$xml .= "\n <url>\n";
							$xml .= " <loc>$permalink</loc>\n";
							$xml .= " <video:video>\n";
							$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">http://www.youtube.com/v/$id</video:player_loc>\n";
							$xml .= "  <video:thumbnail_loc>http://i.ytimg.com/vi/$id/hqdefault.jpg</video:thumbnail_loc>\n";
							$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
							$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";
							 $xml .= " </video:video>\n </url>";
						} 
					}
				endforeach;
			endif;
			
			if(!empty($s3_posts)) :
				$videos = array();
				foreach($s3_posts as $post) :
					$pattern = $p = '#(?<=file=")http://.[^(amazonaws)]*.[^" ]*#';
					if(preg_match_all($pattern, $post->post_content, $matches)){
						$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ; 
						$permalink = get_permalink($post->id);
						
						foreach($matches[0] as $match){
							if(in_array($match, $videos)) continue;
							array_push($videos, $match);
							
							$xml .= "\n <url>\n";
							$xml .= " <loc>$permalink</loc>\n";
							$xml .= " <video:video>\n";
							$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">$match</video:player_loc>\n";
							$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
							$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";
							 $xml .= " </video:video>\n </url>";
						}
					}
				endforeach;
			endif;
			
			$xml .= "\n</urlset>";			
			
			$video_sitemap_url = $_SERVER["DOCUMENT_ROOT"] . '/sitemap-video.xml';
			if (self::IsVideoSitemapWritable($_SERVER["DOCUMENT_ROOT"]) || self::IsVideoSitemapWritable($video_sitemap_url)) {
				if (file_put_contents ($video_sitemap_url, $xml)) {
					return true;
				}
			} 
			
		}
		
		/**
		 * Checks if a file is writable and tries to make it if not.
		 *
		 * @since 3.05b
		 * @access private
		 * @author  VJTD3 <http://www.VJTD3.com>
		 * @return bool true if writable
		 */
		static function IsVideoSitemapWritable($filename) {
			//can we write?
			if(!is_writable($filename)) {
				//no we can't.
				if(!@chmod($filename, 0666)) {
					$pathtofilename = dirname($filename);
					//Lets check if parent directory is writable.
					if(!is_writable($pathtofilename)) {
						//it's not writeable too.
						if(!@chmod($pathtoffilename, 0666)) {
							//darn couldn't fix up parrent directory this hosting is foobar.
							//Lets error because of the permissions problems.
							return false;
						}
					}
				}
			}
			//we can write, return 1/true/happy dance.
			return true;
		}
	    
	   
}
