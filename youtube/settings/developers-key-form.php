<div class="wrap">
	<?php screen_icon('tools');?>
	<h2> Youtube Developer Key </h2>
	<?php
		if($_POST['youtube-devloper-key-submit'] == 'Y') :
			update_option('site-map-youtube-devkey', trim(strip_tags($_POST['youtube-developer-key'])));
			echo "<div class='updated'><p>developer key updated</p></div>";
		endif;
	?>	
	<form action='' method="post">
		<input type="hidden" name="youtube-devloper-key-submit" value="Y" />
		<table class="form-table">
			<tr>
				<td colspan="2">If you have no developer key, <a target="_blank" href="http://code.google.com/apis/youtube/dashboard">Click</a></td>
			</tr>
			<tr>
				<th>youtube Developer key</th>
				<td colspan="3"><input style="width: 732px" type="text" name="youtube-developer-key" value="<?php echo trim(get_option('site-map-youtube-devkey')); ?>" /></td>
			</tr>
			<tr>
				<td><input type="submit"  value="update" class="button-primary" /></td>
			</tr>
		</table>
	</form>
</div>