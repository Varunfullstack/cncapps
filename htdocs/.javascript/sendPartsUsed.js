(function (callActivityID) {
    const sendPartsUsedToSales = function () {
        var object = {
            message: CKEDITOR.instances.partsUsedText.getData(),
            callActivityID: callActivityID
        };

        $.ajax({
            url: '/Activity.php?action=messageToSales',
            method: 'POST',
            type: 'post',
            dataType: 'json',
            data: object
        }).then(function (result) {
            if (result.status == 'error') {
                throw 'Failed to send message';
            } else {
                $('#partsUsedDialog').dialog('close');
                alert('Submitted OK');
            }
        }).catch(function () {
            alert('Failed to send message');
        });
    };

    const cancelPartsUsed = function () {
        CKEDITOR.instances.partsUsedText.setData('');
        $('#partsUsedDialog').dialog('close');
    };

    const container = document.createElement('div');
    container.id = 'partsUsedDialog';
    container.title = "Parts Used";
    container.innerHTML = `
    <textarea id="partsUsedText"
              cols="30"
              rows="10"
    ></textarea>
    <br>
    <div>
        <button id="partsUsedSendButton">Send</button>
        <button id="partsUsedCancelButton">Cancel</button>
    </div>`;
    $(function () {

        document.body.append(container);
        document.getElementById('partsUsedSendButton').addEventListener('click', () => {
            sendPartsUsedToSales();
        });
        document.getElementById('partsUsedCancelButton').addEventListener('click', () => {
            cancelPartsUsed();
        });
        $('#partsUsedDialog').dialog({autoOpen: false, width: 910});

        CKEDITOR.replace('partsUsedText', {
                customConfig: '/ckeditor_config.js'
            }
        );

        $('body').on('click', '.partsUsedButton', () => {
            showPartsUsedDialog();
        })

    });
    const showPartsUsedDialog = function () {
        $('#partsUsedDialog').dialog('open');
    }


})(callActivityID);