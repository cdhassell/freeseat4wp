jQuery(document).ready(function ($){
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
});
