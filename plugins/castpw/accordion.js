jQuery(document).ready(function($) {
	$( "#accordion" ).accordion( { 
		header: "h4",
		collapsible: true,
		active: false,
		heightStyle: "content",
		icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" } 
	} );
});