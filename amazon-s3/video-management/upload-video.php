<script type="text/javascript">
	jQuery(function() {
	  jQuery("#videoUpload").validate({
		errorLabelContainer: jQuery("#validationError"),
		messages: {
			upload_video: {
				required: 'You need to select a video to upload<br>'
			}
		}			
	  });
		
	  jQuery(':input[placeholder]').placeholder();

	});
</script>

<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>Upload Video</h2>
	
		<p>Upload an .flv or mp4 file with the form below to your S3 bucket.</p>

		<form method="POST" id="videoUpload" enctype="multipart/form-data">
			<?php if (!empty($errorMsg)) { ?>
				<div id="validationError">
					<?php echo $errorMsg; ?>
				</div>
			<?php } else { ?>
				<div id="validationError"></div>					
			<?php } ?>

			<?php if (!empty($successMsg)) { ?>
				<div id="successMsg">
					<?php echo $successMsg; ?>
				</div>
			<?php } ?>

			<table>
				<tr>
					<th scope="row">Bucket</th>
					<td>
						<label for="bucket">
							<select name="bucket" class="required">
								<option value="video-sitemap">video sitemap</option>
							</select>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Video File</th>
					<td>
						<label for="upload_image">
							<input type="file" id="upload_video" name="upload_video" class="required" />
							<input type="submit" value="Upload Video">
						</label>
					</td>
				</tr>
			</table>
		</form>


</div> 
