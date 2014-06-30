jQuery(document).ready(function($) {
	$( "#freeseat-dialog p" ).removeClass();
	$( "#freeseat-dialog" )
	.dialog({
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
				if (typeof freeseatPopupUrl != "undefined") { 
					$(location).attr('href',freeseatPopupUrl); 
				}
			}
		}	
	});
});
