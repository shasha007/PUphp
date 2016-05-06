/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function checkCourse(){
    var title      = $( '#title' ).val();
    var type       = $( '#type' ).val();
    var address    = $( '#address' ).val();
    var stime      = $( '#sTime' ).val();
    var etime      = $( '#eTime' ).val();
    var deadline      = $( '#deadline' ).val();
    var textarea  = $('#info').val();
    var teacher  = $('#teacher').val();
    var cre  = $('#cre').val();
    var img  = $('#imgt').val();
    var logo  = $('#logo').val();
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("课程名称不能为空");
        return false;
    }
    if(!textarea){
        ui.error("课程简介不能为空");
        return false;
    }
    if(!teacher){
        ui.error("课程老师不能为空");
        return false;
    }
    if( title.length<2 ) {
        ui.error( '课程名称必须大于2个字符' );
        return false;
    }
    if( title.length>20 ) {
        ui.error( '课程名称最大20个字符' );
        return false;
    }
    if( address == 0 ) {
        ui.error( '请填写课程地点' );
        return false;
    }
    if( type == 0 ) {
        ui.error( '请选择课程分类' );
        return false;
    }
    if( !stime ) {
        ui.error( '请填写开始时间' );
        return false;
    }
    if( !etime ) {
        ui.error( '请填写结束时间' );
        return false;
    }
    if( !deadline ) {
        ui.error( '请填写截止报名时间' );
        return false;
    }
    if( !cre ) {
        ui.error( '请填写课时' );
        return false;
    }
    if(!img){
     if( !logo ) {
        ui.error( '请上传课程图片' );
        return false;
    }
    }
    return true;
}
        
        
        
function checkCourseActive(){
    var title      = $( '#title2' ).val();
    var type       = $( '#type2' ).val();
    var address    = $( '#address2' ).val();
    var stime      = $( '#sTime2' ).val();
    var etime      = $( '#eTime2' ).val();
    var deadline      = $( '#deadline2' ).val();
    var textarea  = $('#info2').val();
    var cre  = $('#cre2').val();
    var img  = $('#imgt').val();
    var logo  = $('#logo2').val();
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("课程活动名称不能为空");
        return false;
    }
    if(!textarea){
        ui.error("课程活动简介不能为空");
        return false;
    }
      
    if( title.length<2 ) {
        ui.error( '课程活动名称必须大于2个字符' );
        return false;
    }
    if( title.length>20 ) {
        ui.error( '课程活动名称最大20个字符' );
        return false;
    }
    if( address == 0 ) {
        ui.error( '请填写课程活动地点' );
        return false;
    }
    if( type == 0 ) {
        ui.error( '请选择课程活动分类' );
        return false;
    }
    if( !stime ) {
        ui.error( '请填写开始时间' );
        return false;
    }
    if( !etime ) {
        ui.error( '请填写结束时间' );
        return false;
    }
    if( !deadline ) {
        ui.error( '请填写截止报名时间' );
        return false;
    }
    if( !cre ) {
        ui.error( '请填写课程活动学分' );
        return false;
    }
    if(!img){
        if( !logo ) {
            ui.error( '请上传课程图片' );
            return false;
        }
    }
    return true;
}

function adminDelAction( mid,courseId ){
    var mid = mid ? mid : getChecked();
    mid = mid.toString();
    if(mid=='' || mid==0){
        alert('请选择要通过的成员！');
        return false;
    }
    if(confirm("是否删除选中的成员？")){
        $.post( U('event/LessonManage/doDeleteMember'),{
            mid:mid,
            id:courseId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                var id_list = mid.split( ',' );
                for (var j=0 ; j< id_list.length ; j++   ){
                    $('#list_'+id_list[j]).remove();
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}

function activeDelAction( mid,courseId ){
    var mid = mid ? mid : getChecked();
    mid = mid.toString();
    if(mid=='' || mid==0){
        alert('请选择要通过的成员！');
        return false;
    }
    if(confirm("是否删除选中的成员？")){
        $.post( U('event/LessonActive/doDeleteMember'),{
            mid:mid,
            id:courseId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                var id_list = mid.split( ',' );
                for (var j=0 ; j< id_list.length ; j++   ){
                    $('#list_'+id_list[j]).remove();
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}
function auditUser(mid,courseId){
    var mid = mid ? mid : getChecked();
    mid = mid.toString();
    if(mid=='' || mid==0){
        alert('请选择要通过的成员！');
        return false;
    }
    if(confirm("是否审核通过选中的成员？")){
        $.post( U('event/LessonManage/doAuditMember'),{
            mid:mid,
            id:courseId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                var id_list = mid.split( ',' );
                for (var j=0 ; j< id_list.length ; j++   ){
                    $('#list_'+id_list[j]).remove();
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}

function auditActiveUser(mid,courseId){
    var mid = mid ? mid : getChecked();
    mid = mid.toString();
    if(mid=='' || mid==0){
        alert('请选择要通过的成员！');
        return false;
    }
    if(confirm("是否审核通过选中的成员？")){
        $.post( U('event/LessonActive/doAuditMember'),{
            mid:mid,
            id:courseId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                var id_list = mid.split( ',' );
                for (var j=0 ; j< id_list.length ; j++   ){
                    $('#list_'+id_list[j]).remove();
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}

function delPhoto(id,nid){
    delList(U('event/LessonActive/deletePhoto')+'&id='+id,nid);
}

function delList(url,nid){
    var nids = nid ? nid : getChecked();
    nids = nids.toString();
    if(nids=='' || nids==0){
        ui.error("请选择要删除的选项");
        return false;
    }
    if( confirm("您确定要删除吗？") ){
        $.post( url,{
            nid:nids
        },function(text ){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                if(nid){
                    $('#list_'+nid).remove();
                }else{
                    var nid_list = nids.split( ',' );
                    for (var j=0 ; j< nid_list.length ; j++   ){
                        $('#list_'+nid_list[j]).remove();
                    }
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}
function getChecked() {
    var ids = new Array();
    $.each($('table input:checked'), function(i, n){
        if($(n).val() != ''){
            ids.push( $(n).val() );
        }
    });
    return ids;
}
function gbcount(o,max){
    var msg = $(o).val();
    var rest = max - msg.length;
    if(rest < 0){
        rest = 0;
        $('#remain').html(0);
        $(o).val(msg.substring(0,max));
        alert('不能超过'+max+'个字!');
    }
    $('#remain').html(rest);
}

function checkon(o){
    if( o.checked == true ){
        $(o).parent().parent().addClass('bg_on') ;
    }else{
        $(o).parent().parent().removeClass('bg_on') ;
    }
}

function checkAll(o){
    if( o.checked == true ){
        $('input[name="checkbox"]').attr('checked','true');
        $('input[name="checkbox[]"]').attr('checked','true');
        $('[overstyle="on"]').addClass("bg_on");
    }else{
        $('input[name="checkbox[]"]').removeAttr('checked');
        $('input[name="checkbox"]').removeAttr('checked');
        $('[overstyle="on"]').removeClass("bg_on");
    }
}