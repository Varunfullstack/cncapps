$(function () {
    window.reAttachTimePickers = function () {
        attachTimePickers();
    };

    function attachTimePickers() {
        $('.timePicker').each(function () {

            const options = {};

            if ($(this).data().timepickerFormat) {
                options.timeFormat = $(this).data().timepickerFormat
            }

            if ($(this).data().timepickerMinTime) {
                options.minTime = $(this).data().timepickerMinTime;
            }

            if ($(this).data().timepickerMaxTime) {
                options.maxTime = $(this).data().timepickerMaxTime;
            }

            $(this).timepicker(options);
        })
    }

    attachTimePickers();
});