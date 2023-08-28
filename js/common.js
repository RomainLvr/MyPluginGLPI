$(function() {
    doStuff();
    $(".glpi_tabs").on("tabsload", function(event, ui) {
        doStuff();
    });
});

var doStuff = function()
{
    $dropdown = $(".dropdown-menu-end");

    if(! $dropdown.hasClass("stuff-added")) {
        $dropdown.addClass("stuff-added");
        $dropdown.prepend("<a href='/training/public/front/config.form.php' class='fas fa-face-smile' style='font-size: 1.5em; margin-left: 10px;'></a>");
    }
}