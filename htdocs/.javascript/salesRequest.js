const salesRequestDialogTemplate = "<div id=\"salesRequestDialog\"\n" +
    "     title=\"Sales Request\"\n" +
    ">\n" +
    "    <label for=\"templateSelector\"></label>\n" +
    "    <select name=\"templateSelector\"\n" +
    "            id=\"templateSelector\"\n" +
    "            onchange=\"changeTemplate()\"\n" +
    "    >\n" +
    "        <option value=\"\">\n" +
    "            Pick a template\n" +
    "        </option>\n" +
    "    </select>\n" +
    "    <br>\n" +
    "    <br>\n" +
    "    <textarea id=\"salesRequestText\"\n" +
    "              cols=\"30\"\n" +
    "              rows=\"10\"\n" +
    "    ></textarea>\n" +
    "    <br>\n" +
    "    <br>\n" +
    "    <div>\n" +
    "        <button disabled\n" +
    "                id=\"sendSalesRequestBtn\"\n" +
    "                onclick=\"sendSalesRequest()\"\n" +
    "        >Send\n" +
    "        </button>\n" +
    "        <button onclick=\"cancelSalesRequest()\">Cancel</button>\n" +
    "    </div>\n" +
    "</div>";

function changeTemplate() {
    var html = "";
    var sendSalesRequestBtn = document.getElementById("sendSalesRequestBtn");
    sendSalesRequestBtn.setAttribute('disabled', true);
    if (event.target.value) {
        sendSalesRequestBtn.removeAttribute('disabled');
        html = $('#templateSelector').find(":selected").data().template;
    }
    CKEDITOR.instances.salesRequestText.setData(html);
}

function startCreateSalesRequest(customerID, domElement) {
    initializeSalesRequest();
    salesRequest.customerID = customerID;
    domElement.innerHTML = salesRequestDialogTemplate;
    hookCKEditor();
    populateOptions();
}

function sendSalesRequest() {
    var object = {
        message: CKEDITOR.instances.salesRequestText.getData(),
        type: $('#templateSelector').val()
    };

    let URL = 'Activity.php?action=sendSalesRequest&problemID=' + window.salesRequest.problemID;
    if (window.salesRequest.customerID) {
        URL = 'CreateSalesRequest.php?action=createSalesRequest&customerID=' + window.salesRequest.customerID;
    }

    $.ajax({
        url: URL,
        method: 'POST',
        type: 'post',
        dataType: 'json',
        data: object
    }).then(function (result) {
        if (result.status == 'error') {
            throw 'Failed to send message';
        } else {
            cancelSalesRequest();
            alert('Submitted OK');
        }
    }).catch(function () {
        alert('Failed to send message');
    });
}

function cancelSalesRequest() {
    $('#templateSelector').val("");
    CKEDITOR.instances.salesRequestText.setData("");
    if (window.salesRequest.dialogTemplate) {
        window.salesRequest.dialogTemplate.dialog('close');
    }
    if (window.salesRequest.onCancel) {
        window.salesRequest.onCancel();
    }

}

function initializeSalesRequest() {
    if (!window.salesRequest) {
        window.salesRequest = {};
    }
}

function populateOptions() {
    $.ajax({
        url: 'StandardText.php?action=getSalesRequestOptions',
        method: 'get',
        dataType: 'json'
    }).then(function (result) {
        $('#templateSelector').html('');

        $('#templateSelector').html(
            "<option value>-- Pick an option --</option>" +
            result.reduce((acc, item) => {
                acc += "<option value='" + item.id + "' data-template='" + item.template + "' >" + item.name + "</option>"
                return acc;
            }, '')
        )

    });
}

function hookCKEditor() {
    CKEDITOR.replace('salesRequestText', {customConfig: '/ckeditor_config.js'});
}

function startSalesRequest(problemID, domElement = null) {
    initializeSalesRequest();
    window.salesRequest.problemID = problemID;

    if (!window.salesRequest.dialogTemplate) {
        window.salesRequest.dialogTemplate = $(salesRequestDialogTemplate).dialog({autoOpen: true, width: 910});
        hookCKEditor();
    } else {
        window.salesRequest.dialogTemplate.dialog('open');
    }

    // we need to pull the available templates
    populateOptions();
}