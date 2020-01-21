;(function (window) {
    function appendScript(filepath) {
        if ($('head script[src="' + filepath + '"]').length > 0)
            return;

        var ele = document.createElement('script');
        ele.setAttribute("type", "text/javascript");
        ele.setAttribute("src", filepath);
        $('head').append(ele);
    }

    const changeRequestDialogTemplate = `
<div id="changeRequestDialog"    title="change Request">
<div>
    <label for="templateSelector"></label>
    <select name="templateSelector"
            id="templateSelector"
    >
        <option value="">
            Pick a template
        </option>
    </select>
    <br>
    <br>
    <textarea id="changeRequestText"
              cols="30"
              rows="10"
    ></textarea>
    <br>
    <br>
   </div>
  <div>
        <button disabled
                id="sendChangeRequestBtn"
        >Send
        </button>
        <button id='cancelBtn'>Cancel</button>
    </div>
    </div>
`;

    function changeTemplate() {
        var html = "";
        var sendChangeRequestBtn = document.getElementById("sendChangeRequestBtn");
        sendChangeRequestBtn.setAttribute('disabled', true);
        if (event.target.value) {
            sendChangeRequestBtn.removeAttribute('disabled');
            html = $('#templateSelector').find(":selected").data().template;
        }
        CKEDITOR.instances.changeRequestText.setData(html);
    }

    function hookListeners() {
        window.document.getElementById('cancelBtn').addEventListener('click', cancelChangeRequest);
        window.document.getElementById('sendChangeRequestBtn').addEventListener('click', sendChangeRequest)
        window.document.getElementById('templateSelector').addEventListener('change', changeTemplate);
    }

    function sendChangeRequest() {
        const fd = new FormData();
        if (window.changeRequest.files) {
            window.changeRequest.files.forEach(file => {
                fd.append("file[]", file);
            });
        }
        fd.append('message', CKEDITOR.instances.changeRequestText.getData());
        fd.append('type', $('#templateSelector').val());

        let URL = 'Activity.php?action=sendChangeRequest&problemID=' + window.changeRequest.problemID;
        $.ajax({
            url: URL,
            method: 'POST',
            type: 'post',
            dataType: 'json',
            contentType: false,
            processData: false,
            data: fd
        }).then(function (result) {
            if (result.status == 'error') {
                throw 'Failed to send message';
            } else {
                cancelChangeRequest();
                alert('Submitted OK');
            }
        }).catch(function () {
            alert('Failed to send message');
        });
    }

    function cancelChangeRequest() {
        $('#templateSelector').val("");
        CKEDITOR.instances.changeRequestText.setData("");
        window.changeRequest.files = [];
        if (window.changeRequest.dialogTemplate) {
            window.changeRequest.dialogTemplate.dialog('close');
        }
        if (window.changeRequest.onCancel) {
            window.changeRequest.onCancel();
        }

    }

    function initializeChangeRequest() {
        if (!window.changeRequest) {
            window.changeRequest = {};
        }
    }

    function populateOptions() {
        $.ajax({
            url: 'StandardText.php?action=getChangeRequestOptions',
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
        CKEDITOR.replace('changeRequestText', {customConfig: '/ckeditor_config.js'});
    }

    window.startChangeRequest = function (problemID, domElement = null) {
        initializeChangeRequest();
        window.changeRequest.problemID = problemID;

        if (!window.changeRequest.dialogTemplate) {
            window.changeRequest.dialogTemplate = $(changeRequestDialogTemplate).dialog({autoOpen: true, width: 910});
            hookCKEditor();
            hookListeners();
        } else {
            window.changeRequest.dialogTemplate.dialog('open');
        }

        // we need to pull the available templates
        populateOptions();
    }
})(window);

