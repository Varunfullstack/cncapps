$(function () {
    window.reAttachCalendars = function () {
        attachCalendars();
    };


    function attachCalendars() {

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
                    beforeShowDay: window[beforeShowDayFunction],
                    showOtherMonths: true,
                    selectOtherMonths: true,
                    onSelect: function (d, i) {
                        if (d !== i.lastVal) {
                            $(this).change();
                        }
                    }
                }
            );
        })
    }

    attachCalendars();
});