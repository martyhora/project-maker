import './vendor'

$(function() {
    $.nette.init();

    $(".confirm-delete").click(function() {
        return confirm("Opravdu smazat záznam?");
    });
});