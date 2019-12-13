function appendScript(filepath) {
    if ($('head script[src="' + filepath + '"]').length > 0)
        return;

    var ele = document.createElement('script');
    ele.setAttribute("type", "text/javascript");
    ele.setAttribute("src", filepath);
    $('head').append(ele);
}

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
    "    <br>" +
    "<div id='uploaderSection' style='display: none'>" +
    "This is the upload section!" +
    "<div id=\"drop-area\">\n" +
    "  <form class=\"my-form\">\n" +
    "    <p>Upload multiple files with the file dialog or by dragging and dropping images onto the dashed region</p>\n" +
    "    <input type=\"file\" id=\"fileElem\" multiple onchange=\"handleFiles(this.files)\">\n" +
    "    <label class=\"button\" for=\"fileElem\">Select some files</label>\n" +
    "  </form>\n" +
    "</div>" +
    "<div id=\"gallery\"></div>" +
    "</div>" +
    "    <div>\n" +
    "        <button disabled\n" +
    "                id=\"sendSalesRequestBtn\"\n" +
    "                onclick=\"sendSalesRequest()\"\n" +
    "        >Send\n" +
    "        </button>\n" +
    "        <button onclick=\"cancelSalesRequest()\">Cancel</button>\n" +
    "    </div>\n" +
    "</div>" +
    "<style>" +
    "#drop-area {\n" +
    "  border: 2px dashed #ccc;\n" +
    "  border-radius: 20px;\n" +
    "  width: 480px;\n" +
    "  font-family: sans-serif;\n" +
    "  margin: 35px auto;\n" +
    "  padding: 20px;\n" +
    "}\n" +
    "#drop-area.highlight {\n" +
    "  border-color: purple;\n" +
    "}\n" +
    "p {\n" +
    "  margin-top: 0;\n" +
    "}\n" +
    ".my-form {\n" +
    "  margin-bottom: 10px;\n" +
    "}\n" +
    "#gallery {margin-top: 10px;display: flex; flex-direction: column;}" +
    ".button {\n" +
    "  display: inline-block;\n" +
    "  padding: 10px;\n" +
    "  background: #ccc;\n" +
    "  cursor: pointer;\n" +
    "  border-radius: 5px;\n" +
    "  border: 1px solid #ccc;\n" +
    "}\n" +
    ".button:hover {\n" +
    "  background: #ddd;\n" +
    "}\n" +
    "#fileElem {\n" +
    "  display: none;\n" +
    "}" +
    ".item{" +
    " width:250px;display: flex;flex-direction: row; " +
    "}" +
    ".item .fileName{ flex-grow: 1; margin: auto}" +
    ".item button {flex-grow: 0}" +
    "</style>";

const fileItem =
    `<div class="item">
     <div class="fileName">{% name %}</div>
     <button onclick="deleteFile({% id %})">X</button>    
    </div>
   `;

function deleteFile(idx) {
    window.salesRequest.files = window.salesRequest.files.filter((item, itemIdx) => idx !== itemIdx);
    drawFiles();
}

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
    appendScript('.javascript/mustache.min.js');
    appendScript('.javascript/mustache-wax.min.js');
    Mustache.tags = ['{%', '%}'];
    salesRequest.customerID = customerID;
    domElement.innerHTML = salesRequestDialogTemplate;
    hookCKEditor();
    initializeUploads();
    populateOptions();
}

function initializeUploads() {
    if (salesRequest.customerID) {
        $('#uploaderSection').show();
        let dropArea = document.getElementById('drop-area');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false)
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false)
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false)
        });

        function highlight(e) {
            dropArea.classList.add('highlight')
        }

        function unhighlight(e) {
            dropArea.classList.remove('highlight')
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;

            handleFiles(files)
        }
    }
}

function handleFiles(files) {
    window.salesRequest.files = [...files];
    drawFiles();
}

function drawFiles() {
    $('#gallery').html('');
    window.salesRequest.files.forEach((file, id) => {
        const context = {id, name: file.name};
        const html = Mustache.to_html(fileItem, context);
        $('#gallery').append(html);
    })
}

function sendSalesRequest() {

    const fd = new FormData();
    window.salesRequest.files.forEach(file => {
        fd.append("file[]", file);
    });

    fd.append('message', CKEDITOR.instances.salesRequestText.getData());
    fd.append('type', $('#templateSelector').val());

    let URL = 'Activity.php?action=sendSalesRequest&problemID=' + window.salesRequest.problemID;
    if (window.salesRequest.customerID) {
        URL = 'CreateSalesRequest.php?action=createSalesRequest&customerID=' + window.salesRequest.customerID;
    }

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
    window.salesRequest.files = [];
    drawFiles();
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