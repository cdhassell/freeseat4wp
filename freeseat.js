jQuery(document).ready(function ($){
    /* used for the popup dialog box for system messages */
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
	
	/* used for the tooltips on the seatmap */
	$('#multiCheck').tooltip();
	
	/* used for the accordion-type dropdowns for help text */	
	$( "#accordion" ).accordion( { 
		header: "h4",
		collapsible: true,
		active: false,
		heightStyle: "content",
		icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" } 
	} );
	
	/* used for setting the printer on the bookinglist page */	
	$( "#printercheck" ).click( function() {
		var printername = $(this).prop("name");
		var ifchecked = $(this).prop("checked");
		$( ".freeseat-print" ).each( function() {
			var theHref = $(this).attr("href");
			var theArg  = "&" + printername + "=1";
			if (ifchecked && theHref.indexOf(theArg) === -1) {
				$(this).attr("href", theHref + theArg);
			} else {
				theHref = theHref.replace(theArg, ""); 
				$(this).attr("href", theHref );
			}
		})
	} );
});
