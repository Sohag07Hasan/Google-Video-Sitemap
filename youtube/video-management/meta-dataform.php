<p><strong>Submit the meta data of the video file to get the Upload Form</strong></p>

<form id="videoUpload" method="post" action="<?php echo $_SESSION['homeUrl'];?>" >
	<input type="hidden" name="youtube-meta-data-submit" value="Y" />
	<table class="form-table">
		<tr>
			<th scope="row"><em>*</em> Video Title:</th>
			<td>
				<label for="title">
					<input style="width:760px" type="text" name="title" value="" />
				</label>
			</td>
		</tr>
		
		<tr>
			<th scope="row">Video Description:</th>
			<td>
				<label for="description">
					<textarea name="description" class="required" rows="3" cols="90"></textarea>
				</label>
			</td>
		</tr>
		
		<tr>
			<th scope="row">Video Category:</th>
			<td>
				<label for ="category">
					<select name="category">
						<option value="Autos">Autos &amp; Vehicles</option>
						<option value="Music">Music</option>
						<option value="Animals">Pets &amp; Animals</option>
						<option value="Sports">Sports</option>
						<option value="Travel">Travel &amp; Events</option>
						<option value="Games">Gadgets &amp; Games</option>
						<option value="Comedy">Comedy</option>
						<option value="People">People &amp; Blogs</option>
						<option value="News">News &amp; Politics</option>
						<option value="Entertainment">Entertainment</option>
						<option value="Education">Education</option>
						<option value="Howto">Howto &amp; Style</option>
						<option value="Nonprofit">Nonprofit &amp; Activism</option>
						<option value="Tech">Science &amp; Technology</option>
					</select>
				</label>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><em>*</em> Tags <span style="color:blue"> space ( ) separated </span></th>
			<td>
				<label for="keywords" >
					<input style="width:760px" type="text" name="tags" value="">
				</label>
			</td>
		</tr>
		
		
		<tr>
			<th scope="row"><input class="button-primary" type="submit" value="Get Video Uploader Form" ></th>
		</tr>
		
	</table>
</form>
