jQuery(document).ready(function ($){
	/* used for the autocomplete search in the login plugin */
    var acs_action = 'freeseat_namesearch_action';
    $("#namesearchInput").autocomplete({
        source: function(req, response){
            $.getJSON(namesearchObject.ajaxurl+'?callback=?&action='+acs_action, req, response);
        },
        select: function(event, ui) {
            window.location.href=ui.item.link;
        },
        minLength: 2,
    });
    
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

});
