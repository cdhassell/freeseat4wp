jQuery(document).ready(function($) {
	$( "#freeseat-dialog" ).css('border-style', 'none' )
	.dialog({
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
				var path = $(this).data('link').href; // Get the stored result
				$(location).attr('href', path);
			}
		}	
	});
});
