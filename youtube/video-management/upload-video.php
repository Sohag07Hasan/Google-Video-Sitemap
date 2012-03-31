<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>Upload Video</h2>
	<?php
		if(self::authenticated()){
			echo '<p>Authenticated and you are now eligible to upload video to youtube</p>';
		}
		else{
			self :: generateAuthSubRequestLink();
		}
		
	?>
</div>