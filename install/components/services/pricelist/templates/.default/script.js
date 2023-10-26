$(document).ready(function () {
    let plus = $('.plus');

    plus.each(function () {
        item($(this));
    });
    function item(item) {
        item.on("click", function () {
            item.parent().next().toggleClass("active")
        });
    }
});