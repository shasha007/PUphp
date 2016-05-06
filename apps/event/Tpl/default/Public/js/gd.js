$(function(){
    changeGd();
    $(":radio").click(function(){
        calcXf($(this).attr('i'),$(this).attr('j'));
    });
});
function calcXf(i,j){
    if(i!=jianban){
        $('#xf'+i).html(j);
    }
    var xf = 0;
    for(k=1;k<=selectNum;k++){
        xf = $('#xf'+k).html()-0+xf;
    }
    if($("input[name='gd"+jianban+"']:checked").val()==2){
        xf = xf/2;
    }
    $('#xf').html(xf);
}
function changeGd(){
    $.each($(':radio:checked'), function(i, n){
        calcXf($(n).attr('i'),$(n).attr('j'));
    });
}
var attachCount = 0;
var attachId = 0;
var maxAttach = 5;
function openUploadFile(){
    restCount = maxAttach-attachCount;
    if(restCount<=0){
        alert('最多上传'+maxAttach+'个文件');
    }else{
        ui.box.load(U('home/Public/uploadFile')+'&isDel=1&attach_type=ecapply&maxfile='+restCount,{title:'上传文件'});
    }
}

function ts_upload_success(serverData){
    var data=$.parseJSON(serverData);
    if(data.status==true){
        attachCount+=1;
        attachId+=1;
        var msg = '<div class="attach_span" id="attach'+attachId+'">';
        msg += '<input name="attach[]" type="hidden" value="'+data.attachId+'" />';
        msg += '<span class="attach_title">'+data.file+'</span><a onclick="delFile('+attachId+')" href="javascript:void(0)">删除</a></div>';
        $('#uploaded_files').append(msg);
        return true;
    }else{
        if(data == '0'){
            alert('上传失败，格式不允许');
        }else{
            alert(data.info);
        }
        isRedirect = false;
        return false;
    }
}
function delFile(id){
    $('#attach'+id).remove();
    attachCount-=1;
}
function checkEcApply(){
    var itype = document.myform.type.value;
    if(itype!=12){
        if (document.myform.title.value.length == 0) {
            alert('名称 不能为空');
            document.myform.title.focus();
            return false;
        }
    }
    if(itype==12){
        if (document.myform.description.value.length == 0) {
            alert('相关说明 不能为空');
            document.myform.description.focus();
            return false;
        }
    }
//    if(checkZs>0 && $('#zs').val()==''){
//        alert('请输入证书名称');
//        $('#zs').focus();
//        return false;
//    }
    if (document.myform.audit.value<=0) {
        alert('请选择审核人');
        document.myform.audit.focus();
        return false;
    }
    xfsum = $('#xf').html()+0;
    if(xfsum<=0){
        alert('当前申请学分为0，请勾选申请细则');
        return false;
    }
    return true;
}