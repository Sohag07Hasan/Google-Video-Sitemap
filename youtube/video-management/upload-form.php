
<h3>Upload Form</h3>

<form enctype="multipart/form-data" id="videoUpload" method="post" action="<?php echo $action_url ;?>" >
	<input type="hidden" name="youtube-video-file-submit" value="Y" />
	 <input name="token" type="hidden" value="<?php echo $tokenValue ?>"/>
	<table class="form-table">
		<tr>
			<td>File format</td>
		</tr>
		<tr>
			<th scope="row">Select your Video:</th>
			<td>
				<label for="title">
					<input  type="file" name="youtube_file" />
				</label>
				<input type="submit" value="Upload" />
			</td>
		</tr>
				
	</table>
</form>
