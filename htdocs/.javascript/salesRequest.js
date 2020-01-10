;(function (window) {


    function appendScript(filepath) {
        if ($('head script[src="' + filepath + '"]').length > 0)
            return;

        var ele = document.createElement('script');
        ele.setAttribute("type", "text/javascript");
        ele.setAttribute("src", filepath);
        $('head').append(ele);
    }

    // language=HTML
    const salesRequestDialogTemplate = `
        <div id="salesRequestDialog"
             title="Sales Request"
        >
            <label for="templateSelector"></label>
            <select name="templateSelector"
                    id="templateSelector"
            >
                <option value=""> Pick a template</option>
            </select>
            <br>
            <br>
            <textarea id="salesRequestText"
                      cols="30"
                      rows="10"
            ></textarea>
            <br>
            <br>
            <div id='uploaderSection'
                 style='display: none'
            >This is the upload section!
                <div id="drop-area">
                    <form class="my-form"><p>Upload multiple files with the file dialog or by dragging and dropping
                        images onto the dashed region</p>
                        <input type="file"
                               id="fileElem"
                               multiple
                        > <label class="button"
                                 for="fileElem"
                        >Select some files</label></form>
                </div>
                <div id="gallery"></div>
            </div>
            <div>
                <button disabled
                        id="sendSalesRequestBtn"
                >Send
                </button>
                <button id="salesRequestCancelBtn">Cancel</button>
            </div>
        </div>`;

    const fileItem =
        `<div class="item">
     <div class="fileName">{% name %}</div>
     <button data-file-id="{% id %}" class="deleteFileItemBtn">X</button>    
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

    window.startCreateSalesRequest = function (customerID, domElement) {
        initializeSalesRequest();
        appendScript('.javascript/mustache.min.js');
        appendScript('.javascript/mustache-wax.min.js');
        Mustache.tags = ['{%', '%}'];
        salesRequest.customerID = customerID;
        domElement.innerHTML = salesRequestDialogTemplate;
        hookListeners();
        hookCKEditor();

        initializeUploads();
        populateOptions();
    };

    function hookListeners() {
        window.document.getElementById('templateSelector').addEventListener('change', changeTemplate);
        window.document.getElementById('salesRequestCancelBtn').addEventListener('click', cancelSalesRequest);
        window.document.getElementById('sendSalesRequestBtn').addEventListener('click', sendSalesRequest);
        const fileElement = window.document.getElementById('fileElem');
        fileElement.addEventListener('change', () => handleFiles(fileElement.files));
        window.document.getElementById('gallery').addEventListener('click', (mouseEvent) => {
            if (mouseEvent.target.classList.contains('deleteFileItemBtn')) {
                deleteFile(+mouseEvent.target.dataset.fileId);
            }
        });
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

            function highlight() {
                dropArea.classList.add('highlight')
            }

            function unhighlight() {
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
        if (window.salesRequest.files) {
            window.salesRequest.files.forEach(file => {
                fd.append("file[]", file);
            });
        }

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
            if (result.status === 'error') {
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
                    acc += "<option value='" + item.id + "' data-template='" + item.template + "' >" + item.name + "</option>";
                    return acc;
                }, '')
            )

        });
    }

    function hookCKEditor() {
        CKEDITOR.replace('salesRequestText', {customConfig: '/ckeditor_config.js'});
    }

    window.startSalesRequest = function (problemID, domElement = null) {
        initializeSalesRequest();
        window.salesRequest.problemID = problemID;

        if (!window.salesRequest.dialogTemplate) {
            window.salesRequest.dialogTemplate = $(salesRequestDialogTemplate).dialog({autoOpen: true, width: 910});
            hookCKEditor();
            hookListeners();
        } else {
            window.salesRequest.dialogTemplate.dialog('open');
        }

        // we need to pull the available templates
        populateOptions();
    }
})(window);