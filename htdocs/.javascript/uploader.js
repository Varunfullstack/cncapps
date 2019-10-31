window.dragUploaderConfig = {
    maxUploadedFiles: 10,
    maxFileSizeMB: 10,
};
$(function () {
    function clearInput(input) {
        input.value = null;
    }

    $(".dragUploader").change(function (e) {
        var destination = $(this).data().description;
        if (e.target.files.length > window.dragUploaderConfig.maxUploadedFiles) {
            clearInput(e.target);
            alert(`You cannot upload more than ${window.dragUploaderConfig.maxUploadedFiles} files at a time`);
            return;
        }
        for (let i = 0; i < e.target.files.length; i++) {
            const file = e.target.files[i];

            if (file.size > window.dragUploaderConfig.maxFileSizeMB * 1024 * 1024) {
                clearInput(e.target);
                alert(`You cannot upload files larger than ${window.dragUploaderConfig.maxFileSizeMB}MB:  ${file.name}`);
                return;
            }
        }
        if (destination) {
            $('input[name="' + destination + '"]').val(e.target.files[0].name);
        }
    })
});