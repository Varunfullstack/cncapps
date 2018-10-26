$(function () {
    $('.jqueryCalendar').each(function () {
        var beforeShowDayFunction = null;
        if ($(this).data().jquerycalendarbeforeshowday) {
            beforeShowDayFunction = $(this).data().jquerycalendarbeforeshowday;
        }


        $(this).datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                firstDay: 1,
                beforeShowDay: window[beforeShowDayFunction]
            }
        );
    })
})