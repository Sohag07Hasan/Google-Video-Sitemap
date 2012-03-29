
<html>
<head>
    <script type="text/javascript" src="<?php echo  $_GET['base'] . 'js/flowplayer-3.2.6.js' ;?>"></script>
</head> 

<body>
<?php if (!empty($_GET['media'])) { ?>
	<a href="<?php echo  $_GET['media']; ?>" style="display:block;width:640px;height:380px"  id="player"></a> 
	
	<script>		
		flowplayer("player", "<?php echo $_GET['base'] . 'player/flowplayer-3.2.7.swf' ?>", {
		    clip:  {
		        autoPlay: false,
		        autoBuffering: false,
		        bufferLength: 5
		    }			
		});
	</script>
<?php } else { ?>
		<p>Media not found</p>
<?php } ?>	
</body>
</html>
