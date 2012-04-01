<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>Upload Video</h2>
	<?php
		if(self::authenticated()){
			if($_POST['youtube-meta-data-submit'] == 'Y') {
				self :: createUploadForm($_POST['title'], $_POST['description'], $_POST['category'], $_POST['tags']);
			}
			else{
				if(isset($_GET['status'])){
					self :: uploadStatus();
				}
				include __DIR__ . '/meta-dataform.php';
			}
		}
		else{
			self :: generateAuthSubRequestLink();
		}
		
	?>
</div>