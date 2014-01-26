jQuery(document).ready(function($) {
	$( "#freeseat-dialog" )
	.dialog({
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
				var path = $( this ).data( 'link' ).href;	
				// $(location).attr('href', path);
			}
		}	
	});
});
