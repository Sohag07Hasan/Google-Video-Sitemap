<html>

<body>
<?php if (!empty($_GET['url'])) { ?>
	
	
	<b><? echo $_GET['title']; ?></b><br />
	<object width="425" height="350">
			<param name="movie" value="<?php echo $_GET['url'] . '&autoplay=1' ;?>"></param>
			<param name="wmode" value="transparent"></param>
			<embed src="<?php echo $_GET['url'] . '&autoplay=1' ;?>" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed>
        </object>
	
	
<?php } else { ?>
		<p>Media not found</p>
<?php } ?>	
</body>
</html>
