function checkExcel() {
    if ($('#file').val() == '') {
        alert('请先选择上传文件');
        return false;
    }
    $('#res').html('');
    var options = {
        success: function (txt) {
            var json = $.parseJSON(txt);
            $('#file').val('');
            if (json.status == 1) {
                $('#step2').show();
                $('#tmpFile').val(json.data);
                $('#res').html('<span class="cGreen">' + json.info + '</span>');
            } else {
                $('#step2').hide();
                $('#res').html('<span class="cRed">' + json.info + '</span>');
            }
        }
    };
    $('#check').ajaxSubmit(options);
}
function doit() {
    if ($('#tmpFile').val() == '') {
        alert('请先通过检查');
        return false;
    }
    if ($('#reason').val() == '') {
        alert('请填写发放理由');
        return false;
    }
    $('#res').html('');
    var options = {
        success: function (txt) {
            var json = $.parseJSON(txt);
            $('#step2').hide();
            if (json.status == 1) {
                $('#res').html(json.info);
                $('#tmpFile').val('');
                $('#file').val('');
            } else {
                $('#res').html('<span class="cRed">' + json.info + '</span>');
            }

        }
    };
    $('#doit').ajaxSubmit(options);
}