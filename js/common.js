$(function() {
    doStuff();
    $(".glpi_tabs").on("tabsload", function(event, ui) {
        doStuff();
    });
});

var doStuff = function()
{

    console.log("doStuff");
    if (! $("html").hasClass("stuff-added-test")) {
        $("html").addClass("stuff-added-test");
    }

    $dropdown = $(document).find("/html/body/div[2]/header/div/div[2]/div/div[1]/div/a");
    
    if(! $dropdown.hasClass("stuff-added")) {
        $dropdown.addClass("stuff-added");
        $dropdown.append("<a href='#' class='fas fa-face-smile'></a>");
    }
};