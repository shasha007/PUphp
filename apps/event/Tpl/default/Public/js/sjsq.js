function submitForm(v_form){
    if(checkform(v_form)){
        v_form.submit();
    }
}
function openUpload(){
    ui.box.load(U('event/Sjsq/uploadImg'),{title:'上传图片'});
}
function openFlash(){
    ui.box.load(U('event/Sjsq/addFlash'),{title:'上传视频'});
}
var isRedirect = true;
var swfu;
//开始上传
function start_upload(){
    swfu.startUpload();
    $('#btnUpload').attr('disabled',true).removeClass('btn5').val("上传中...");
}

//单图上传回调函数,返回上传完成文件的信息
function ts_upload_success(serverData){
    var data	=	$.parseJSON(serverData);
    if(data.status==true){
        var imgCount = $("#uploaded_photos").children("li").length;
        var msg = '<li class="prov_link p1" id="img'+data.id+'">';
        msg += '<div class="prov_thumb"><img src="'+data.src+'" alt="" /></div>';
        if(imgCount>0){
            msg += '<div id="topimg'+data.id+'" class="prov_thumb_set"><a href="javascript:void(0)" onclick="topImg('+data.id+')">设为封面</a></div>';
        }else{
            msg += '<div id="topimg'+data.id+'" class="prov_thumb_set" style="display:none"><a href="javascript:void(0)" onclick="topImg('+data.id+')">设为封面</a></div>';
        }
        msg += '<div class="prov_thumb_del"><a href="javascript:void(0)" onclick="delImg('+data.id+')">删除</a></div>';
        msg += '<input type="hidden" name="imgs[]" value="'+data.id+'" /></li>';
        $('#uploaded_photos').append(msg);
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
function post_flash(){
var link = $('#link').val();
if(link!=''){
    $.post(U('event/Sjsq/doAddFlash'),{link:link},function(txt){
        json = $.parseJSON(txt);
        if( json.status == 1 ){
            var msg = '<div>'+json.title+' <a href="javascript:void(0)" onclick="javascript:$(this).parent().remove();">删除</a>'
            msg += '<input type="hidden" name="flash[]" value="'+json.id+'" /></div>';
            $('#uploaded_flash').append(msg);
            ui.box.close();
        }else{
            alert( json.info );
        }
    });
}
}
function delImg(id){
    $('#img'+id).remove();
}

function topImg(id){
    oldTop = $("#uploaded_photos li:first-child");
    oldId = oldTop.attr('id');
    $('#top'+oldId).show();
    oldHtml = oldTop.html();
    newTop = $('#img'+id);
    newId = 'img'+id;
    newHtml = newTop.html();
    oldTop.html(newHtml);
    oldTop.attr('id', newId);
    newTop.html(oldHtml);
    newTop.attr('id', oldId);
    $('#top'+newId).hide();
}
//当文件队列有文件时
function enableUploadButton(file){
    $('#btnUpload').attr('disabled',false).addClass('btn5').val("开始上传");
}
//全部上传完成
function queueComplete(numFilesUploaded) {
    ui.box.close();
}