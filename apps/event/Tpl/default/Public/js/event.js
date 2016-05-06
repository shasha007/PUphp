function doNote(id,obj){
    var note = obj.value;
    if(note>0 && confirm('确定要给此活动打'+note+'分吗?')){
        $.post(U('event/Index/ajaxNote'),{id:id,note:obj.value},function( text ){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                $('#noteSelect'+id).remove();
                $('#note'+id).html(json.data.note);
                $('#noteNum'+id).html(json.data.noteUser);
            }else{
                ui.error( json.info );
            }
        });
    }
}
function playFlash(vid){
    ui.box.load(U('event/Index/playFlash')+'&id='+vid,{title:'播放视频'});
}
function addFlash(id,uid){
    var url = U('event/Backend/addFlash')+'&id='+id;
    if(uid){
        url = url + '&uid=' + uid;
    }
    ui.box.load(url,{title:'添加视频'});
}
function delFlash(id,nid){
    delList(U('event/Backend/deleteFlash')+'&id='+id,nid);
}
function delPhoto(id,nid){
    delList(U('event/Backend/deletePhoto')+'&id='+id,nid);
}
function delNews(id,nid){
    delList(U('event/Backend/deleteNews')+'&id='+id,nid);
}
function selectArea(){
    var typevalue = $("#current").val();
    ui.box.load(U('event/Index/area')+'&selected='+typevalue,{title:'选择校区'});
}
function changeVote(id,uid,act){
    if( act == 'open' ){
        v2 = "设为关闭";
        act2 = 'close';
    }else{
        v2 = "设为打开";
        act2 = 'open';
    }
    $.post(U('event/Backend/doChangeVote'),{
        id:id,
        uid:uid,
        type:act
    },function( text ){
        json = eval('('+text+')');
        if( json.status == 1 ){
            ui.success( json.info );
            $('#vote'+uid).html('<a href="javascript:changeVote('+id+','+uid+',\''+act2+'\');" >'+v2+'</a>');
        }else{
            ui.error( json.info );
        }
    });
}
function changeIsTicket(id,act){
    if( act == 'open' ){
        v= "打开投票功能";
        v2 = "关闭投票功能";
        act2 = 'close';

    }else{
        v = "关闭投票功能";
        v2 = "打开投票功能";
        act2 = 'open';
    }
    if( confirm( '是否'+v ) ){
        $.post(U('event/Backend/doChangeIsTicket'),{
            id:id,
            type:act
        },function( text ){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                $('#ticket').html('<a href="javascript:void(0);" onclick="changeIsTicket('+id+',\''+act2+'\')">'+v2+'</a>');
            }else{
                ui.error( json.info );
            }
        });
    }
}
//取消申请，参加
function EventDelAction( id ){
    $.post( U('event/Index/doDelAction'),{id:id},function(text){
        json = eval('('+text+')');
        if( json.status == 1 ){
            ui.success( json.info );
            setTimeout(function(){location.reload();},1500);
        }else{
            ui.error( json.info );
        }
    });
}

function auditUser(mid,eventId){
    if(confirm("是否审核通过选中的成员？")){
        $.post( U('event/Backend/doAuditMember'),{
            mid:mid,
            id:eventId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                $('#list_'+mid).remove();
            }else{
                ui.error( json.info );
            }
        });
    }
}

function adminDelAction( mid,eventId ){
    if(confirm("是否删除选中的成员？")){
        $.post( U('event/Backend/doDeleteMember'),{
            mid:mid,
            id:eventId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                $('#list_'+mid).remove();
            }else{
                ui.error( json.info );
            }
        });
    }
}

function endEvent( id ){
    if(confirm('是否提前结束报名?')){
        $.post( U('event/Backend/doEndAction'),{
            id:id
        },function( text ){
            if( text == 1 ){
                ui.success('提前结束报名成功');
                $('#event_satus_' + id).html('活动报名结束');//活动列表
                var statusTag = $('#event_satus')
                statusTag.html('此活动报名已经结束');//活动详细页
                statusTag.removeClass('green');
                statusTag.addClass('red');
                $('#endDeadline').remove();//活动详细页
            }else if( text == -1 ){
                ui.error( '非法访问' );
            }else if( text == 0 ){
                ui.error( '结束活动报名失败。请稍后再试' );
            }else{
                ui.error( '未知错误' );
            }
        });
    }
}

//取消申请，参加
function editCollect( id, act ){
    $.post( U('event/Index/editCollect'),{id:id,type:act},function(text){
        json = eval('('+text+')');
        if( json.status == 1 ){
            ui.success( json.info );
            if(act == 'add'){
                $('#col_add'+id).hide();
                $('#col_cancel'+id).show();
            }else{
                $('#col_cancel'+id).hide();
                $('#col_add'+id).show();
            }
        }else{
            ui.error( json.info );
        }
    });
}
function delEvent(eventId,jump){
    var jump = jump==true?true:false;
    if(confirm('确认删除此活动?')){
        $.post( U('event/Backend/doDeleteEvent'),{
            id:eventId
        },function( text ){
            if( text == 1 ){
                ui.success('删除活动成功');
                if(jump == true){
                    location.href=U('event/Index/index')+'&action=launch';
                }else{
                    $('#event_'+eventId).remove();
                }
            }else if( text == 0 ){
                ui.error( '删除活动失败！' );
            }else{
                ui.error( '未知错误，请稍后再试' );
            }
        });
    }
}

function removeHTMLTag(str) {
    str = str.replace(/<\/?[^>]*>/g,'');
    str = str.replace(/[ | ]*\n/g,'\n');
    str=str.replace(/&nbsp;/ig,'');
    return str;
}

function check(){
    var title      = $( '#title' ).val();
    var type       = $( '#type' ).val();
    var address    = $( '#address' ).val();
    var limitCount = $( '#limitCount' ).val();
    //var explain    = getEditorContent('explain');//$( '#explain' ).val();
    var stime      = $( '#sTime' ).val();
    var etime      = $( '#eTime' ).val();
    var deadline      = $( '#deadline' ).val();
    var textarea  = $('#textarea').val();
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("活动名称不能为空");
        return false;
    }
	if(!textarea){
		ui.error("活动简介不能为空");
		return false;
	}
    if( title.length<4 ) {
        ui.error( '活动名称必须大于4个字符' );
        return false;
    }
    if( title.length>30 ) {
        ui.error( '活动名称最大30个字符' );
        return false;
    }
    if( $( '#showSids' ).val() == 0 ) {
        ui.error( '请选择活动显示于哪些学校' );
        return false;
    }
    if( address == 0 ) {
        ui.error( '请填写活动地点' );
        return false;
    }
    if( type == 0 ) {
        ui.error( '请选择活动分类' );
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
    return true;
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