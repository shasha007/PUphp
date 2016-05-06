function doNote(id,obj){
    var note = obj.value;
    if(note>0 && confirm('确定要给此活动打'+note+'分吗?')){
        $.post(U('event/School/ajaxNote'),{id:id,note:obj.value},function( text ){
            var json =$.parseJSON(text);
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
    ui.box.load(U('event/School/playFlash')+'&id='+vid,{title:'播放视频'});
}
function addFlash(id,uid){
    var url = U('event/Author/addFlash')+'&id='+id;
    if(uid){
        url = url + '&uid=' + uid;
    }
    ui.box.load(url,{title:'添加视频'});
}
function delFlash(id,nid){
    delList(U('event/Author/deleteFlash')+'&id='+id,nid);
}
function delPhoto(id,nid){
    delList(U('event/Author/deletePhoto')+'&id='+id,nid);
}
function delNews(id,nid){
    delList(U('event/Author/deleteNews')+'&id='+id,nid);
}
function selectArea(){
    var typevalue = $("#current").val();
    ui.box.load(U('event/Index/area')+'&selected='+typevalue,{title:'选择校区'});
}
function changeVote(id,uid,act){
    if( act == 'open' ){
        v2 = "已打开";
        act2 = 'close';
    }else{
        v2 = "已关闭";
        act2 = 'open';
    }
    $.post(U('event/Author/doChangeVote'),{
        id:id,
        uid:uid,
        type:act
    },function( text ){
        var json =$.parseJSON(text);
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
        v2 = "投票已打开";
        act2 = 'close';

    }else{
        v = "关闭投票功能";
        v2 = "投票已关闭";
        act2 = 'open';
    }
    if( confirm( '是否'+v ) ){
        $.post(U('event/Author/doChangeIsTicket'),{
            id:id,
            type:act
        },function( text ){
            var json =$.parseJSON(text);
            if( json.status == 1 ){
                ui.success( json.info );
                $('#ticket').html('<a href="javascript:void(0);" onclick="changeIsTicket('+id+',\''+act2+'\')">'+v2+'</a>');
            }else{
                ui.error( json.info );
            }
        });
    }
}
function repeatedVote(id,act){
    if( act == 'open' ){
        v= "改为可重复投给一个选手";
        v2 = "可重复投票";
        act2 = 'close';

    }else{
        v = "改为不可重复投给一个选手";
        v2 = "不可重复投票";
        act2 = 'open';
    }
    if( confirm( '是否'+v ) ){
        $.post(U('event/Author/doRepeatedVote'),{
            id:id,
            type:act
        },function( text ){
            var json =$.parseJSON(text);
            if( json.status == 1 ){
                ui.success( json.info );
                $('#repeatedVote').html('<a href="javascript:void(0);" onclick="repeatedVote('+id+',\''+act2+'\')">'+v2+'</a>');
            }else{
                ui.error( json.info );
            }
        });
    }
}
function changeUpload(id,act){
    if( act == 'open' ){
        v= "打开选手上传功能";
        v2 = "选手上传已打开";
        act2 = 'close';

    }else{
        v = "关闭选手上传功能";
        v2 = "选手上传已关闭";
        act2 = 'open';
    }
    if( confirm( '是否'+v ) ){
        $.post(U('event/Author/doChangeUpload'),{
            id:id,
            type:act
        },function( text ){
            var json =$.parseJSON(text);
            if( json.status == 1 ){
                ui.success( json.info );
                $('#upload').html('<a href="javascript:void(0);" onclick="changeUpload('+id+',\''+act2+'\')">'+v2+'</a>');
            }else{
                ui.error( json.info );
            }
        });
    }
}
//取消申请，参加
function EventDelAction( id ){
    $.post( U('event/School/doDelAction'),{id:id},function(text){
        var json =$.parseJSON(text);
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
        $.post( U('event/Author/doAuditMember'),{
            mid:mid,
            id:eventId
        },function(text){
            var json =$.parseJSON(text);
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
        $.post( U('event/Author/doDeleteMember'),{
            mid:mid,
            id:eventId
        },function(text){
            var json =$.parseJSON(text);
            if( json.status == 1 ){
                ui.success( json.info );
                $('#list_'+mid).remove();
            }else{
                ui.error( json.info );
            }
        });
    }
}

function adminAttendAction (mid,eventId,id ){
    if(confirm("是否将该成员签到？")){
        $.post( U('event/Author/userAttend'),{
            mid:mid,
            id:eventId
        },function(text){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                $('#dao'+id).html('已签到');
            }else{
                ui.error( json.info );
            }
        });
    }
}

function delPlayer(pid,eventId ){
    if(confirm("是否删除选中的选手？")){
        $.post( U('event/Author/doDeletePlayer'),{
            pid:pid,
            id:eventId
        },function(text){
            var json =$.parseJSON(text);
            if( json.status == 1 ){
                ui.success( json.info );
                $('#list_'+pid).remove();
            }else{
                ui.error( json.info );
            }
        });
    }
}

function endEvent( id ){
    if(confirm('是否提前结束报名?')){
        $.post( U('event/Author/doEndAction'),{
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

function delEvent(eventId,jump){
    var jump = jump==true?true:false;
    if(confirm('确认删除此活动?')){
        $.post( U('event/Author/doDeleteEvent'),{
            id:eventId
        },function( text ){
            if( text == 1 ){
                ui.success('删除活动成功');
                if(jump == true){
                    location.href=U('event/School/index')+'&action=launch';
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
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("活动名称不能为空");
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
    if(!$('#textarea').val()){
            ui.error("活动简介不能为空");
            return false;
    }
        if($( '#group' )&&$( '#group' ).val()==0){
        ui.error( '请选择归属部落' );
        return false;
    }
    var audit       = $( '#audit_uid' ).val();
    if( audit == 0 ) {
        ui.error( '请选择审核人' );
        return false;
    }
    var type       = $( '#type' ).val();
    if( type == 0 ) {
        ui.error( '请选择活动分类' );
        return false;
    }
    var address    = $( '#address' ).val();
    if( address == 0 ) {
        ui.error( '请填写活动地点' );
        return false;
    }
    var stime      = $( '#sTime' ).val();
    if( !stime ) {
        ui.error( '请填写开始时间' );
        return false;
    }
    var etime      = $( '#eTime' ).val();
    if( !etime ) {
        ui.error( '请填写结束时间' );
        return false;
    }
    var deadline      = $( '#deadline' ).val();
    if( !deadline ) {
        ui.error( '请填写截止报名时间' );
        return false;
    }
    if(stime>etime){
        ui.error( '活动结束时间不得早于开始时间' );
        return false;
    }
    var startline      = $( '#startline' ).val();
    if(startline!='' && startline>deadline){
        ui.error( '报名截止时间不得早于报名开始时间' );
        return false;
    }
    if(deadline>etime){
        ui.error( '报名截止时间不能晚于活动结束时间' );
        return false;
    }
    if($('#editType').val() == 'add'){
        var cover   =$('input[name="imgid1[]"]').val();
        if(!cover){
            ui.error( '请上传活动图标' );
            return false;
        }
        var defaultBanner = $('input:radio[name="banner"]:checked').val();
        if(!defaultBanner) {
            var logo    = $('input[name="imgid2[]"]').val();
            if(!logo) {
                ui.error( '请上传或选择活动海报' );
                return false;
            }
        }
    }
    return true;
}

function newCheck(){
    var stime      = $( '#sTime' ).val();
    var etime      = $( '#eTime' ).val();
    var deadline      = $( '#deadline' ).val();
    var address    = $( '#address' ).val();
    if( address == 0 ) {
        ui.error( '请填写活动地点' );
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
    if(stime>etime){
        ui.error( '活动结束时间不得早于开始时间' );
        return false;
    }
    var startline      = $( '#startline' ).val();
    if(startline!='' && startline>deadline){
        ui.error( '报名截止时间不得早于报名开始时间' );
        return false;
    }
    if(deadline>etime){
        ui.error( '报名截止时间不能晚于活动结束时间' );
        return false;
    }
    return true;
}
function checkProv(){
    var title      = $( '#title' ).val();
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("活动名称不能为空");
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
    if(!$('#textarea').val()){
            ui.error("活动简介不能为空");
            return false;
    }
    if( $('#type').val() == 0 ) {
        ui.error( '请选择活动分类' );
        return false;
    }
    var stime      = $( '#sTime' ).val();
    if( !stime ) {
        ui.error( '请填写开始时间' );
        return false;
    }
    var etime      = $( '#eTime' ).val();
    if( !etime ) {
        ui.error( '请填写结束时间' );
        return false;
    }
    var deadline      = $( '#deadline' ).val();
    if( !deadline ) {
        ui.error( '请填写截止报名时间' );
        return false;
    }
    if(stime>etime){
        ui.error( '活动结束时间不得早于开始时间' );
        return false;
    }
    var startline      = $( '#startline' ).val();
    if(startline!='' && startline>deadline){
        ui.error( '报名截止时间不得早于报名开始时间' );
        return false;
    }
    if(deadline>etime){
        ui.error( '报名截止时间不能晚于活动结束时间' );
        return false;
    }
    if($('#editType').val() == 'add'){
        var cover   =$('input[name="imgid1[]"]').val();
        if(!cover){
            ui.error( '请上传活动图标' );
            return false;
        }
        var defaultBanner = $('input:radio[name="banner"]:checked').val();
        if(!defaultBanner) {
            var logo    = $('input[name="imgid2[]"]').val();
            if(!logo) {
                ui.error( '请上传或选择活动海报' );
                return false;
            }
        }
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