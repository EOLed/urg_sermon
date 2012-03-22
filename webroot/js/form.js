function on_validate(dom_id, XMLHttpRequest, textStatus) {
    $("#loading-validate").hide();
    
    if ($(dom_id + "Error").text() == "") {
        $(dom_id + "Error").hide();
        $(dom_id).after($(dom_id + "Valid"));
        $(dom_id + "Valid").show();
        $(dom_id).removeClass("invalid");
    } else {
        $(dom_id + "Valid").hide();
        $(dom_id).after($(dom_id + "Error"));
        $(dom_id + "Error").show();
        $(dom_id).addClass("invalid");
    }
}

function loading_validate(dom_id) {
    $(dom_id).after($("#loading-validate"));
    $(dom_id + "Error").hide();
    $("#loading-validate").show();
}

