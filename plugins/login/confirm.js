jQuery(document).ready(function($) {
	$("#confirmDialog").dialog({
		autoOpen: false,
		modal: true
	});

	$(".confirmLink").click(function(e) {
		e.preventDefault();
		var targetUrl = $(this).attr("href");
	
		$("#confirmDialog").dialog({
			buttons : {
				"Confirm" : function() {
					window.location.href = targetUrl;
				},
				"Cancel" : function() {
					$(this).dialog("close");
				}
			}
		});
		$("#confirmDialog").dialog("open");
	});
});