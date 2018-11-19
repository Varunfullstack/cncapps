var $directDebit;

function beforeShowDay(date) {
    return [
        $directDebit[0].checked ? date.getDate() === 1 : true,
        "",
        null
    ];
}

$(function () {


    var invoiceInterval = $('#invoiceInterval');
    var autoGenerateContractInvoice = $('#autoGenerateContractInvoice');
    var installationDate = $('#installationDate');

    $directDebit = $('#directDebit');

    function processDirectDebitChecked() {
        invoiceInterval.val(1);
        invoiceInterval.attr('readonly', true);
        autoGenerateContractInvoice[0].checked = true;
        autoGenerateContractInvoice.attr('onclick', "return false;");
    }

    function processDirectDebitUnchecked() {
        invoiceInterval.attr('readonly', false);
        autoGenerateContractInvoice.attr('onclick', null);
    }

    if ($directDebit[0].checked) {
        processDirectDebitChecked();
    }


    $directDebit.change(function () {
        if ($directDebit[0].checked) {
            var installationDateValue = installationDate.val();

            if (!installationDateValue || (moment(installationDateValue, "DD/MM/YYYY")).date() !== 1) {
                alert('The Installation Date must be the first of the month when paying by Direct Debit.');
                $directDebit[0].checked = false;
                return;
            }

            processDirectDebitChecked();
        } else {
            processDirectDebitUnchecked();
        }
    })

});