$(function () {
    $(".telephoneValidator").keypress(function (e) {
        const key = e.which;
        if (key >= 48 && key <= 57) // numbers
            return true;
        if (e.preventDefault) e.preventDefault(); //normal browsers
        e.returnValue = false; //IE
    })
});