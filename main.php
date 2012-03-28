<?php
/*
 * Plugin Name: Google Video Sitemap
 * Author: Mahibul Hasan Sohag
 * Plugin url: http://sohag.me
 * 
 */

//define('Video-Sitemap-Dir', ABSPATH . 'wp-content/plugins/Google-Video-Sitemap');
define('VideoSitemapDir', dirname(__FILE__));
define('VideoSitemapURL', plugins_url('', __FILE__));
define('Video-Sitemap-File', __FILE__);
define('VideoSitemapS3', VideoSitemapDir .'/amazon-s3');

//including classes
include VideoSitemapS3 . '/amazon-s3.php';
amazon_s3 :: init();

?>