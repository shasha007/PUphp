function changeType(o,credit){
    var typeId = $(o).val();
    if(typeId != 0){
        var change = '';
        var maxNum = $(o).find("option:selected").attr("banner");
        var pid = $(o).find("option:selected").attr("pid");
        for (var i=1;i<=maxNum;i++){
            change += '<label><input name="banner" type="radio" value="'+i+'" /> <img width="440px" height="100px" src="'+_PIC_URL_+'/data/sys_pic/event/'
            +pid+'_'+i+'.jpg" /></label><br/>'
        }
        $('#bannerImg').html(change);
        var auto_credit = $('#auto_credit').val();
        if(credit && auto_credit){
            var levelId = $('#levelId').val();
            ajaxAutoCredit(typeId,levelId);
        }
    }
}
function ajaxAutoCredit(typeId,levelId){
    if(!typeId || !levelId){
        return ;
    }
    $.ajax({type: "POST",url: _URL_+"&act=autoCredit",timeout: 20000,data:"levelId="+levelId+"&typeId="+typeId,
        success: function(response){
            var json = $.parseJSON(response);
            if(json.status == 1){
                $('#credit').val(json.info);
                $('#credit').css("background", "#ffffcc");
            }else{
                ui.error(json.info);
            }
        },
        error:function(){
            ui.error('自动学分更新失败，稍后再试');
        }
    });
}
function changeLevel(){
    var auto_credit = $('#auto_credit').val();
    if(!auto_credit){
        return ;
    }
    var typeId = $('#type').val();
    var levelId = $('#levelId').val();
    ajaxAutoCredit(typeId,levelId);
}
function clearRadio(){
    $(":radio[name=banner]:checked").removeAttr('checked');
}