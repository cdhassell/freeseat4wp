jQuery(document).ready(function($) {
	$( "p.warning" ).removeClass( "warning" );
	$( "#freeseat-dialog" )
	.dialog({
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
				$(location).attr('href',freeseatPopupUrl);
			}
		}	
	});
});
