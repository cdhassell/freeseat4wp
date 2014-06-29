jQuery(document).ready(function($) {
	var l = $( "#freeseat-dialog" ).text().length;
	if( l > 0 ) {
		$( "p.warning" ).removeClass( "warning" );
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
	}
});
