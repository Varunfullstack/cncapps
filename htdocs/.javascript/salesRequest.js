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

function sendSalesRequest() {
    var object = {
        message: CKEDITOR.instances.salesRequestText.getData(),
        type: $('#templateSelector').val()
    };

    $.ajax({
        url: 'Activity.php?action=sendSalesRequest&problemID=' + window.salesRequest.problemID,
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
    window.salesRequest.dialogTemplate.dialog('close');
}

function startSalesRequest(problemID) {
    if (!window.salesRequest) {
        window.salesRequest = {};
    }
    window.salesRequest.problemID = problemID;

    if (!window.salesRequest.dialogTemplate) {
        window.salesRequest.dialogTemplate = $(salesRequestDialogTemplate).dialog({autoOpen: true, width: 910});
        CKEDITOR.replace('salesRequestText', {customConfig: '/ckeditor_config.js'});
    } else {
        window.salesRequest.dialogTemplate.dialog('open');
    }

    // we need to pull the available templates


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