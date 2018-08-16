$(function () {
    $(".dragUploader").change(function (e) {
        var destination = $(this).data().description;
        $('input[name="' + destination + '"]').val(e.target.files[0].name);
    })
})