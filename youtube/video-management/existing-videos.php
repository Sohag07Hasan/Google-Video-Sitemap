<script type="text/javascript">
	jQuery(function() {	  
	  jQuery("#videoListTable").tablesorter();
	  jQuery("#videoListTable").paginateTable({ rowsPerPage: 20});	  
	  jQuery(".colorBox").colorbox();
	  	  
	  jQuery("a#getShortLink").click(function() {
		var videoFile = jQuery(this).attr("title"); 
		var linkText = '<h2>Wordpress Shortcode</h2><p>Copy and paste the following shortcode into the page or post where you would like to embed your video: </p><br>';
		var shortLink = '<p>[Youtube_embed_video src="http://www.youtube.com/embed/' + videoFile + '" width="500" height="395" class=""]</p>';
		jQuery("#videoInfo").html(linkText + shortLink + '<br>');
		jQuery().colorbox({width:"50%", inline:true, href:"#videoInfo"});
	  });	  
	 
	});
</script>

<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>Existing Youtube Videos</h2>
	
	<?php
		if($del_msg){
			echo $del_msg;
		}
	?>
	
	<table id="videoListTable" class="tablesorter" cellspacing="0" >
		<thead>
				<tr>
					<th>File Name</th>
					<th>Category</th>
					<th>Tags</th>
					<th>View Count</th>				
					<th>Actions</th>								
				</tr>
			</thead>
			
			<tbody>
				<?php
					$ypage = get_option('siteurl') . '/wp-admin/admin.php?page=youtube-video';
					foreach ($feed as $entry){
						$url = VideoSitemapURL . '/youtube/video-management/preview-video.php?';
						$url .= 'title=' . urlencode(htmlspecialchars($entry->getVideoTitle()));
						$url .= '&url=' . htmlspecialchars(self::findFlashUrl($entry));
						$delete_url = $ypage . '&action=d&vid=' . $entry->getVideoId();
						
					?>
						<tr>
							<td><?php echo htmlspecialchars($entry->getVideoTitle());?></td>
							<td><?php echo htmlspecialchars($entry->getVideoCategory()); ?></td>
							<td><?php echo implode(', ', $entry->getVideoTags()); ?></td>
							<td><?php echo $entry->getVideoViewCount(); ?></td>
							<td>
								<a class="colorBox" title="<?php echo htmlspecialchars($entry->getVideoTitle()); ?>" href=<?php echo $url; ?> id="<?php echo  $entry->getVideoId();  ?>" >Preview</a>
								<a href="#<?php //echo $entry->getEditLink()->getHref();?>">Edit</a>
								<a href="<?php echo $delete_url; ?>">Delete</a>
								<a href="#" title="<?php echo $entry->getVideoId(); ?>" id="getShortLink">
								Get Shortlink</a>															
							</td>
						</tr>
					<?php
					}
				?>
			</tbody>
						
	</table>
	<div align="center">
		<div class='pager'>
			<a href='#' alt='Previous' class='prevPage'>Prev</a> - 
				Page <span class='currentPage'></span> of <span class='totalPages'></span>
				- <a href='#' alt='Next' class='nextPage'>Next</a>
			<br>
			<span class='pageNumbers'></span>
		</div>
	</div>
	
	<div style='display:none'>
		<div id='videoInfo' style='padding:10px;'></div>
	</div>
	
</div>
