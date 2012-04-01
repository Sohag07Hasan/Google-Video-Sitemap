
jQuery(document).ready(function($){
	

	/** 
	* retrieve the upload status after uploading
	*/
	$('.ytVideoAppcheckUploadDetails').click(function(){
		videoId = $(this).attr('id');
				
		jQuery.ajax({
			type : 'post',
			url : YVHandler.ajaxurl,
			dataType : "html",
			cache : false,
			timeout : 10000,
			data: {
				'action' : 'get_upload_details',
				'vid' : videoId			
			},
			success:function(result){
				$('#show_message').html(result);	
			},

			error: function(jqXHR, textStatus, errorThrown){
				jQuery('#footer').html(textStatus);
				alert(textStatus);
				return false;
			}
		});
	});
		
	return false;

});