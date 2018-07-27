$(function () {
    $('.jqueryCalendar').each(function () {
        $(this).datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                first: 1
            }
        );
    })
})